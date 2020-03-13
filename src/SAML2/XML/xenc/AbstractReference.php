<?php

declare(strict_types=1);

namespace SAML2\XML\xenc;

use DOMElement;
use Webmozart\Assert\Assert;

/**
 * Abstract class representing references. No custom elements are allowed.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractReference extends AbstractXencElement
{
    /** @var string */
    protected $uri;


    /**
     * AbstractReference constructor.
     *
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->setURI($uri);
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
     * @inheritDoc
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, static::getClassName(static::class));
        Assert::same($xml->localName, static::NS);

        return new static(self::getAttribute($xml, 'URI'));
    }


    /**
     * @inheritDoc
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('URI', $this->uri);
        return $e;
    }
}
