<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utilities\Temporal;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\SignedElementInterface;
use SimpleSAML\SAML2\XML\SignedElementTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\Utils\Security as SecurityUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;
use SimpleSAML\XMLSecurity\XMLSecEnc;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

/**
 * Class representing a SAML 2 assertion.
 *
 * @package simplesamlphp/saml2
 */
class Assertion extends AbstractSamlElement implements SignedElementInterface
{
    use SignedElementTrait;

    /**
     * The identifier of this assertion.
     *
     * @var string
     */
    protected string $id;

    /**
     * The issue timestamp of this assertion, as an UNIX timestamp.
     *
     * @var int
     */
    protected int $issueInstant;

    /**
     * The issuer of this assertion.
     *
     * If the issuer's format is \SAML2\Constants::NAMEID_ENTITY, this property will just take the issuer's string
     * value.
     *
     * @var \SimpleSAML\SAML2\XML\saml\Issuer
     */
    protected Issuer $issuer;

    /**
     * The subject of this assertion
     *
     * @var \SimpleSAML\SAML2\XML\saml\Subject|null
     */
    protected ?Subject $subject;

    /**
     * The subject of this assertion
     *
     * If the NameId is null, no subject was included in the assertion.
     *
     * @var \SimpleSAML\SAML2\XML\saml\NameID|null
     */
    protected ?NameID $nameId = null;

    /**
     * The encrypted NameId of the subject.
     *
     * If this is not null, the NameId needs decryption before it can be accessed.
     *
     * @var \DOMElement|null
     */
    protected ?DOMElement $encryptedNameId = null;

    /**
     * The statements made by this assertion.
     *
     * @var \SimpleSAML\SAML2\XML\saml\AbstractStatement[]
     */
    protected $statements = [];

    /**
     * The attributes, as an associative array, indexed by attribute name
     *
     * To ease handling, all attribute values are represented as an array of values, also for values with a multiplicity
     * of single. There are 5 possible variants of datatypes for the values: a string, an integer, an array, a
     * DOMNodeList or a \SimpleSAML\SAML2\XML\saml\NameID object.
     *
     * If the attribute is an eduPersonTargetedID, the values will be SAML2\XML\saml\NameID objects.
     * If the attribute value has an type-definition (xsi:string or xsi:int), the values will be of that type.
     * If the attribute value contains a nested XML structure, the values will be a DOMNodeList
     * In all other cases the values are treated as strings
     *
     * **WARNING** a DOMNodeList cannot be serialized without data-loss and should be handled explicitly
     *
     * @var array multi-dimensional array of \DOMNodeList|\SimpleSAML\SAML2\XML\saml\NameID|string|int|array
     */
    protected array $attributes = [];

    /**
     * The SubjectConfirmation elements of the Subject in the assertion.
     *
     * @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[]
     */
    protected array $SubjectConfirmation = [];

    /**
     * @var bool
     */
    protected bool $wasSignedAtConstruction = false;

    /**
     * @var \SimpleSAML\SAML2\XML\saml\Conditions|null
     */
    protected $conditions;


