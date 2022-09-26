<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\XML\AbstractElement;

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
     * @var \SimpleSAML\XML\AbstractElement|null
     */
    protected ?AbstractElement $Extensions = null;


    /**
     * Collect the value of the Extensions property.
     *
     * @return \SimpleSAML\XML\AbstractElement|null
     */
    public function getExtensions(): ?AbstractElement
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions property.
     *
     * @param \SimpleSAML\XML\AbstractElement|null $extensions
     */
    protected function setExtensions(?AbstractElement $extensions): void
    {
        $this->Extensions = $extensions;
    }
}
