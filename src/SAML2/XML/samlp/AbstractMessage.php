<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utilities\Temporal;
use SAML2\Utils;
use SAML2\XML\ExtendableElementTrait;
use SAML2\XML\saml\Issuer;
use SAML2\XML\SignedElementInterface;
use SAML2\XML\SignedElementTrait;
use Webmozart\Assert\Assert;

/**
 * Base class for all SAML 2 messages.
 *
 * Implements what is common between the samlp:RequestAbstractType and
 * samlp:StatusResponseType element types.
 */
abstract class AbstractMessage extends AbstractSamlpElement implements SignedElementInterface
{
    use ExtendableElementTrait;
    use SignedElementTrait;

    /**
     * The identifier of this message.
     *
     * @var string
     */
    protected $id;

    /**
     * The version of this message.
     *
     * @var string
     */
    protected $version;

    /**
     * The issue timestamp of this message, as an UNIX timestamp.
     *
     * @var int
     */
    protected $issueInstant;

    /**
     * The destination URL of this message if it is known.
     *
     * @var string|null
     */
    protected $destination = null;

    /**
     * The destination URL of this message if it is known.
     *
     * @var string|null
     */
    protected $consent;

    /**
     * The entity id of the issuer of this message, or null if unknown.
     *
     * @var \SAML2\XML\saml\Issuer|null
     */
    protected $issuer = null;

    /**
     * The RelayState associated with this message.
     *
     * @var string|null
     */
    protected $relayState = null;

    /**
     * The \DOMDocument we are currently building.
     *
     * This variable is used while generating XML from this message. It holds the
     * \DOMDocument of the XML we are generating.
     *
     * @var \DOMDocument
     */
    protected $document;

    /**
     * @var bool
     */
    protected $messageContainedSignatureUponConstruction = false;

    /**
     * Available methods for validating this message.
     *
     * @var array
     */
    private $validators = [];

    /**
     * @var null|string
     */
    private $signatureMethod = null;


    /**
     * Initialize a message.
     *
     * @param \SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string|null $version
     * @param int|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \Exception
     */
    protected function __construct(
        ?Issuer $issuer = null,
        ?string $id = null,
        ?string $version = null,
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null
    ) {
        $this->setIssuer($issuer);
        $this->setId($id);
        $this->setVersion($version);
        $this->setIssueInstant($issueInstant);
        $this->setDestination($destination);
        $this->setConsent($consent);
        $this->setExtensions($extensions);
    }


    /**
     * Validate the signature element of a SAML message, and configure this object appropriately to perform the
     * signature verification afterwards.
     *
     * Please note this method does NOT verify the signature, it just validates the signature construction and prepares
     * this object to do the verification.
     *
     * @param \DOMElement $xml The SAML message whose signature we want to validate.
     * @return void
     */
    protected function validateSignature(DOMElement $xml): void
    {
        try {
            /** @var \DOMAttr[] $signatureMethod */
            $signatureMethod = Utils::xpQuery($xml, './ds:Signature/ds:SignedInfo/ds:SignatureMethod/@Algorithm');
            if (empty($signatureMethod)) {
                throw new \Exception('No Algorithm specified in signature.');
            }

            $sig = Utils::validateElement($xml);

            if ($sig !== false) {
                $this->messageContainedSignatureUponConstruction = true;
                $this->certificates = $sig['Certificates'];
                $this->validators[] = [
                    'Function' => ['\SAML2\Utils', 'validateSignature'],
                    'Data' => $sig,
                ];
                $this->signatureMethod = $signatureMethod[0]->value;
            }
        } catch (\Exception $e) {
            // ignore signature validation errors
        }
    }


    /**
     * Add a method for validating this message.
     *
     * This function is used by the HTTP-Redirect binding, to make it possible to
     * check the signature against the one included in the query string.
     *
     * @param callable $function The function which should be called
     * @param mixed $data The data that should be included as the first parameter to the function
     * @return void
     */
    public function addValidator(callable $function, $data): void
    {
        $this->validators[] = [
            'Function' => $function,
            'Data' => $data,
        ];
    }


