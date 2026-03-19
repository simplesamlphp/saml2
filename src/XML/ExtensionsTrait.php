<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use DOMElement;
use SimpleSAML\XML\ExtendableElementTrait;

/**
 * Trait grouping common functionality for elements implementing ExtensionType.
 *
 * @package simplesamlphp/saml2
 */
trait ExtensionsTrait
{
    use ExtendableElementTrait;


    /**
     * Extensions constructor.
     *
     * @param \SimpleSAML\XML\SerializableElementInterface[] $extensions
     */
    public function __construct(array $extensions)
    {
        $this->setElements($extensions);
    }


    /**
     */
    public function isEmptyElement(): bool
    {
        if (empty($this->getElements())) {
            return true;
        }

        foreach ($this->getElements() as $extension) {
            if ($extension->isEmptyElement() === false) {
                return false;
            }
        }

        return true;
    }


    /**
     * Convert this object into its md:Extensions XML representation.
     *
     * @param \DOMElement|null $parent The element we should add this Extensions element to.
     * @return \DOMElement The new md:Extensions XML element.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if (!$this->isEmptyElement()) {
            foreach ($this->getElements() as $extension) {
                if (!$extension->isEmptyElement()) {
                    $extension->toXML($e);
                }
            }
        }

        return $e;
    }


    /**
     */
    abstract public function instantiateParentElement(?DOMElement $parent = null): DOMElement;
}
