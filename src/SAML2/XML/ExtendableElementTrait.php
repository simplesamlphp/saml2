<?php

declare(strict_types=1);

namespace SAML2\XML;

use SAML2\XML\md\Extensions;

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
     * @var \SAML2\XML\md\Extensions
     */
    protected $Extensions;


    /**
     * Collect the value of the Extensions property.
     *
     * @return \SAML2\XML\md\Extensions
     */
    public function getExtensions(): Extensions
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions property.
     *
     * @param \SAML2\XML\md\Extensions|null $extensions
     */
    protected function setExtensions(?Extensions $extensions): void
    {
        if ($extensions === null) {
            return;
        }

        $this->Extensions = $extensions;
    }
}
