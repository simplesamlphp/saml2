<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Utils\Random as RandomUtils;
use SimpleSAML\XMLSecurity\XML\SignableElementInterface;
use SimpleSAML\XMLSecurity\XML\SignableElementTrait;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;
use SimpleSAML\XMLSecurity\XML\SignedElementTrait;

use function array_pop;

/**
 * Base class for all SAML 2 messages.
 *
 * Implements what is common between the samlp:RequestAbstractType and
 * samlp:StatusResponseType element types.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMessage extends AbstractSamlpElement implements SignableElementInterface, SignedElementInterface
{
    use ExtendableElementTrait;
    use SignableElementTrait;
    use SignedElementTrait;


    /**
     * The \DOMDocument we are currently building.
     *
     * This variable is used while generating XML from this message. It holds the
     * \DOMDocument of the XML we are generating.
     *
     * @var \DOMDocument|null
     */
    protected ?DOMDocument $document = null;

    /** @var bool */
    protected bool $messageContainedSignatureUponConstruction = false;

    /**
     * The original signed XML
     *
     * @var \DOMElement
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected DOMElement $xml;


    /**
     * Initialize a message.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param \DateTimeImmutable|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \Exception
     */
    protected function __construct(
        protected ?Issuer $issuer = null,
        protected ?string $id = null,
        protected string $version = '2.0',
        protected ?DateTimeImmutable $issueInstant = null,
        protected ?string $destination = null,
        protected ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::nullOrSame($issueInstant?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::nullOrValidNCName($id); // Covers the empty string
        Assert::nullOrValidURI($destination); // Covers the empty string
        Assert::nullOrValidURI($consent); // Covers the empty string

        $this->setExtensions($extensions);
    }


    /**
     * Retrieve the identifier of this message.
     *
     * @return string The identifier of this message
     */
    public function getId(): string
    {
        if ($this->id === null) {
            return (new RandomUtils())->generateId();
        }

        return $this->id;
    }


    /**
     * Retrieve the version of this message.
     *
     * @return string The version of this message
     */
    public function getVersion(): string
    {
        return $this->version;
    }


    /**
     * Retrieve the issue timestamp of this message.
     *
     * @return \DateTimeImmutable The issue timestamp of this message, as an UNIX timestamp
     */
    public function getIssueInstant(): DateTimeImmutable
    {
        if ($this->issueInstant === null) {
            return Utils::getContainer()->getClock()->now();
        }

        return $this->issueInstant;
    }


    /**
     * Retrieve the destination of this message.
     *
     * @return string|null The destination of this message, or NULL if no destination is given
     */
    public function getDestination(): ?string
    {
        return $this->destination;
    }


    /**
     * Get the given consent for this message.
     * Most likely (though not required) a value of urn:oasis:names:tc:SAML:2.0:consent.
     *
     * @see \SimpleSAML\SAML2\Constants
     * @return string|null Consent
     */
    public function getConsent(): ?string
    {
        return $this->consent;
    }


    /**
     * Retrieve the issuer if this message.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Issuer|null The issuer of this message, or NULL if no issuer is given
     */
    public function getIssuer(): ?Issuer
    {
        return $this->issuer;
    }


    /**
     * Query whether or not the message contained a signature at the root level when the object was constructed.
     *
     * @return bool
     */
    public function isMessageConstructedWithSignature(): bool
    {
        return $this->messageContainedSignatureUponConstruction;
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
    protected function setXML(DOMElement $xml): void
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


    /**
     * @return string[]|null
     */
    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $root = $this->instantiateParentElement($parent);

        /* Ugly hack to add another namespace declaration to the root element. */
        $root->setAttributeNS(C::NS_SAML, 'saml:tmp', 'tmp');
        $root->removeAttributeNS(C::NS_SAML, 'tmp');

        $root->setAttribute('Version', $this->getVersion());
        $root->setAttribute('ID', $this->getId());
        $root->setAttribute('IssueInstant', $this->getIssueInstant()->format(C::DATETIME_FORMAT));

        if ($this->getDestination() !== null) {
            $root->setAttribute('Destination', $this->getDestination());
        }

        if ($this->getConsent() !== null && $this->getConsent() !== C::CONSENT_UNSPECIFIED) {
            $root->setAttribute('Consent', $this->getConsent());
        }

        $this->getIssuer()?->toXML($root);

        $extensions = $this->getExtensions();
        if ($extensions !== null && !$extensions->isEmptyElement()) {
            $extensions->toXML($root);
        }

        return $root;
    }


    /**
     * Create XML from this class
     *
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        if ($this->isSigned() === true && $this->signer === null) {
            // We already have a signed document and no signer was set to re-sign it
            if ($parent === null) {
                return $this->xml;
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