    /**
     * Validate this message against a public key.
     *
     * true is returned on success, false is returned if we don't have any
     * signature we can validate. An exception is thrown if the signature
     * validation fails.
     *
     * @param XMLSecurityKey $key The key we should check against
     * @throws \Exception
     * @return bool true on success, false when we don't have a signature
     */
    public function validate(XMLSecurityKey $key): bool
    {
        if (count($this->validators) === 0) {
            return false;
        }

        $exceptions = [];

        foreach ($this->validators as $validator) {
            $function = $validator['Function'];
            $data = $validator['Data'];

            try {
                call_user_func($function, $data, $key);
                /* We were able to validate the message with this validator. */

                return true;
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        /* No validators were able to validate the message. */
        throw $exceptions[0];
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
     * @return void
     */
    private function setId(?string $id): void
    {
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
     * Set the version of this message.
     *
     * @param string|null $id The version of this message
     * @return void
     */
    private function setVersion(?string $version): void
    {
        if ($version === null) {
            $version = '2.0';
        }

        Assert::same($version, '2.0');
        $this->version = $version;
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
     * @return void
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
     * @return void
     */
    private function setDestination(string $destination = null): void
    {
        $this->destination = $destination;
    }


    /**
     * Get the given consent for this message.
     * Most likely (though not required) a value of urn:oasis:names:tc:SAML:2.0:consent.
     *
     * @see \SAML2\Constants
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
     * @see \SAML2\Constants
     * @param string|null $consent
     * @return void
     */
    private function setConsent(?string $consent): void
    {
        $this->consent = $consent;
    }


    /**
     * Retrieve the issuer if this message.
     *
     * @return \SAML2\XML\saml\Issuer|null The issuer of this message, or NULL if no issuer is given
     */
    public function getIssuer(): ?Issuer
    {
        return $this->issuer;
    }


    /**
     * Set the issuer of this message.
     *
     * @param \SAML2\XML\saml\Issuer|null $issuer The new issuer of this message
     * @return void
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
     * @return void
     */
    public function setRelayState(string $relayState = null): void
    {
        $this->relayState = $relayState;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $root = $this->instantiateParentElement($parent);

        /* Ugly hack to add another namespace declaration to the root element. */
        $root->setAttributeNS(Constants::NS_SAML, 'saml:tmp', 'tmp');
        $root->removeAttributeNS(Constants::NS_SAML, 'tmp');

        $root->setAttribute('ID', $this->id);
        $root->setAttribute('Version', '2.0');
        $root->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->issueInstant));

        if ($this->destination !== null) {
            $root->setAttribute('Destination', $this->destination);
        }
        if ($this->consent !== null && $this->consent !== Constants::CONSENT_UNSPECIFIED) {
            $root->setAttribute('Consent', $this->consent);
        }

        if ($this->issuer !== null) {
            $this->issuer->toXML($root);
        }

        if ($this->Extensions !== null && !$this->Extensions->isEmptyElement()) {
            $this->Extensions->toXML($root);
        }

        return $root;
    }


    /**
     * Convert this message to a signed XML document.
     * This method sign the resulting XML document if the private key for
     * the signature is set.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    public function toSignedXML(): DOMElement
    {
        $root = $this->toXML();

        if ($this->signingKey === null) {
            /* We don't have a key to sign it with. */

            return $root;
        }

        /* Find the position we should insert the signature node at. */
        if ($this->issuer !== null) {
            /*
             * We have an issuer node. The signature node should come
             * after the issuer node.
             */
            $issuerNode = $root->firstChild;
            /** @psalm-suppress PossiblyNullPropertyFetch */
            $insertBefore = $issuerNode->nextSibling;
        } else {
            /* No issuer node - the signature element should be the first element. */
            $insertBefore = $root->firstChild;
        }

        Utils::insertSignature($this->signingKey, $this->certificates, $root, $insertBefore);

        return $root;
    }


    /**
     * @return null|string
     */
    public function getSignatureMethod(): ?string
    {
        return $this->signatureMethod;
    }
}
