<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Utils;
use SAML2\XML\alg\AbstractAlgElement as ALG;
use SAML2\XML\alg\DigestMethod;
use SAML2\XML\alg\SigningMethod;
use SAML2\XML\Chunk;
use SAML2\XML\ExtensionsTrait;
use SAML2\XML\init\RequestInitiator;
use SAML2\XML\mdattr\EntityAttributes;
use SAML2\XML\mdrpi\AbstractMdrpiElement as MDRPI;
use SAML2\XML\mdrpi\PublicationInfo;
use SAML2\XML\mdrpi\RegistrationInfo;
use SAML2\XML\mdui\AbstractMduiElement as MDUI;
use SAML2\XML\mdui\DiscoHints;
use SAML2\XML\mdui\UIInfo;
use SAML2\XML\shibmd\Scope;
use Webmozart\Assert\Assert;

/**
 * Class for handling SAML2 metadata extensions.
 *
 * @package simplesamlphp/saml2
 */
final class Extensions extends AbstractMdElement
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
            RequestInitiator::NS             => [
                'RequestInitiator' => RequestInitiator::class,
            ],
        ];

        /** @var \DOMElement $node */
        foreach (Utils::xpQuery($xml, './*') as $node) {
            if (
                !is_null($node->namespaceURI)
                && array_key_exists($node->namespaceURI, $supported)
                && array_key_exists($node->localName, $supported[$node->namespaceURI])
            ) {
                $ret[] = $supported[$node->namespaceURI][$node->localName]::fromXML($node);
            } else {
                $ret[] = new Chunk($node);
            }
        }

        return new self($ret);
    }
}
