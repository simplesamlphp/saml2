<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\XMLSchema\Type\DurationValue;
use SimpleSAML\XMLSchema\Type\IDValue;

/**
 * Class to represent a metadata document
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMetadataDocument extends AbstractSignedMdElement
{
    use ExtendableElementTrait;


    /**
     * Generic constructor for SAML metadata documents.
     *
     * @param \SimpleSAML\XMLSchema\Type\IDValue|null $id The ID for this document. Defaults to null.
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $validUntil Unix time of validity for this document.
     *   Defaults to null.
     * @param \SimpleSAML\XMLSchema\Type\DurationValue|null $cacheDuration Maximum time this document can be cached.
     *   Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions. Defaults to null.
     */
    public function __construct(
        protected ?IDValue $id = null,
        protected ?SAMLDateTimeValue $validUntil = null,
        protected ?DurationValue $cacheDuration = null,
        ?Extensions $extensions = null,
    ) {
        if ($extensions !== null) {
            $exts = $extensions->getList();

            /**
             * MDUI 2.1: this element MUST NOT appear more than once within a given <md:Extensions> element.
             */
            $uiInfo = array_values(array_filter($exts, function ($ext) {
                return $ext instanceof UIInfo;
            }));
            Assert::maxCount($uiInfo, 1, ProtocolViolationException::class);

            /**
             * MDUI 2.2: this element MUST NOT appear more than once within a given <md:Extensions> element.
             */
            $discoHints = array_values(array_filter($exts, function ($ext) {
                return $ext instanceof DiscoHints;
            }));
            Assert::maxCount($discoHints, 1, ProtocolViolationException::class);

            /**
             * MDRPI 2.1: this element MUST NOT appear more than once within a given <md:Extensions> element.
             */
            $regInfo = array_values(array_filter($exts, function ($ext) {
                return $ext instanceof RegistrationInfo;
            }));
            Assert::maxCount($regInfo, 1, ProtocolViolationException::class);

            /**
             * MDRPI 2.2: this element MUST NOT appear more than once within a given <md:Extensions> element.
             */
            $pubInfo = array_values(array_filter($exts, function ($ext) {
                return $ext instanceof PublicationInfo;
            }));
            Assert::maxCount($regInfo, 1, ProtocolViolationException::class);

            /**
             * MDRPI 2.3: The <mdrpi:PublicationPath> element MUST NOT appear more than once within the
             * <md:Extensions> element of a given <md:EntitiesDescriptor> or <md:EntityDescriptor> element.
             */
            $pubPath = array_values(array_filter($exts, function ($ext) {
                return $ext instanceof PublicationPath;
            }));
            Assert::maxCount($pubPath, 1, ProtocolViolationException::class);
        }

        $this->setExtensions($extensions);
    }


    /**
     * Collect the value of the id property.
     *
     * @return \SimpleSAML\XMLSchema\Type\IDValue|null
     */
    public function getId(): ?IDValue
    {
        return $this->id;
    }


    /**
     * Collect the value of the validUntil property.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null
     */
    public function getValidUntil(): ?SAMLDateTimeValue
    {
        return $this->validUntil;
    }


    /**
     * Collect the value of the cacheDuration property.
     *
     * @return \SimpleSAML\XMLSchema\Type\DurationValue|null
     */
    public function getCacheDuration(): ?DurationValue
    {
        return $this->cacheDuration;
    }


    /**
     * @return \DOMElement
     */
    protected function getOriginalXML(): DOMElement
    {
        return $this->isSigned() ? $this->getXML() : $this->toUnsignedXML();
    }


    /**
     * @param \DOMElement|null $parent
     *
     * @return \DOMElement
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getId() !== null) {
            $e->setAttribute('ID', $this->getId()->getValue());
        }

        if ($this->getValidUntil() !== null) {
            $e->setAttribute('validUntil', $this->getValidUntil()->getValue());
        }

        if ($this->getCacheDuration() !== null) {
            $e->setAttribute('cacheDuration', $this->getCacheDuration()->getValue());
        }

        $extensions = $this->getExtensions();
        if ($extensions !== null && !$extensions->isEmptyElement()) {
            $extensions->toXML($e);
        }

        return $e;
    }
}
