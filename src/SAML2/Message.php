<?php

namespace SAML2;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Utilities\Temporal;
use SAML2\XML\samlp\Extensions;

/**
 * Base class for all SAML 2 messages.
 *
 * Implements what is common between the samlp:RequestAbstractType and
 * samlp:StatusResponseType element types.
 *
 * @package SimpleSAMLphp
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class Message implements SignedElement
{
    /**
     * Request extensions.
     *
     * @var array
     */
    protected $extensions;

    /**
     * The name of the root element of the DOM tree for the message.
     *
     * Used when creating a DOM tree from the message.
     *
     * @var string
     */
    private $tagName;

    /**
     * The identifier of this message.
     *
     * @var string
     */
    private $id;

    /**
     * The issue timestamp of this message, as an UNIX timestamp.
     *
     * @var int
     */
    private $issueInstant;

    /**
     * The destination URL of this message if it is known.
     *
     * @var string|null
     */
    private $destination;

    /**
     * The destination URL of this message if it is known.
     *
     * @var string|null
     */
    private $consent = Constants::CONSENT_UNSPECIFIED;

    /**
     * The entity id of the issuer of this message, or null if unknown.
     *
     * @var string|object|null
     */
    private $issuer;

    /**
     * The RelayState associated with this message.
     *
     * @var string|null
     */
    private $relayState;

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
     * The private key we should use to sign the message.
     *
     * The private key can be null, in which case the message is sent unsigned.
     *
     * @var XMLSecurityKey|null
     */
    private $signatureKey;

    /**
     * @var bool
     */
    protected $messageContainedSignatureUponConstruction = false;

    /**
     * List of certificates that should be included in the message.
     *
     * @var array
     */
    private $certificates;

    /**
     * Available methods for validating this message.
     *
     * @var array
     */
    private $validators;

    /**
     * @var null|string
     */
    private $signatureMethod;

    /**
     * Initialize a message.
     *
     * This constructor takes an optional parameter with a \DOMElement. If this
     * parameter is given, the message will be initialized with data from that
     * XML element.
     *
     * If no XML element is given, the message is initialized with suitable
     * default values.
     *
     * @param string          $tagName The tag name of the root element.
     * @param \DOMElement|null $xml     The input message.
     * @throws \Exception
     */
    protected function __construct($tagName, \DOMElement $xml = null)
    {
        assert('is_string($tagName)');
        $this->tagName = $tagName;

        $this->id = Utils::getContainer()->generateId();
        $this->issueInstant = Temporal::getTime();
        $this->certificates = array();
        $this->validators = array();

        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('ID')) {
            throw new \Exception('Missing ID attribute on SAML message.');
        }
        $this->id = $xml->getAttribute('ID');

        if ($xml->getAttribute('Version') !== '2.0') {
            /* Currently a very strict check. */
            throw new \Exception('Unsupported version: ' . $xml->getAttribute('Version'));
        }

        $this->issueInstant = Utils::xsDateTimeToTimestamp($xml->getAttribute('IssueInstant'));

        if ($xml->hasAttribute('Destination')) {
            $this->destination = $xml->getAttribute('Destination');
        }

        if ($xml->hasAttribute('Consent')) {
            $this->consent = $xml->getAttribute('Consent');
        }

        $issuer = Utils::xpQuery($xml, './saml_assertion:Issuer');
        if (!empty($issuer)) {
            $this->issuer = trim($issuer[0]->textContent);
        }

        /* Validate the signature element of the message. */
        try {
            /** @var null|\DOMAttr $signatureMethod */
            $signatureMethod = Utils::xpQuery($xml, './ds:Signature/ds:SignedInfo/ds:SignatureMethod/@Algorithm');

            $sig = Utils::validateElement($xml);

            if ($sig !== false) {
                $this->messageContainedSignatureUponConstruction = true;
                $this->certificates = $sig['Certificates'];
                $this->validators[] = array(
                    'Function' => array('\SAML2\Utils', 'validateSignature'),
                    'Data' => $sig,
                    );
                $this->signatureMethod = $signatureMethod[0]->value;
            }
        } catch (\Exception $e) {
            /* Ignore signature validation errors. */
        }

        $this->extensions = Extensions::getList($xml);
    }

    /**
     * Add a method for validating this message.
     *
     * This function is used by the HTTP-Redirect binding, to make it possible to
     * check the signature against the one included in the query string.
     *
     * @param callback $function The function which should be called.
     * @param mixed    $data     The data that should be included as the first parameter to the function.
     */
    public function addValidator($function, $data)
    {
        assert('is_callable($function)');

        $this->validators[] = array(
            'Function' => $function,
            'Data' => $data,
            );
    }

    /**
     * Validate this message against a public key.
     *
     * true is returned on success, false is returned if we don't have any
     * signature we can validate. An exception is thrown if the signature
     * validation fails.
     *
     * @param  XMLSecurityKey $key The key we should check against.
     * @return boolean        true on success, false when we don't have a signature.
     * @throws \Exception
     */
    public function validate(XMLSecurityKey $key)
    {
        if (count($this->validators) === 0) {
            return false;
        }

        $exceptions = array();

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
     * @return string The identifier of this message.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the identifier of this message.
     *
     * @param string $id The new identifier of this message.
     */
    public function setId($id)
    {
        assert('is_string($id)');

        $this->id = $id;
    }

    /**
     * Retrieve the issue timestamp of this message.
     *
     * @return int The issue timestamp of this message, as an UNIX timestamp.
     */
    public function getIssueInstant()
    {
        return $this->issueInstant;
    }

    /**
     * Set the issue timestamp of this message.
     *
     * @param int $issueInstant The new issue timestamp of this message, as an UNIX timestamp.
     */
    public function setIssueInstant($issueInstant)
    {
        assert('is_int($issueInstant)');

        $this->issueInstant = $issueInstant;
    }

    /**
     * Retrieve the destination of this message.
     *
     * @return string|null The destination of this message, or NULL if no destination is given.
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set the destination of this message.
     *
     * @param string|null $destination The new destination of this message.
     */
    public function setDestination($destination)
    {
        assert('is_string($destination) || is_null($destination)');

        $this->destination = $destination;
    }

    /**
     * Set the given consent for this message.
     *
     * Most likely (though not required) a value of rn:oasis:names:tc:SAML:2.0:consent.
     * @see \SAML2\Constants
     *
     * @param string $consent
     */
    public function setConsent($consent)
    {
        assert('is_string($consent)');

        $this->consent = $consent;
    }

    /**
     * Set the given consent for this message.
     *
     * Most likely (though not required) a value of rn:oasis:names:tc:SAML:2.0:consent.
     * @see \SAML2\Constants
     *
     * @return string Consent
     */
    public function getConsent()
    {
        return $this->consent;
    }

    /**
     * Retrieve the issuer if this message.
     *
     * @return string|null The issuer of this message, or NULL if no issuer is given.
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * Set the issuer of this message.
     *
     * @param string|object|null $issuer The new issuer of this message.
     */
    public function setIssuer($issuer)
    {
        assert('is_string($issuer) || is_object($issuer) || is_null($issuer)');

        $this->issuer = $issuer;
    }

    /**
     * Query whether or not the message contained a signature at the root level when the object was constructed.
     *
     * @return bool
     */
    public function isMessageConstructedWithSignature()
    {
        return $this->messageContainedSignatureUponConstruction;
    }

    /**
     * Retrieve the RelayState associated with this message.
     *
     * @return string|null The RelayState, or NULL if no RelayState is given.
     */
    public function getRelayState()
    {
        return $this->relayState;
    }

    /**
     * Set the RelayState associated with this message.
     *
     * @param string|null $relayState The new RelayState.
     */
    public function setRelayState($relayState)
    {
        assert('is_string($relayState) || is_null($relayState)');

        $this->relayState = $relayState;
    }

    /**
     * Convert this message to an unsigned XML document.
     *
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree.
     */
    public function toUnsignedXML()
    {
        $this->document = DOMDocumentFactory::create();

        $root = $this->document->createElementNS(Constants::NS_SAMLP, 'samlp:' . $this->tagName);
        $this->document->appendChild($root);

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
            if (is_string($this->issuer)){
                Utils::addString($root, \SAML2_Const::NS_SAML, 'saml:Issuer', $this->issuer);
            }elseif (is_object($this->issuer)){
                $this->setIssuerAttribute($root);
            }
        }

        if (!empty($this->extensions)) {
            Extensions::addList($root, $this->extensions);
        }

        return $root;
    }
    
    /**
     * Set attribute for Issuer if is an object.
     *
     */
    private function setIssuerAttribute($root) {
        Utils::addString($root, \SAML2_Const::NS_SAML, 'saml:Issuer', $this->issuer->__toString());
        $issuer = \SAML2_Utils::xpQuery($root, './saml_assertion:Issuer');
        if ($this->issuer->getNameQualifier()){
            $issuer[0]->setAttribute('NameQualifier', $this->issuer->getNameQualifier());
        }
        if ($this->issuer->getFormat()){
            $issuer[0]->setAttribute('Format', $this->issuer->getFormat());
        }
        if ($this->issuer->getSPNameQualifier()){
            $issuer[0]->setAttribute('SPNameQualifier', $this->issuer->getSPNameQualifier());
        }
        if ($this->issuer->getSPProvidedID()){
            $issuer[0]->setAttribute('SPProvidedID', $this->issuer->getSPProvidedID());
        }
    }

    /**
     * Convert this message to a signed XML document.
     *
     * This method sign the resulting XML document if the private key for
     * the signature is set.
     *
     * @return \DOMElement The root element of the DOM tree.
     */
    public function toSignedXML()
    {
        $root = $this->toUnsignedXML();

        if ($this->signatureKey === null) {
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
            $insertBefore = $issuerNode->nextSibling;
        } else {
            /* No issuer node - the signature element should be the first element. */
            $insertBefore = $root->firstChild;
        }


        Utils::insertSignature($this->signatureKey, $this->certificates, $root, $insertBefore);

        return $root;
    }


    /**
     * Retrieve the private key we should use to sign the message.
     *
     * @return XMLSecurityKey|null The key, or NULL if no key is specified.
     */
    public function getSignatureKey()
    {
        return $this->signatureKey;
    }


    /**
     * Set the private key we should use to sign the message.
     *
     * If the key is null, the message will be sent unsigned.
     *
     * @param XMLSecurityKey|null $signatureKey
     */
    public function setSignatureKey(XMLsecurityKey $signatureKey = null)
    {
        $this->signatureKey = $signatureKey;
    }


    /**
     * Set the certificates that should be included in the message.
     *
     * The certificates should be strings with the PEM encoded data.
     *
     * @param array $certificates An array of certificates.
     */
    public function setCertificates(array $certificates)
    {
        $this->certificates = $certificates;
    }


    /**
     * Retrieve the certificates that are included in the message.
     *
     * @return array An array of certificates.
     */
    public function getCertificates()
    {
        return $this->certificates;
    }


    /**
     * Convert an XML element into a message.
     *
     * @param  \DOMElement    $xml The root XML element.
     * @return \SAML2\Message The message.
     * @throws \Exception
     */
    public static function fromXML(\DOMElement $xml)
    {
        if ($xml->namespaceURI !== Constants::NS_SAMLP) {
            throw new \Exception('Unknown namespace of SAML message: ' . var_export($xml->namespaceURI, true));
        }

        switch ($xml->localName) {
            case 'AttributeQuery':
                return new AttributeQuery($xml);
            case 'AuthnRequest':
                return new AuthnRequest($xml);
            case 'LogoutResponse':
                return new LogoutResponse($xml);
            case 'LogoutRequest':
                return new LogoutRequest($xml);
            case 'Response':
                return new Response($xml);
            case 'ArtifactResponse':
                return new ArtifactResponse($xml);
            case 'ArtifactResolve':
                return new ArtifactResolve($xml);
            default:
                throw new \Exception('Unknown SAML message: ' . var_export($xml->localName, true));
        }
    }

    /**
     * Retrieve the Extensions.
     *
     * @return \SAML2\XML\samlp\Extensions.
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Set the Extensions.
     *
     * @param array|null $extensions The Extensions.
     */
    public function setExtensions($extensions)
    {
        assert('is_array($extensions) || is_null($extensions)');

        $this->extensions = $extensions;
    }

    /**
     * @return null|string
     */
    public function getSignatureMethod()
    {
        return $this->signatureMethod;
    }
}
