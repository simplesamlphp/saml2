<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Chunk;

/**
 * Class for handling SAML2 extensions.
 *
 * @package SimpleSAMLphp
 */
class Extensions
{
    /**
     * Get a list of Extensions in the given element.
     *
     * @param  \DOMElement $parent The element that may contain the samlp:Extensions element.
     * @return array Array of extensions.
     */
    public static function getList(DOMElement $parent): array
    {
        $xpCache = XPath::getXPath($parent);
        $ret = [];
        /** @var \DOMElement $node */
        foreach (XPath::xpQuery($parent, './saml_protocol:Extensions/*', $xpCache) as $node) {
            $ret[] = new Chunk($node);
        }

        return $ret;
    }


    /**
     * Add a list of Extensions to the given element.
     *
     * @param \DOMElement $parent The element we should add the extensions to.
     * @param \SimpleSAML\XML\Chunk[] $extensions List of extension objects.
     * @return void
     */
    public static function addList(DOMElement $parent, array $extensions): void
    {
        if (empty($extensions)) {
            return;
        }

        $extElement = $parent->ownerDocument->createElementNS(C::NS_SAMLP, 'samlp:Extensions');
        $parent->appendChild($extElement);

        foreach ($extensions as $ext) {
            $ext->toXML($extElement);
        }
    }
}
