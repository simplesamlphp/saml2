<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\XML\Exception\SchemaViolationException;

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
     * @param string|null $id The ID for this document. Defaults to null.
     * @param \DateTimeImmutable|null    $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions. Defaults to null.
     */
    public function __construct(
        protected ?string $id = null,
        protected ?DateTimeImmutable $validUntil = null,
        protected ?string $cacheDuration = null,
        ?Extensions $extensions = null,
    ) {
        Assert::nullOrValidNCName($id, SchemaViolationException::class);
        Assert::nullOrSame($validUntil?->getTimeZone()->getName(), 'Z');
        Assert::nullOrValidDuration($cacheDuration, SchemaViolationException::class);

        $this->setExtensions($extensions);
    }


    /**
     * Collect the value of the id property.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }


    /**
     * Collect the value of the validUntil property.
     *
     * @return \DateTimeImmutable|null
     */
    public function getValidUntil(): ?DateTimeImmutable
    {
        return $this->validUntil;
    }


    /**
     * Collect the value of the cacheDuration property.
     *
     * @return string|null
     */
    public function getCacheDuration(): ?string
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
            $e->setAttribute('ID', $this->getId());
        }

        if ($this->getValidUntil() !== null) {
            $e->setAttribute('validUntil', $this->getValidUntil()->format(C::DATETIME_FORMAT));
        }

        if ($this->getCacheDuration() !== null) {
            $e->setAttribute('cacheDuration', $this->getCacheDuration());
        }

        $extensions = $this->getExtensions();
        if ($extensions !== null && !$extensions->isEmptyElement()) {
            $extensions->toXML($e);
        }

        return $e;
    }
}
