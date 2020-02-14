<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use DOMElement;
use Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Utilities\Certificate;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Wrapper class for XML signatures
 *
 * @package simplesamlphp/saml2
 */
final class Signature extends AbstractDsElement
{
    /** @var string */
    protected $algorithm;

    /** @var string[] */
    protected $certificates = [];

    /** @var \RobRichards\XMLSecLibs\XMLSecurityKey */
    protected $key;

    /** @var \RobRichards\XMLSecLibs\XMLSecurityDSig */
    protected $signer;


    /**
     * Signature constructor.
     *
     * @param string $algorithm
     * @param string[] $certificates
     * @param XMLSecurityKey|null $key
     *
     * @throws \Exception
     */
    public function __construct(
        string $algorithm,
        array $certificates = [],
        ?XMLSecurityKey $key = null
    ) {
        $this->setAlgorithm($algorithm);
        $this->setCertificates($certificates);
        $this->setKey($key);

        $this->signer = new XMLSecurityDSig();
        $this->signer->idKeys[] = 'ID';
    }


    /**
     * Get the algorithm used by this signature.
     *
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }


    /**
     * Set the algorithm used by this signature.
     *
     * @param string $algorithm
     */
    protected function setAlgorithm(string $algorithm): void
    {
        Assert::notEmpty($algorithm, 'Signature algorithm cannot be empty');
        $this->algorithm = $algorithm;
    }


    /**
     * Get the array of certificates attached to this signature.
     *
     * @return array
     */
    public function getCertificates(): array
    {
        return $this->certificates;
    }


    /**
     * Set the array of certificates (in PEM format) attached to this signature.
     *
     * @param string[] $certificates
     */
    protected function setCertificates(array $certificates): void
    {
        Assert::allStringNotEmpty($certificates, 'Cannot add empty certificates.');
        Assert::allTrue(
            array_map([Certificate::class, 'hasValidStructure'], $certificates),
            'One or more certificates have an invalid format.'
        );
        $this->certificates = $certificates;
    }


    /**
     * @param XMLSecurityKey $key
     */
    protected function setKey(?XMLSecurityKey $key): void
    {
        if ($key !== null) {
            Assert::eq($this->algorithm, $key->getAlgorithm(), 'Key type does not match signature algorithm.');
        }
        $this->key = $key;
    }


    /**
     * @return XMLSecurityDSig
     */
    public function getSigner(): XMLSecurityDSig
    {
        return $this->signer;
    }


    /**
     * @param DOMElement $xml
     *
     * @return object
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Signature');
        Assert::same($xml->namespaceURI, Signature::NS);

        $parent = $xml->parentNode;

        $sigMethod = Utils::xpQuery($xml, './ds:SignedInfo/ds:SignatureMethod');
        Assert::notEmpty($sigMethod, 'Missing ds:SignatureMethod element.');
        /** @var \DOMElement $sigMethod */
        $sigMethod = $sigMethod[0];
        Assert::true(
            $sigMethod->hasAttribute('Algorithm'),
            'Missing "Algorithm" attribute on ds:SignatureMethod element.'
        );

        // now we extract all available X509 certificates in the signature element
        $certificates = [];
        foreach (Utils::xpQuery($xml, './ds:KeyInfo/ds:X509Data/ds:X509Certificate') as $certNode) {
            $certificates[] = Certificate::convertToCertificate(
                str_replace(["\r", "\n", "\t", ' '], '', trim($certNode->textContent))
            );
        }

        $signature = new self($sigMethod->getAttribute('Algorithm'), $certificates);

        $signature->signer->sigNode = $xml;

        // canonicalize the XMLDSig SignedInfo element in the message
        $signature->signer->canonicalizeSignedInfo();

        // validate referenced xml nodes
        if (!$signature->signer->validateReference()) {
            throw new Exception('Digest validation failed.');
        }

        // check that $root is one of the signed nodes
        $rootSigned = false;
        /** @var \DOMNode $signedNode */
        foreach ($signature->signer->getValidatedNodes() as $signedNode) {
            if ($signedNode->isSameNode($parent)) {
                $rootSigned = true;
                break;
            } elseif ($parent->parentNode instanceof \DOMDocument && $signedNode->isSameNode($parent->ownerDocument)) {
                // $parent is the root element of a signed document
                $rootSigned = true;
                break;
            }
        }
        if (!$rootSigned) {
            throw new Exception('The parent element is not signed.');
        }

        return $signature;
    }


    /**
     * @param DOMElement|null $parent
     *
     * @return DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        Assert::notNull($parent, 'Cannot create a Signature without anything to sign.');
        Assert::notNull($this->key, 'Cannot sign without a signing key.');

        // find first child element
        $childElements = Utils::xpQuery($parent, './*');
        $firstChildElement = null;
        if (count($childElements) > 0) {
            $firstChildElement = $childElements[0];
        }

        Utils::insertSignature($this->key, $this->certificates, $parent, $firstChildElement);
        return Utils::xpQuery($parent, './ds:Signature')[0];
    }
}