    /**
     * Assertion constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param string|null $id
     * @param int|null $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Subject|null $subject
     * @param \SimpleSAML\SAML2\XML\saml\Conditions|null $conditions
     * @param \SimpleSAML\SAML2\XML\saml\AbstractStatement[] $statements
     */
    public function __construct(
        Issuer $issuer,
        ?string $id = null,
        ?int $issueInstant = null,
        ?Subject $subject = null,
        ?Conditions $conditions = null,
        array $statements = []
    ) {
        Assert::true(
            $subject || !empty($statements),
            "Either a <saml:Subject> or some statement must be present in a <saml:Assertion>"
        );
        $this->setIssuer($issuer);
        $this->setId($id);
        $this->setIssueInstant($issueInstant);
        $this->setSubject($subject);
        $this->setConditions($conditions);
        $this->setStatements($statements);
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
     * Set the value of the subject-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\Subject|null $subject
     */
    protected function setSubject(?Subject $subject): void
    {
        $this->subject = $subject;
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
     * Set the value of the conditions-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\Conditions|null $conditions
     */
    protected function setConditions(?Conditions $conditions): void
    {
        $this->conditions = $conditions;
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
     * @return \SimpleSAML\SAML2\XML\saml\Statement[]
     */
    public function getStatements(): array
    {
        return array_values(array_filter($this->statements, function ($statement) {
            return $statement instanceof Statement;
        }));
    }


    /**
     * Set the statements in this assertion
     *
     * @param \SimpleSAML\SAML2\XML\saml\AbstractStatement[] $statements
     */
    protected function setStatements(array $statements): void
    {
        Assert::allIsInstanceOf($statements, AbstractStatement::class);

        $this->statements = $statements;
    }


    /**
     * Retrieve the identifier of this assertion.
     *
     * @return string The identifier of this assertion.
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Set the identifier of this assertion.
     *
     * @param string|null $id The new identifier of this assertion.
     */
    public function setId(?string $id): void
    {
        if ($id === null) {
            $id = Utils::getContainer()->generateId();
        }
        $this->id = $id;
    }


    /**
     * Retrieve the issue timestamp of this assertion.
     *
     * @return int The issue timestamp of this assertion, as an UNIX timestamp.
     */
    public function getIssueInstant(): int
    {
        return $this->issueInstant;
    }


    /**
     * Set the issue timestamp of this assertion.
     *
     * @param int|null $issueInstant The new issue timestamp of this assertion, as an UNIX timestamp.
     */
    public function setIssueInstant(?int $issueInstant): void
    {
        if ($issueInstant === null) {
            $issueInstant = Temporal::getTime();
        }

        $this->issueInstant = $issueInstant;
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
     * Set the issuer of this message.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer The new issuer of this assertion.
     */
    public function setIssuer(Issuer $issuer): void
    {
        $this->issuer = $issuer;
    }


    /**
     * Retrieve the SubjectConfirmation elements we have in our Subject element.
     *
     * @return array Array of \SimpleSAML\SAML2\XML\saml\SubjectConfirmation elements.
     */
    public function getSubjectConfirmation(): array
    {
        return $this->SubjectConfirmation;
    }


    /**
     * Set the SubjectConfirmation elements that should be included in the assertion.
     *
     * @param array $SubjectConfirmation Array of \SimpleSAML\SAML2\XML\saml\SubjectConfirmation elements.
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function setSubjectConfirmation(array $SubjectConfirmation): void
    {
        Assert::allIsInstanceOf($SubjectConfirmation, SubjectConfirmation::class);
        $this->SubjectConfirmation = $SubjectConfirmation;
    }


    /**
     * @return bool
     */
    public function wasSignedAtConstruction(): bool
    {
        return $this->wasSignedAtConstruction;
    }


    /**
     * Convert XML into an Assertion
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Assertion', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Assertion::NS, InvalidDOMElementException::class);
        Assert::same(self::getAttribute($xml, 'Version'), '2.0', 'Unsupported version: %s');

        $issueInstant = XMLUtils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::minCount($issuer, 1, 'Missing <saml:Issuer> in assertion.', MissingElementException::class);
        Assert::maxCount($issuer, 1, 'More than one <saml:Issuer> in assertion.', TooManyElementsException::class);

        $subject = Subject::getChildrenOfClass($xml);
        Assert::maxCount($subject, 1, 'More than one <saml:Subject> in <saml:Assertion>', TooManyElementsException::class);

        $conditions = Conditions::getChildrenOfClass($xml);
        Assert::maxCount($conditions, 1, 'More than one <saml:Conditions> in <saml:Assertion>.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one <ds:Signature> element is allowed.', TooManyElementsException::class);

        $authnStatement = AuthnStatement::getChildrenOfClass($xml);
        $attrStatement = AttributeStatement::getChildrenOfClass($xml);
        $statements = Statement::getChildrenOfClass($xml);

        $assertion = new self(
            array_pop($issuer),
            self::getAttribute($xml, 'ID'),
            $issueInstant,
            array_pop($subject),
            array_pop($conditions),
            array_merge($authnStatement, $attrStatement, $statements)
        );

        if (!empty($signature)) {
            $assertion->setSignature($signature[0]);
            $assertion->wasSignedAtConstruction = true;
        }

        return $assertion;
    }


    /**
     * Convert this assertion to an XML element.
     *
     * @param \DOMElement|null $parentElement The DOM node the assertion should be created in.
     *
     * @return \DOMElement This assertion.
     * @throws \Exception
     */
    public function toXML(DOMElement $parentElement = null): DOMElement
    {
        $e = $this->instantiateParentElement($parentElement);

        $e->setAttribute('Version', '2.0');
        $e->setAttribute('ID', $this->id);
        $e->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->issueInstant));

        $issuer = $this->issuer->toXML($e);

        if ($this->subject !== null) {
            $this->subject->toXML($e);
        }

        if ($this->conditions !== null) {
            $this->conditions->toXML($e);
        }

        foreach ($this->statements as $statement) {
            $statement->toXML($e);
        }

        if ($this->signingKey !== null) {
            SecurityUtils::insertSignature($this->signingKey, $this->certificates, $e, $issuer->nextSibling);
        }

        return $e;
    }
}
