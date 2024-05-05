<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\ElementInterface;

use function in_array;

/**
 * Trait grouping common functionality for elements implementing ExtensionType.
 *
 * @package simplesamlphp/saml2
 */
trait ExtensionsTrait
{
    /** @var \SimpleSAML\XML\SerializableElementInterface[] */
    protected array $extensions = [];


    /**
     * Extensions constructor.
     *
     * @param \SimpleSAML\XML\SerializableElementInterface[] $extensions
     */
    public function __construct(array $extensions)
    {
        Assert::maxCount($extensions, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($extensions, ElementInterface::class);

        foreach ($extensions as $extension) {
            /** @var \SimpleSAML\XML\AbstractElement $extension */
            $namespace = $extension->getNamespaceURI();

            Assert::notNull(
                $namespace,
                'Extensions MUST NOT include global (non-namespace-qualified) elements.',
                ProtocolViolationException::class,
            );
            Assert::true(
                !in_array($namespace, [C::NS_SAML, C::NS_SAMLP], true),
                'Extensions MUST NOT include any SAML-defined namespace elements.',
                ProtocolViolationException::class,
            );
        }

        /**
         * Set an array with all extensions present.
         */
        $this->extensions = $extensions;
    }


    /**
     * Get an array with all extensions present.
     *
     * @return \SimpleSAML\XML\SerializableElementInterface[]
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

        foreach ($this->extensions as $extension) {
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
