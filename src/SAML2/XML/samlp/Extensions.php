<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\ExtensionsTrait;
use SAML2\Utils;
use SAML2\XML\Chunk;
use Webmozart\Assert\Assert;

/**
 * Class for handling SAML2 extensions.
 *
 * @package simplesamlphp/saml2
 */
final class Extensions extends AbstractSamlpElement
{
    use ExtensionsTrait;

    /**
     * Create an Extensions object from its md:Extensions XML representation.
     *
     * For those supported extensions, an object of the corresponding class will be created. The rest will be added
     * as a \SAML2\XML\Chunk object.
     *
     * @param \DOMElement $xml
     * @return \SAML2\XML\md\Extensions
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::eq(
            $xml->namespaceURI,
            self::NS,
            'Unknown namespace \'' . strval($xml->namespaceURI) . '\' for Extensions element.'
        );
        Assert::eq(
            $xml->localName,
            static::getClassName(static::class),
            'Invalid Extensions element \'' . $xml->localName . '\''
        );
        $ret = [];

        /** @var \DOMElement $node */
        foreach (Utils::xpQuery($xml, './*') as $node) {
            $ret[] = new Chunk($node);
        }

        return new self($ret);
    }
}
