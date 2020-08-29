<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\AbstractXMLElement;

/**
 * Trait grouping common functionality for elements implementing ExtensionType.
 *
 * @package simplesamlphp/saml2
 */
trait ExtensionsTrait
{
    /**
     * @var \SimpleSAML\SAML2\XML\AbstractXMLElement[]
     */
    protected $extensions = [];


    /**
     * Extensions constructor.
     *
     * @var \SimpleSAML\SAML2\XML\AbstractXMLElement[]
     */
    public function __construct(array $extensions)
    {
        Assert::allIsInstanceOf($extensions, AbstractXMLElement::class);
        $this->extensions = $extensions;
    }


    /**
     * Get an array with all extensions present.
     *
     * @return \SimpleSAML\SAML2\XML\AbstractXMLElement[]
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


    /**
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    abstract public function instantiateParentElement(DOMElement $parent = null): DOMElement;
}
