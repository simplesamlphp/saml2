<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\alg\AbstractAlgElement as ALG;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\XML\alg\SigningMethod;
use SimpleSAML\SAML2\XML\emd\RepublishRequest;
use SimpleSAML\SAML2\XML\ExtensionsTrait;
use SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse;
use SimpleSAML\SAML2\XML\init\RequestInitiator;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement as MDRPI;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdui\AbstractMduiElement as MDUI;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\XML\Constants\NS;

use function array_key_exists;

/**
 * Class for handling SAML2 metadata extensions.
 *
 * @package simplesamlphp/saml2
 */
final class Extensions extends AbstractMdElement implements SchemaValidatableElementInterface
{
    use ExtensionsTrait;
    use SchemaValidatableElementTrait;


    /** The namespace-attribute for the xs:any element */
    public const string XS_ANY_ELT_NAMESPACE = NS::OTHER;

    /**
     * The exclusions for the xs:any element
     *
     * @var array<int, array<int, string>>
     */
    public const array XS_ANY_ELT_EXCLUSIONS = [
        ['urn:oasis:names:tc:SAML:2.0:assertion', '*'],
        ['urn:oasis:names:tc:SAML:2.0:metadata', '*'],
        ['urn:oasis:names:tc:SAML:2.0:protocol', '*'],
    ];


    /**
     * Create an Extensions object from its md:Extensions XML representation.
     *
     * For those supported extensions, an object of the corresponding class will be created.
     * The rest will be added as a \SimpleSAML\XML\Chunk object.
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
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
        $supported = [
            RepublishRequest::NS => [
                'RepublishRequest' => RepublishRequest::class,
            ],
            DiscoveryResponse::NS => [
                'DiscoveryResponse' => DiscoveryResponse::class,
            ],
            Scope::NS => [
                'Scope' => Scope::class,
            ],
            EntityAttributes::NS => [
                'EntityAttributes' => EntityAttributes::class,
            ],
            MDRPI::NS => [
                'RegistrationInfo' => RegistrationInfo::class,
                'PublicationInfo' => PublicationInfo::class,
                'PublicationPath' => PublicationPath::class,
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
        foreach (XPath::xpQuery($xml, './*', XPath::getXPath($xml)) as $node) {
            if (
                array_key_exists($node->namespaceURI, $supported)
                && array_key_exists($node->localName, $supported[$node->namespaceURI])
            ) {
                $ret[] = $supported[$node->namespaceURI][$node->localName]::fromXML($node);
            } else {
                $ret[] = new Chunk($node);
            }
        }

        return new static($ret);
    }
}
