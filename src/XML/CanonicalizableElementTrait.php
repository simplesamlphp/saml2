<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use DOMElement;
use SimpleSAML\XMLSecurity\Assert\Assert;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\CanonicalizationFailedException;
use SimpleSAML\XMLSecurity\Exception\ReferenceValidationFailedException;
use SimpleSAML\XMLSecurity\XML\CanonicalizableElementTrait as BaseCanonicalizableElementTrait;
use SimpleSAML\XMLSecurity\XML\ds\Transforms;

/**
 * A trait implementing the CanonicalizableElementInterface.
 *
 * @package simplesamlphp/xml-security
 */
trait CanonicalizableElementTrait
{
    use BaseCanonicalizableElementTrait;


    /**
     * Process all transforms specified by a given Reference element.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\Transforms $transforms The transforms to apply.
     * @param \DOMElement $data The data referenced.
     *
     * @return string The canonicalized data after applying all transforms specified by $ref.
     *
     * @see http://www.w3.org/TR/xmldsig-core/#sec-ReferenceProcessingModel
     */
    public function processTransforms(
        Transforms $transforms,
        DOMElement $data,
    ): string {
        Assert::maxCount(
            $transforms->getTransform(),
            C::MAX_TRANSFORMS,
            ReferenceValidationFailedException::class,
            'Too many transforms.',
        );

        $canonicalMethod = C::C14N_EXCLUSIVE_WITHOUT_COMMENTS;
        $arXPath = null;
        $prefixList = null;

        foreach ($transforms->getTransform() as $transform) {
            $canonicalMethod = $transform->getAlgorithm()->getValue();
            switch ($canonicalMethod) {
                case C::XMLDSIG_ENVELOPED:
                    break;
                case C::C14N_EXCLUSIVE_WITHOUT_COMMENTS:
                case C::C14N_EXCLUSIVE_WITH_COMMENTS:
                    $inclusiveNamespaces = $transform->getInclusiveNamespaces();
                    if ($inclusiveNamespaces !== null) {
                        $prefixes = $inclusiveNamespaces->getPrefixes();
                        if ($prefixes !== null) {
                            $prefixList = array_map('strval', $prefixes->toArray());
                        }
                    }
                    break;
                default:
                    throw new CanonicalizationFailedException(sprintf(
                        'Message rejected due to unsupported canonicalization transform; %s',
                        $canonicalMethod,
                    ));
            }
        }

        return $this->canonicalizeData($data, $canonicalMethod, $arXPath, $prefixList);
    }
}
