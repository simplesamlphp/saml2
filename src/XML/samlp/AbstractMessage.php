<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue};
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\{SignableElementTrait, SignedElementTrait};
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\XML\{SignableElementInterface, SignedElementInterface};

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
    use SignedElementTrait {
        SignedElementTrait::getBlacklistedAlgorithms insteadof SignableElementTrait;
    }


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
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $issueInstant
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \Exception
     */
    protected function __construct(
        protected IDValue $id,
        protected ?Issuer $issuer = null,
        protected ?SAMLDateTimeValue $issueInstant = null,
        protected ?SAMLAnyURIValue $destination = null,
        protected ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        $this->setExtensions($extensions);
    }


    /**
     * Retrieve the identifier of this message.
     *
     * @return \SimpleSAML\XMLSchema\Type\IDValue The identifier of this message
     */
    public function getId(): IDValue
    {
        return $this->id;
    }


    /**
     * Retrieve the issue timestamp of this message.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue The issue timestamp of this message, as an UNIX timestamp
     */
    public function getIssueInstant(): SAMLDateTimeValue
    {
        return $this->issueInstant;
    }


    /**
     * Retrieve the destination of this message.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null The destination of this message,
     *   or NULL if no destination is given
     */
    public function getDestination(): ?SAMLAnyURIValue
    {
        return $this->destination;
    }


    /**
     * Get the given consent for this message.
     * Most likely (though not required) a value of urn:oasis:names:tc:SAML:2.0:consent.
     *
     * @see \SimpleSAML\SAML2\Constants
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null Consent
     */
    public function getConsent(): ?SAMLAnyURIValue
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
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $root = $this->instantiateParentElement($parent);

        /* Ugly hack to add another namespace declaration to the root element. */
//        $root->setAttributeNS(C::NS_SAML, 'saml:tmp', 'tmp');
//        $root->removeAttributeNS(C::NS_SAML, 'tmp');

        $root->setAttribute('Version', '2.0');
        $root->setAttribute('ID', $this->getId()->getValue());
        $root->setAttribute('IssueInstant', $this->getIssueInstant()->getValue());

        if ($this->getDestination() !== null) {
            $root->setAttribute('Destination', $this->getDestination()->getValue());
        }

        if ($this->getConsent() !== null && $this->getConsent()->getValue() !== C::CONSENT_UNSPECIFIED) {
            $root->setAttribute('Consent', $this->getConsent()->getValue());
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
