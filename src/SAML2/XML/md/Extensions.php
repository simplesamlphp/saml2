<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\XML\alg\SigningMethod;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\XML\Chunk;

use function array_key_exists;
use function is_null;

/**
 * Class for handling SAML2 metadata extensions.
 * @package SimpleSAMLphp
 */
class Extensions
{
    /**
     * Get a list of Extensions in the given element.
     *
     * @param \DOMElement $parent The element that may contain the md:Extensions element.
     * @return (\SimpleSAML\SAML2\XML\shibmd\Scope|
     *          \SimpleSAML\SAML2\XML\mdattr\EntityAttributes|
     *          \SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo|
     *          \SimpleSAML\SAML2\XML\mdrpi\PublicationInfo|
     *          \SimpleSAML\SAML2\XML\mdui\UIInfo|
     *          \SimpleSAML\SAML2\XML\mdui\DiscoHints|
     *          \SimpleSAML\SAML2\XML\alg\DigestMethod|
     *          \SimpleSAML\SAML2\XML\alg\SigningMethod|
     *          \SimpleSAML\XML\Chunk)[]  Array of extensions.
     */
    public static function getList(DOMElement $parent): array
    {
        $ret = [];
        $supported = [
            C::NS_SHIBMD => [
                'Scope' => Scope::class,
            ],
            C::NS_MDATTR => [
                'EntityAttributes' => EntityAttributes::class,
            ],
            C::NS_MDRPI => [
                'RegistrationInfo' => RegistrationInfo::class,
                'PublicationInfo' => PublicationInfo::class,
            ],
            C::NS_MDUI => [
                'UIInfo' => UIInfo::class,
                'DiscoHints' => DiscoHints::class,
            ],
            C::NS_ALG => [
                'DigestMethod' => DigestMethod::class,
                'SigningMethod' => SigningMethod::class,
            ],
        ];

        $nodes = XPath::xpQuery($parent, './saml_metadata:Extensions/*', XPath::getXPath($parent));
        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            if (
                !is_null($node->namespaceURI) &&
                array_key_exists($node->namespaceURI, $supported) &&
                array_key_exists($node->localName, $supported[$node->namespaceURI])
            ) {
                $ret[] = new $supported[$node->namespaceURI][$node->localName]($node);
            } else {
                $ret[] = new Chunk($node);
            }
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

        $extElement = $parent->ownerDocument->createElementNS(C::NS_MD, 'md:Extensions');
        $parent->appendChild($extElement);

        foreach ($extensions as $ext) {
            $ext->toXML($extElement);
        }
    }
}
