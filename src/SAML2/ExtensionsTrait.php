<?php

declare(strict_types=1);

namespace SAML2;

use DOMElement;
use SAML2\XML\AbstractXMLElement;

/**
 * Trait grouping common functionality for elements implementing ExtensionType.
 *
 * @package simplesamlphp/saml2
 */
trait ExtensionsTrait
{
    /**
     * @var AbstractXMLElement[]
     */
    protected $extensions = [];


    /**
     * Extensions constructor.
     *
     * @var AbstractXMLElement[]
     */
    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }


    /**
     * Get an array with all extensions present.
     *
     * @return AbstractXMLElement[]
     */
    public function getList(): array
    {
        return $this->extensions;
    }


    /**
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        if (empty($this->extensions)) {
            return true;
        }

        $empty = false;
        foreach ($this->extensions as $extension) {
            $empty &= $extension->isEmptyElement();
        }

        return boolval($empty);
    }


    /**
     * Convert this object into its md:Extensions XML representation.
     *
     * @param \DOMElement|null $parent The element we should add this Extensions element to.
     * @return \DOMElement The new md:Extensions XML element.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        foreach ($this->extensions as $extension) {
            if (!$extension->isEmptyElement()) {
                $extension->toXML($e);
            }
        }
        return $e;
    }
}
