<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\alg\AbstractAlgElement as ALG;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\XML\alg\SigningMethod;
use SimpleSAML\SAML2\XML\ExtensionsTrait;
use SimpleSAML\SAML2\XML\init\RequestInitiator;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement as MDRPI;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement as MDUI;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Utils as XMLUtils;

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
     * as a \SimpleSAML\XML\Chunk object.
     *
     * @param \DOMElement $xml
     * @return \SimpleSAML\SAML2\XML\md\Extensions
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::eq(
            $xml->namespaceURI,
            self::NS,
            'Unknown namespace \'' . strval($xml->namespaceURI) . '\' for Extensions element.',
            InvalidDOMElementException::class
        );
        Assert::eq(
            $xml->localName,
            static::getClassName(static::class),
            'Invalid Extensions element \'' . $xml->localName . '\'',
            InvalidDOMElementException::class
        );
        $ret = [];
        $supported = [
            Scope::NS => [
                'Scope' => Scope::class,
            ],
            EntityAttributes::NS => [
                'EntityAttributes' => EntityAttributes::class,
            ],
            MDRPI::NS => [
                'RegistrationInfo' => RegistrationInfo::class,
                'PublicationInfo' => PublicationInfo::class,
            ],
            MDUI::NS => [
                'UIInfo' => UIInfo::class,
                'DiscoHints' => DiscoHints::class,
            ],
            ALG::NS => [
                'DigestMethod' => DigestMethod::class,
                'SigningMethod' => SigningMethod::class,
            ],
            RequestInitiator::NS => [
                'RequestInitiator' => RequestInitiator::class,
            ],
        ];

        /** @var \DOMElement $node */
        foreach (XMLUtils::xpQuery($xml, './*') as $node) {
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

        /**
         * The <mdrpi:PublicationInfo> element MUST NOT appear more than once within the <md:Extensions> element
         *  of a given <md:EntitiesDescriptor>or <md:EntityDescriptor> element.
         */
        $pi = array_values(array_filter($ret, function ($statement) {
            return $statement instanceof PublicationInfo;
        }));
        Assert::maxCount(
            $pi,
            1,
            'The <mdrpi:PublicationInfo> element MUST NOT appear more than once within the <md:Extensions> element'
            . ' of a given <md:EntitiesDescriptor> or <md:EntityDescriptor> element.',
            ProtocolViolationException::class
        );


        /**
         * The <mdrpi:RegistrationInfo> element MUST NOT appear more than once within the <md:Extensions> element
         *  of a given <md:EntitiesDescriptor>or <md:EntityDescriptor> element.
         */
        $ri = array_values(array_filter($ret, function ($statement) {
            return $statement instanceof RegistrationInfo;
        }));
        Assert::maxCount(
            $ri,
            1,
            'The <mdrpi:RegistrationInfo> element MUST NOT appear more than once within the <md:Extensions> element'
            . ' of a given <md:EntitiesDescriptor> or <md:EntityDescriptor> element.',
            ProtocolViolationException::class
        );

        return new self($ret);
    }
}
