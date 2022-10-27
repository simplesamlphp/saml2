<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMDocument;
use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utilities\Temporal;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XMLSecurity\Exception\NoSignatureFoundException;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\XML\SignableElementInterface;
use SimpleSAML\XMLSecurity\XML\SignableElementTrait;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;
use SimpleSAML\XMLSecurity\XML\SignedElementTrait;

use function array_pop;
use function gmdate;

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
     * The identifier of this message.
     *
     * @var string
     */
    protected string $id;

    /**
     * The version of this message.
     *
     * @var string
     */
    protected string $version = '2.0';

    /**
     * The issue timestamp of this message, as an UNIX timestamp.
     *
     * @var int
     */
    protected int $issueInstant;

    /**
     * The destination URL of this message if it is known.
     *
     * @var string|null
     */
    protected ?string $destination = null;

    /**
     * The destination URL of this message if it is known.
     *
     * @var string|null
     */
    protected ?string $consent;

    /**
     * The entity id of the issuer of this message, or null if unknown.
     *
     * @var \SimpleSAML\SAML2\XML\saml\Issuer|null
     */
    protected ?Issuer $issuer = null;

    /**
     * The RelayState associated with this message.
     *
     * @var string|null
     */
    protected ?string $relayState = null;

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
     * Initialize a message.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param int|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     * @param string|null $relayState
     *
     * @throws \Exception
     */
    protected function __construct(
        ?Issuer $issuer = null,
        ?string $id = null,
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
        ?string $relayState = null
    ) {
        $this->setIssuer($issuer);
        $this->setId($id);
        $this->setIssueInstant($issueInstant);
        $this->setDestination($destination);
        $this->setConsent($consent);
        $this->setExtensions($extensions);
        $this->setRelayState($relayState);
    }


    /**
     * Retrieve the identifier of this message.
     *
     * @return string The identifier of this message
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Set the identifier of this message.
     *
     * @param string|null $id The new identifier of this message
     */
    private function setId(?string $id): void
    {
        Assert::nullOrNotWhitespaceOnly($id);

        if ($id === null) {
            $id = Utils::getContainer()->generateId();
        }

        $this->id = $id;
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
     * @return int The issue timestamp of this message, as an UNIX timestamp
     */
    public function getIssueInstant(): int
    {
        return $this->issueInstant;
    }


    /**
     * Set the issue timestamp of this message.
     *
     * @param int|null $issueInstant The new issue timestamp of this message, as an UNIX timestamp
     */
    private function setIssueInstant(?int $issueInstant): void
    {
        if ($issueInstant === null) {
            $issueInstant = Temporal::getTime();
        }

        $this->issueInstant = $issueInstant;
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
     * Set the destination of this message.
     *
     * @param string|null $destination The new destination of this message
     */
    private function setDestination(string $destination = null): void
    {
        Assert::nullOrValidURI($destination); // Covers the empty string
        $this->destination = $destination;
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
     * Set the given consent for this message.
     * Most likely (though not required) a value of urn:oasis:names:tc:SAML:2.0:consent.
     *
     * @see \SimpleSAML\SAML2\Constants
     * @param string|null $consent
     */
    private function setConsent(?string $consent): void
    {
        Assert::nullOrValidURI($consent); // Covers the empty string
        $this->consent = $consent;
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
     * Set the issuer of this message.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer The new issuer of this message
     */
    private function setIssuer(Issuer $issuer = null): void
    {
        $this->issuer = $issuer;
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
     * Retrieve the RelayState associated with this message.
     *
     * @return string|null The RelayState, or NULL if no RelayState is given
     */
    public function getRelayState(): ?string
    {
        return $this->relayState;
    }


    /**
     * Set the RelayState associated with this message.
     *
     * @param string|null $relayState The new RelayState
     */
    public function setRelayState(string $relayState = null): void
    {
        Assert::nullOrNotWhitespaceOnly($relayState);

        $this->relayState = $relayState;
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
     * @return array|null
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
        $root->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getIssueInstant()));

        if ($this->getDestination() !== null) {
            $root->setAttribute('Destination', $this->getDestination());
        }

        if ($this->getConsent() !== null && $this->getConsent() !== C::CONSENT_UNSPECIFIED) {
            $root->setAttribute('Consent', $this->getConsent());
        }

        $this->getIssuer()?->toXML($root);

        if ($this->getExtensions() !== null && !$this->getExtensions()->isEmptyElement()) {
            $this->getExtensions()->toXML($root);
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

            $node = $parent->ownerDocument?->importNode($this->xml, true);
            $parent->appendChild($node);
            return $parent;
        }

        $e = $this->toUnsignedXML($parent);

        if ($this->signer !== null) {
            $signedXML = $this->doSign($e);

            // Test for an Issuer
            $messageElements = XPath::xpQuery($signedXML, './saml_assertion:Issuer', XPath::getXPath($signedXML));
            $issuer = array_pop($messageElements);

            $signedXML->insertBefore($this->signature->toXML($signedXML), $issuer->nextSibling);
            return $signedXML;
        }

        return $e;
    }
}
