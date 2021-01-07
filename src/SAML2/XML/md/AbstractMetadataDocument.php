<?php

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\XML\SignedElementTrait;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;

/**
 * Class to represent a metadata document
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMetadataDocument extends AbstractSignedMdElement
{
    use ExtendableElementTrait;
    use SignedElementTrait;

    /**
     * The ID of this element.
     *
     * @var string|null
     */
    protected ?string $ID;

    /**
     * How long this element is valid, as a unix timestamp.
     *
     * @var int|null
     */
    protected ?int $validUntil;

    /**
     * The length of time this element can be cached, as string.
     *
     * @var string|null
     */
    protected ?string $cacheDuration;


    /**
     * Generic constructor for SAML metadata documents.
     *
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param int|null    $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions. Defaults to null.
     */
    public function __construct(
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null
    ) {
        $this->setID($ID);
        $this->setValidUntil($validUntil);
        $this->setCacheDuration($cacheDuration);
        $this->setExtensions($extensions);
    }


    /**
     * Collect the value of the ID property.
     *
     * @return string|null
     */
    public function getID()
    {
        return $this->ID;
    }


    /**
     * Set the value of the ID property.
     *
     * @param string|null $id
     */
    protected function setID(?string $id): void
    {
        $this->ID = $id;
    }


    /**
     * Collect the value of the validUntil property.
     *
     * @return int|null
     */
    public function getValidUntil(): ?int
    {
        return $this->validUntil;
    }


    /**
     * Set the value of the validUntil-property
     *
     * @param int|null $validUntil
     */
    protected function setValidUntil(?int $validUntil): void
    {
        $this->validUntil = $validUntil;
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
     * Set the value of the cacheDuration-property
     *
     * @param string|null $cacheDuration
     */
    protected function setCacheDuration(?string $cacheDuration): void
    {
        $this->cacheDuration = $cacheDuration;
    }


    /**
     * @param \DOMElement|null $parent
     *
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->ID !== null) {
            $e->setAttribute('ID', $this->ID);
        }

        if ($this->validUntil !== null) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->validUntil));
        }

        if ($this->cacheDuration !== null) {
            $e->setAttribute('cacheDuration', $this->cacheDuration);
        }

        if ($this->Extensions !== null && !$this->Extensions->isEmptyElement()) {
            $this->Extensions->toXML($e);
        }

        return $e;
    }
}
