<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils\XPath;
use SAML2\XML\alg\DigestMethod;
use SAML2\XML\alg\SigningMethod;
use SAML2\XML\mdattr\EntityAttributes;
use SAML2\XML\mdrpi\PublicationInfo;
use SAML2\XML\mdrpi\RegistrationInfo;
use SAML2\XML\mdui\DiscoHints;
use SAML2\XML\mdui\UIInfo;
use SAML2\XML\shibmd\Scope;
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
     * @return (\SAML2\XML\shibmd\Scope|
     *          \SAML2\XML\mdattr\EntityAttributes|
     *          \SAML2\XML\mdrpi\RegistrationInfo|
     *          \SAML2\XML\mdrpi\PublicationInfo|
     *          \SAML2\XML\mdui\UIInfo|
     *          \SAML2\XML\mdui\DiscoHints|
     *          \SAML2\XML\alg\DigestMethod|
     *          \SAML2\XML\alg\SigningMethod|
     *          \SimpleSAML\XML\Chunk)[]  Array of extensions.
     */
    public static function getList(DOMElement $parent): array
    {
        $ret = [];
        $supported = [
            Constants::NS_SHIBMD => [
                'Scope' => Scope::class,
            ],
            EntityAttributes::NS => [
                'EntityAttributes' => EntityAttributes::class,
            ],
            Constants::NS_MDRPI => [
                'RegistrationInfo' => RegistrationInfo::class,
                'PublicationInfo' => PublicationInfo::class,
            ],
            Constants::NS_MDUI => [
                'UIInfo' => UIInfo::class,
                'DiscoHints' => DiscoHints::class,
            ],
            Constants::NS_ALG => [
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

        $extElement = $parent->ownerDocument->createElementNS(Constants::NS_MD, 'md:Extensions');
        $parent->appendChild($extElement);

        foreach ($extensions as $ext) {
            $ext->toXML($extElement);
        }
    }
}
