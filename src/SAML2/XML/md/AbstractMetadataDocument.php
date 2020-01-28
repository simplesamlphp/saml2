<?php

namespace SAML2\XML\md;

use DOMElement;
use SAML2\SignedElementHelper;
use SAML2\Utils;
use SAML2\XML\ExtendableElement;

/**
 * Class to represent a metadata document
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMetadataDocument extends AbstractSignedMdElement
{
    use ExtendableElement;
    use SignedElementHelper;

    /**
     * The ID of this element.
     *
     * @var string|null
     */
    protected $ID;

    /**
     * How long this element is valid, as a unix timestamp.
     *
     * @var int|null
     */
    protected $validUntil;

    /**
     * The length of time this element can be cached, as string.
     *
     * @var string|null
     */
    protected $cacheDuration;


    /**
     * Generic constructor for SAML metadata documents.
     *
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param int|null    $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SAML2\XML\md\Extensions[]|null $extensions An array of extensions. Defaults to null.
     */
    public function __construct(
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?array $extensions = null
    ) {
        $this->ID = $ID;
        $this->setValidUntil($validUntil);
        $this->cacheDuration = $cacheDuration;
        $this->setExtensions($extensions);
    }


    /**
     * Process an XML element and get its ID property, if any.
     *
     * @param \DOMElement $xml An element that may contain an ID.
     *
     * @return string|null
     */
    public static function getIDFromXML(DOMElement $xml): ?string
    {
        if ($xml->hasAttribute('ID')) {
            return $xml->getAttribute('ID');
        }
        return null;
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
     * Process an XML element and get its validUntil property, if any.
     *
     * @param \DOMElement $xml An element that may contain validUntil.
     *
     * @return int|null
     *
     * @throws \Exception If the processed validUntil from $xml is not a valid timestamp.
     */
    public static function getValidUntilFromXML(DOMElement $xml): ?int
    {
        if ($xml->hasAttribute('validUntil')) {
            return Utils::xsDateTimeToTimestamp($xml->getAttribute('validUntil'));
        }
        return null;
    }


    /**
     * Collect the value of the validUntil property.
     *
     * @return int|null
     */
    public function getValidUntil()
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
     * Process an XML element and get its cacheDuration property, if any.
     *
     * @param \DOMElement $xml An element that may contain cacheDuration.
     *
     * @return string|null
     */
    public static function getCacheDurationFromXML(DOMElement $xml): ?string
    {
        if ($xml->hasAttribute('cacheDuration')) {
            return $xml->getAttribute('cacheDuration');
        }
        return null;
    }


    /**
     * Collect the value of the cacheDuration property.
     *
     * @return string|null
     */
    public function getCacheDuration()
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

        $this->addExtensionsToXML($e);
        return $e;
    }
}
