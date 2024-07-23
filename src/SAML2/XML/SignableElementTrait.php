<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmInterface;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\RuntimeException;
use SimpleSAML\XMLSecurity\Exception\UnsupportedAlgorithmException;
use SimpleSAML\XMLSecurity\Utils\XML;
use SimpleSAML\XMLSecurity\XML\ds\CanonicalizationMethod;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\Signature;
use SimpleSAML\XMLSecurity\XML\ds\SignatureMethod;
use SimpleSAML\XMLSecurity\XML\ds\SignatureValue;
use SimpleSAML\XMLSecurity\XML\ds\SignedInfo;
use SimpleSAML\XMLSecurity\XML\ds\Transform;
use SimpleSAML\XMLSecurity\XML\ds\Transforms;
use SimpleSAML\XMLSecurity\XML\SignableElementTrait as BaseSignableElementTrait;

use function base64_encode;

/**
 * Helper trait for processing signable elements.
 *
 * @package simplesamlphp/saml2
 */
trait SignableElementTrait
{
    use BaseSignableElementTrait;


    /**
     * Sign the current element.
     *
     * The signature will not be applied until toXML() is called.
     *
     * @param \SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmInterface $signer The actual signer implementation
     * to use.
     * @param string $canonicalizationAlg The identifier of the canonicalization algorithm to use.
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo|null $keyInfo A KeyInfo object to add to the signature.
     */
    public function sign(
        SignatureAlgorithmInterface $signer,
        string $canonicalizationAlg = C::C14N_EXCLUSIVE_WITHOUT_COMMENTS,
        ?KeyInfo $keyInfo = null,
    ): void {
        /**
         * 5.4.2: SAML assertions and protocol messages MUST supply a value for the ID attribute
         * on the root element of the assertion or protocol message being signed.
         */
        Assert::notNull($this->getID(), "Signable element must have an ID set before it can be signed.");

        $this->signer = $signer;
        $this->keyInfo = $keyInfo;
        Assert::oneOf(
            $canonicalizationAlg,
            [
                C::C14N_INCLUSIVE_WITH_COMMENTS,
                C::C14N_EXCLUSIVE_WITHOUT_COMMENTS,
                C::C14N_EXCLUSIVE_WITH_COMMENTS,
                C::C14N_EXCLUSIVE_WITHOUT_COMMENTS,
            ],
            'Unsupported canonicalization algorithm: %s',
            UnsupportedAlgorithmException::class,
        );
        $this->c14nAlg = $canonicalizationAlg;
    }


    /**
     * Do the actual signing of the document.
     *
     * Note that this method does not insert the signature in the returned \DOMElement. The signature will be available
     * in $this->signature as a \SimpleSAML\XMLSecurity\XML\ds\Signature object, which can then be converted to XML
     * calling toXML() on it, passing the \DOMElement value returned here as a parameter. The resulting \DOMElement
     * can then be inserted in the position desired.
     *
     * E.g.:
     *     $xml = // our XML to sign
     *     $signedXML = $this->doSign($xml);
     *     $signedXML->appendChild($this->signature->toXML($signedXML));
     *
     * @param \DOMElement $xml The element to sign.
     * @return \DOMElement The signed element, without the signature attached to it just yet.
     */
    protected function doSign(DOMElement $xml): DOMElement
    {
        Assert::notNull(
            $this->signer,
            'Cannot call toSignedXML() without calling sign() first.',
            RuntimeException::class,
        );

        $algorithm = $this->signer->getAlgorithmId();
        $digest = $this->signer->getDigest();

        $transforms = new Transforms([
            /**
             * 5.4.1: SAML assertions and protocols MUST use enveloped signatures when
             * signing assertions and protocol messages
             */
            new Transform(C::XMLDSIG_ENVELOPED),
            new Transform($this->c14nAlg),
        ]);

        $canonicalDocument = XML::processTransforms($transforms, $xml);

        $signedInfo = new SignedInfo(
            new CanonicalizationMethod($this->c14nAlg),
            new SignatureMethod($algorithm),
            [$this->getReference($digest, $transforms, $xml, $canonicalDocument)],
        );

        $signingData = $signedInfo->canonicalize($this->c14nAlg);
        $signedData = base64_encode($this->signer->sign($signingData));

        $this->signature = new Signature($signedInfo, new SignatureValue($signedData), $this->keyInfo);
        return DOMDocumentFactory::fromString($canonicalDocument)->documentElement;
    }
}
