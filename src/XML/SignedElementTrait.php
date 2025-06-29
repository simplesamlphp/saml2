<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XMLSecurity\Exception\ReferenceValidationFailedException;
use SimpleSAML\XMLSecurity\XML\ds\Signature;
use SimpleSAML\XMLSecurity\XML\SignedElementTrait as BaseSignedElementTrait;

/**
 * Helper trait for processing signed elements.
 *
 * @package simplesamlphp/saml2
 */
trait SignedElementTrait
{
    use BaseSignedElementTrait;


    /**
     * Initialize a signed element from XML.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\Signature $signature The ds:Signature object
     */
    protected function setSignature(Signature $signature): void
    {
        /**
         * Signatures MUST contain a single <ds:Reference> containing a same-document reference to the ID
         * attribute value of the root element of the assertion or protocol message being signed. For example, if the
         * ID attribute value is "foo", then the URI attribute in the <ds:Reference> element MUST be "#foo".
         */

        $references = $signature->getSignedInfo()->getReferences();
        Assert::count($references, 1, "A signature needs to have exactly one Reference, %d found.");

        $reference = array_pop($references);
        Assert::notNull($reference->getURI(), "URI attribute not found.", ReferenceValidationFailedException::class);
        Assert::validURI($reference->getURI(), ReferenceValidationFailedException::class);
        Assert::startsWith(
            $reference->getURI(),
            '#',
            "Reference must contain a same-document reference to the ID-attribute of the root element.",
            ReferenceValidationFailedException::class,
        );

        /**
         * E91: Disallow <ds:Object> element in signatures
         *
         * The <ds:Object> element is not defined for use with SAML signatures, and SHOULD NOT be present.
         */

        Assert::isEmpty(
            $signature->getObjects(),
            ProtocolViolationException::class,
            'The <ds:Object> element is not defined for use with SAML signatures, and SHOULD NOT be present.',
        );

        $this->signature = $signature;
    }


    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }
}
