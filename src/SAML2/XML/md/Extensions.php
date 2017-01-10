<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\alg\Common as ALG;
use SAML2\XML\alg\DigestMethod;
use SAML2\XML\alg\SigningMethod;
use SAML2\XML\Chunk;
use SAML2\XML\mdattr\EntityAttributes;
use SAML2\XML\mdrpi\Common as MDRPI;
use SAML2\XML\mdrpi\PublicationInfo;
use SAML2\XML\mdrpi\RegistrationInfo;
use SAML2\XML\mdui\DiscoHints;
use SAML2\XML\mdui\UIInfo;
use SAML2\XML\shibmd\Scope;

/**
 * Class for handling SAML2 metadata extensions.
 *
 * @package SimpleSAMLphp
 */
class Extensions
{
    /**
     * Get a list of Extensions in the given element.
     *
     * @param  \DOMElement $parent The element that may contain the md:Extensions element.
     * @return \SAML2\XML\Chunk[]  Array of extensions.
     */
    public static function getList(\DOMElement $parent)
    {
        $ret = array();
        $supported = array(
            Scope::NS => array(
                'Scope'
            ),
            EntityAttributes::NS => array(
                'EntityAttributes'
            ),
            MDRPI::NS_MDRPI => array(
                'RegistrationInfo',
                'PublicationInfo',
            ),
            UIInfo::NS => array(
                'UIInfo'
            ),
            DiscoHints::NS => array(
                'DiscoHints'
            ),
            ALG::NS => array(
                'DigestMethod',
                'SigningMethod',
            ),
        );

        foreach (Utils::xpQuery($parent, './saml_metadata:Extensions/*') as $node) {
            if (array_key_exists($node->namespaceURI, $supported) &&
                in_array($node->localName, $supported[$node->namespaceURI])
            ) {
                $ret[] = new $node->localName($node);
            } else {
                $ret[] = new Chunk($node);
            }
        }

        return $ret;
    }

    /**
     * Add a list of Extensions to the given element.
     *
     * @param \DOMElement        $parent     The element we should add the extensions to.
     * @param \SAML2\XML\Chunk[] $extensions List of extension objects.
     */
    public static function addList(\DOMElement $parent, array $extensions)
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
