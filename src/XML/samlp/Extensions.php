<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\ExtensionsTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * Class for handling SAML2 extensions.
 *
 * @package simplesamlphp/saml2
 */
final class Extensions extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use ExtensionsTrait;
    use SchemaValidatableElementTrait;

    /**
     * Create an Extensions object from its samlp:Extensions XML representation.
     *
     * For those supported extensions, an object of the corresponding class will be created. The rest will be added
     * as a \SimpleSAML\XML\Chunk object.
     *
     * @param \DOMElement $xml
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::eq(
            $xml->namespaceURI,
            self::NS,
            'Unknown namespace \'' . strval($xml->namespaceURI) . '\' for Extensions element.',
            InvalidDOMElementException::class,
        );
        Assert::eq(
            $xml->localName,
            static::getClassName(static::class),
            'Invalid Extensions element \'' . $xml->localName . '\'',
            InvalidDOMElementException::class,
        );
        $ret = [];

        /** @var \DOMElement $node */
        foreach (XPath::xpQuery($xml, './*', XPath::getXPath($xml)) as $node) {
            $ret[] = new Chunk($node);
        }

        return new static($ret);
    }
}
