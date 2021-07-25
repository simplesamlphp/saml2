<?php

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\XML\ExtendableAttributesTrait;

use function gmdate;

/**
 * Class to represent a metadata document
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMetadataDocument extends AbstractSignedMdElement
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;

    /**
     * The ID of this element.
     *
     * @var string|null
     */
    protected ?string $id;

    /** @var \DOMElement $xml */
    protected DOMElement $xml;

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
     * @param string|null $id The ID for this document. Defaults to null.
     * @param int|null    $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions. Defaults to null.
     * @param \DOMAttr[] $namespacedAttributes
     */
    public function __construct(
        ?string $id = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        $namespacedAttributes = []
    ) {
        $this->setId($id);
        $this->setValidUntil($validUntil);
        $this->setCacheDuration($cacheDuration);
        $this->setExtensions($extensions);
        $this->setAttributesNS($namespacedAttributes);
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
     * Set the value of the id property.
     *
     * @param string|null $id
     */
    protected function setId(?string $id): void
    {
        $this->id = $id;
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
     * @inheritDoc
     */
    protected function getOriginalXML(): DOMElement
    {
        return $this->xml ?? $this->toUnsignedXML();
    }


    /**
     * Create XML from this class
     *
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->toUnsignedXML($parent);

        if ($this->signer !== null) {
            $signedXML = $this->doSign($e);
            $signedXML->insertBefore(
                $this->signature->toXML($signedXML),
                $signedXML->firstChild
            );
            return $signedXML;
        }

        return $e;
    }


    /**
     * @param \DOMElement|null $parent
     *
     * @return \DOMElement
     */
    protected function toUnsignedXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

        if ($this->id !== null) {
            $e->setAttribute('ID', $this->id);
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
