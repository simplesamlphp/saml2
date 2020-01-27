<?php

namespace SAML2\XML;

use DOMElement;
use SAML2\Utils;
use SAML2\XML\alg\AbstractAlgElement as ALG;
use SAML2\XML\alg\DigestMethod;
use SAML2\XML\alg\SigningMethod;
use SAML2\XML\mdattr\EntityAttributes;
use SAML2\XML\mdrpi\AbstractMdrpiElement as MDRPI;
use SAML2\XML\mdrpi\PublicationInfo;
use SAML2\XML\mdrpi\RegistrationInfo;
use SAML2\XML\mdui\Common as MDUI;
use SAML2\XML\mdui\DiscoHints;
use SAML2\XML\mdui\UIInfo;
use SAML2\XML\shibmd\Scope;

/**
 * Trait for metadata elements that can be extended.
 *
 * @package simplesamlphp/saml2
 */
trait ExtendableElement
{
    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    protected $Extensions = [];


    /**
     * Process an XML element and get extensions from it.
     *
     * @param DOMElement $xml An element that may contain the md:Extensions element.
     *
     * @return (\SAML2\XML\shibmd\Scope|
     *          \SAML2\XML\mdattr\EntityAttributes|
     *          \SAML2\XML\mdrpi\RegistrationInfo|
     *          \SAML2\XML\mdrpi\PublicationInfo|
     *          \SAML2\XML\mdui\UIInfo|
     *          \SAML2\XML\mdui\DiscoHints|
     *          \SAML2\XML\alg\DigestMethod|
     *          \SAML2\XML\alg\SigningMethod|
     *          \SAML2\XML\Chunk)[]  Array of extensions.
     */
    public static function getExtensionsFromXML(DOMElement $xml): array
    {
        $ret = [];
        $supported = [
            Scope::NS            => [
                'Scope' => Scope::class,
            ],
            EntityAttributes::NS => [
                'EntityAttributes' => EntityAttributes::class,
            ],
            MDRPI::NS            => [
                'RegistrationInfo' => RegistrationInfo::class,
                'PublicationInfo'  => PublicationInfo::class,
            ],
            MDUI::NS             => [
                'UIInfo'     => UIInfo::class,
                'DiscoHints' => DiscoHints::class,
            ],
            ALG::NS              => [
                'DigestMethod'  => DigestMethod::class,
                'SigningMethod' => SigningMethod::class,
            ],
        ];

        /** @var \DOMElement $node */
        foreach (Utils::xpQuery($xml, './saml_metadata:Extensions/*') as $node) {
            if (
                !is_null($node->namespaceURI)
                && array_key_exists($node->namespaceURI, $supported)
                && array_key_exists($node->localName, $supported[$node->namespaceURI])
            ) {
                if (
                    $node->namespaceURI === ALG::NS
                    || $node->namespaceURI === EntityAttributes::NS
                    || $node->namespaceURI === MDRPI::NS
                    || $node->namespaceURI === Scope::NS
                ) {
                    /** @psalm-suppress UndefinedMethod */
                    $ret[] = $supported[$node->namespaceURI][$node->localName]::fromXML($node);
                } else {
                    /** @psalm-suppress InvalidArgument */
                    $ret[] = new $supported[$node->namespaceURI][$node->localName]($node);
                }
            } else {
                $ret[] = new Chunk($node);
            }
        }

        return $ret;
    }


    /**
     * Collect the value of the Extensions property.
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions property.
     *
     * @param array|null $extensions
     *
     * @return void
     */
    protected function setExtensions(?array $extensions): void
    {
        if ($extensions === null) {
            return;
        }
        $this->Extensions = $extensions;
    }


    /**
     * Add any existing extensions to a given element.
     *
     * @param DOMElement $parent The parent element where the md:Extensions must be placed.
     */
    protected function addExtensionsToXML(DOMElement $parent): void
    {
        if (empty($this->Extensions)) {
            return;
        }

        $e = $parent->ownerDocument->createElementNS($this::NS, $this::NS_PREFIX . ':Extensions');
        $parent->appendChild($e);

        foreach ($this->Extensions as $ext) {
            $ext->toXML($e);
        }
    }
}
