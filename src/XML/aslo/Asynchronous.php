<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\aslo;

use Dom;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;

/**
 * Class representing a aslo:Asynchronous element.
 *
 * @package simplesaml/saml2
 */
final class Asynchronous extends AbstractAsloElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Convert XML into a Asynchronous
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(Dom\Element $xml): static
    {
        Assert::same($xml->localName, 'Asynchronous', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Asynchronous::NS, InvalidDOMElementException::class);

        return new static();
    }


    /**
     * Convert this Asynchronous to XML.
     */
    public function toXML(?Dom\Element $parent = null): Dom\Element
    {
        return $this->instantiateParentElement($parent);
    }
}
