<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\XML\AbstractXMLElement;

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
     * @var \SimpleSAML\XML\AbstractXMLElement|null
     */
    protected ?AbstractXMLElement $Extensions = null;


    /**
     * Collect the value of the Extensions property.
     *
     * @return \SimpleSAML\XML\AbstractXMLElement|null
     */
    public function getExtensions(): ?AbstractXMLElement
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions property.
     *
     * @param \SimpleSAML\XML\AbstractXMLElement|null $extensions
     */
    protected function setExtensions(?AbstractXMLElement $extensions): void
    {
        $this->Extensions = $extensions;
    }
}
