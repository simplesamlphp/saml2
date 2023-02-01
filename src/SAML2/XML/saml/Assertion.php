<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utilities\Temporal;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\Utils\Security as SecurityUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;
use SimpleSAML\XMLSecurity\XML\EncryptableElementInterface;
use SimpleSAML\XMLSecurity\XML\EncryptableElementTrait;
use SimpleSAML\XMLSecurity\XML\SignableElementInterface;
use SimpleSAML\XMLSecurity\XML\SignableElementTrait;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;
use SimpleSAML\XMLSecurity\XML\SignedElementTrait;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;

use function array_filter;
use function array_merge;
use function array_pop;
use function array_values;

/**
 * Class representing a SAML 2 assertion.
 *
 * @package simplesamlphp/saml2
 */
final class Assertion extends AbstractSamlElement implements
    EncryptableElementInterface,
    SignableElementInterface,
    SignedElementInterface
{
    use EncryptableElementTrait;
    use SignableElementTrait;
    use SignedElementTrait;


    /**
     * @var bool
     */
    protected bool $wasSignedAtConstruction = false;

    /**
     * The original signed XML
     *
     * @var \DOMElement
     */
    protected DOMElement $xml;


    /**
     * Assertion constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param string|null $id
     * @param int|null $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Subject|null $subject
     * @param \SimpleSAML\SAML2\XML\saml\Conditions|null $conditions
     * @param \SimpleSAML\SAML2\XML\saml\AbstractStatementType[] $statements
     */
    public function __construct(
        protected Issuer $issuer,
        protected ?string $id = null,
        protected ?int $issueInstant = null,
        protected ?Subject $subject = null,
        protected ?Conditions $conditions = null,
        protected array $statements = [],
    ) {
        $this->dataType = C::XMLENC_ELEMENT;

        Assert::true(
            $subject || !empty($statements),
            "Either a <saml:Subject> or some statement must be present in a <saml:Assertion>",
        );
        Assert::allIsInstanceOf($statements, AbstractStatementType::class);
        Assert::nullOrNotWhitespaceOnly($id);
    }


    /**
     * Collect the value of the subject
     *
     * @return \SimpleSAML\SAML2\XML\saml\Subject|null
     */
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }


    /**
     * Collect the value of the conditions-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\Conditions|null
     */
    public function getConditions(): ?Conditions
    {
        return $this->conditions;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\AttributeStatement[]
     */
    public function getAttributeStatements(): array
    {
        return array_values(array_filter($this->statements, function ($statement) {
            return $statement instanceof AttributeStatement;
        }));
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\AuthnStatement[]
     */
    public function getAuthnStatements(): array
    {
        return array_values(array_filter($this->statements, function ($statement) {
            return $statement instanceof AuthnStatement;
        }));
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\AbstractStatement[]
     */
    public function getStatements(): array
    {
        return array_values(array_filter($this->statements, function ($statement) {
            return $statement instanceof AbstractStatement;
        }));
    }


    /**
     * Retrieve the identifier of this assertion.
     *
     * @return string The identifier of this assertion.
     */
    public function getId(): string
    {
        if ($this->id === null) {
            $container = ContainerSingleton::getInstance();
            return $container->generateId();
        }

        return $this->id;
    }


    /**
     * Retrieve the issue timestamp of this assertion.
     *
     * @return int The issue timestamp of this assertion, as an UNIX timestamp.
     */
    public function getIssueInstant(): int
    {
        if ($this->issueInstant === null) {
            return Temporal::getTime();
        }

        return $this->issueInstant;
    }


    /**
     * Retrieve the issuer if this assertion.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Issuer The issuer of this assertion.
     */
    public function getIssuer(): Issuer
    {
        return $this->issuer;
    }


    /**
     * @return bool
     */
    public function wasSignedAtConstruction(): bool
    {
        return $this->wasSignedAtConstruction;
    }


    /**
     * Get the XML element.
     *
     * @return \DOMElement
     */
    public function getXML(): DOMElement
    {
        return $this->xml;
    }


    /**
     * Set the XML element.
     *
     * @param \DOMElement $xml
     */
    private function setXML(DOMElement $xml): void
    {
        $this->xml = $xml;
    }


    /**
     * @return \DOMElement
     */
    protected function getOriginalXML(): DOMElement
    {
        return $this->xml ?? $this->toUnsignedXML();
    }


    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }


    public function getEncryptionBackend(): ?EncryptionBackend
    {
        // return the encryption backend you want to use,
        // or null if you are fine with the default
        return null;
    }


    /**
     * Convert XML into an Assertion
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Assertion', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Assertion::NS, InvalidDOMElementException::class);
        Assert::same(self::getAttribute($xml, 'Version'), '2.0', 'Unsupported version: %s');

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = XMLUtils::xsDateTimeToTimestamp($issueInstant);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::minCount($issuer, 1, 'Missing <saml:Issuer> in assertion.', MissingElementException::class);
        Assert::maxCount($issuer, 1, 'More than one <saml:Issuer> in assertion.', TooManyElementsException::class);

        $subject = Subject::getChildrenOfClass($xml);
        Assert::maxCount(
            $subject,
            1,
            'More than one <saml:Subject> in <saml:Assertion>',
            TooManyElementsException::class,
        );

        $conditions = Conditions::getChildrenOfClass($xml);
        Assert::maxCount(
            $conditions,
            1,
            'More than one <saml:Conditions> in <saml:Assertion>.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one <ds:Signature> element is allowed.', TooManyElementsException::class);

        $authnStatement = AuthnStatement::getChildrenOfClass($xml);
        $attrStatement = AttributeStatement::getChildrenOfClass($xml);
        $statements = AbstractStatement::getChildrenOfClass($xml);

        $assertion = new static(
            array_pop($issuer),
            self::getAttribute($xml, 'ID'),
            $issueInstant,
            array_pop($subject),
            array_pop($conditions),
            array_merge($authnStatement, $attrStatement, $statements),
        );

        if (!empty($signature)) {
            $assertion->setSignature($signature[0]);
            $assertion->wasSignedAtConstruction = true;
            $assertion->setXML($xml);
        }

        return $assertion;
    }


    /**
     * Convert this assertion to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('Version', '2.0');
        $e->setAttribute('ID', $this->getId());
        $e->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getIssueInstant()));

        $this->getIssuer()->toXML($e);
        $this->getSubject()?->toXML($e);
        $this->getConditions()?->toXML($e);

        foreach ($this->statements as $statement) {
            $statement->toXML($e);
        }

        return $e;
    }


    /**
     * Convert this assertion to a signed XML element, if a signer was set.
     *
     * @param \DOMElement|null $parent The DOM node the assertion should be created in.
     *
     * @return \DOMElement This assertion.
     * @throws \Exception
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($this->isSigned() === true && $this->signer === null) {
            // We already have a signed document and no signer was set to re-sign it
            if ($parent === null) {
                return $this->getXML();
            }

            $node = $parent->ownerDocument?->importNode($this->getXML(), true);
            $parent->appendChild($node);
            return $parent;
        }

        $e = $this->toUnsignedXML($parent);

        if ($this->signer !== null) {
            $signedXML = $this->doSign($e);

            // Test for an Issuer
            $messageElements = XPath::xpQuery($signedXML, './saml_assertion:Issuer', XPath::getXPath($signedXML));
            $issuer = array_pop($messageElements);

            $signedXML->insertBefore($this->signature?->toXML($signedXML), $issuer->nextSibling);
            return $signedXML;
        }

        return $e;
    }
}
