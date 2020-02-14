<?php

declare(strict_types=1);

namespace SAML2\XML;

/**
 * Trait for metadata elements that can be extended.
 *
 * @package simplesamlphp/saml2
 */
trait ExtendableElementTrait
{
    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var AbstractXMLElement|null
     */
    protected $Extensions = null;


    /**
     * Collect the value of the Extensions property.
     *
     * @return AbstractXMLElement|null
     */
    public function getExtensions(): ?AbstractXMLElement
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions property.
     *
     * @param AbstractXMLElement|null $extensions
     */
    protected function setExtensions(?AbstractXMLElement $extensions): void
    {
        $this->Extensions = $extensions;
    }
}
