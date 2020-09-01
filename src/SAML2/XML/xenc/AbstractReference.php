<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\xenc;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Abstract class representing references. No custom elements are allowed.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractReference extends AbstractXencElement
{
    /** @var string */
    protected $uri;

    /** @var \SimpleSAML\XML\Chunk[] */
    protected $references = [];


    /**
     * AbstractReference constructor.
     *
     * @param string $uri
     * @param \SimpleSAML\XML\Chunk[] $references
     */
    protected function __construct(string $uri, array $references = [])
    {
        $this->setURI($uri);
        $this->setReferences($references);
    }


    /**
     * Get the value of the URI attribute of this reference.
     *
     * @return string
     */
    public function getURI(): string
    {
        return $this->uri;
    }


    /**
     * @param string $uri
     */
    protected function setURI(string $uri): void
    {
        Assert::notEmpty($uri, 'The URI attribute of a reference cannot be empty.');
        $this->uri = $uri;
    }


    /**
     * Collect the references
     *
     * @return \SimpleSAML\XML\Chunk[]
     */
    public function getReferences(): array
    {
        return $this->references;
    }


    /**
     * Set the value of the references-property
     *
     * @param \SimpleSAML\XML\Chunk[] $references
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     *   if the supplied array contains anything other than Chunk objects
     */
    private function setReferences(array $references): void
    {
        Assert::allIsInstanceOf($references, Chunk::class);
        $this->references = $references;
    }


    /**
     * @inheritDoc
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, static::getClassName(static::class), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);

        $URI = self::getAttribute($xml, 'URI');

        $references = [];
        foreach ($xml->childNodes as $reference) {
            if (!($reference instanceof DOMElement)) {
                continue;
            }

            $references[] = new Chunk($reference);
        }

        return new static($URI, $references);
    }


    /**
     * @inheritDoc
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('URI', $this->uri);

        foreach ($this->references as $reference) {
            $e->appendChild($e->ownerDocument->importNode($reference->getXML(), true));
        }

        return $e;
    }
}
