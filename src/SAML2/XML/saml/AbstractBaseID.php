<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\ExtensionPointInterface;
use SimpleSAML\SAML2\XML\ExtensionPointTrait;
use SimpleSAML\SAML2\XML\IDNameQualifiersTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\XML\EncryptableElementInterface;
use SimpleSAML\XMLSecurity\XML\EncryptableElementTrait;

use function count;
use function explode;

/**
 * SAML BaseID data type.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractBaseID extends AbstractSamlElement implements
     BaseIdentifierInterface,
     EncryptableElementInterface,
     ExtensionPointInterface
{
    use EncryptableElementTrait;
    use ExtensionPointTrait;
    use IDNameQualifiersTrait;

    /** @var string */
    public const LOCALNAME = 'BaseID';

    /** @var string */
    protected string $type;


    /**
     * Initialize a saml:BaseID from scratch
     *
     * @param string $type
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    protected function __construct(
        string $type,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null
    ) {
        $this->type = $type;
        $this->setNameQualifier($NameQualifier);
        $this->setSPNameQualifier($SPNameQualifier);
    }


    /**
     * @inheritDoc
     */
    public function getXsiType(): string
    {
        return $this->type;
    }


    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }


    public function getEncryptionBackend(): ?EncryptionBackend
    {
        // return the encryption backend you want to use,
        // or null if you are fine with the default
        return null;
    }


    /**
     * Convert XML into an BaseID
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\BaseIdentifierInterface
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'BaseID', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, C::NS_SAML, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:BaseID> element.',
            SchemaViolationException::class
        );

        $type = $xml->getAttributeNS(C::NS_XSI, 'type');
        Assert::validQName($type, SchemaViolationException::class);

        // first, try to resolve the type to a full namespaced version
        $qname = explode(':', $type, 2);
        if (count($qname) === 2) {
            list($prefix, $element) = $qname;
        } else {
            $prefix = null;
            list($element) = $qname;
        }
        $ns = $xml->lookupNamespaceUri($prefix);
        $type = ($ns === null ) ? $element : implode(':', [$ns, $element]);

        // now check if we have a handler registered for it
        $handler = Utils::getContainer()->getExtensionHandler($type);
        if ($handler === null) {
            // we don't have a handler, proceed with unknown identifier
            return new UnknownID(
                new Chunk($xml),
                $type,
                self::getAttribute($xml, 'NameQualifier', null),
                self::getAttribute($xml, 'SPNameQualifier', null)
            );
        }

        Assert::subclassOf(
            $handler,
            AbstractBaseID::class,
            'Elements implementing BaseID must extend \SimpleSAML\SAML2\XML\saml\AbstractBaseID.');
        return $handler::fromXML($xml);
    }


    /**
     * Convert this BaseID to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this BaseID.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('xmlns:' . static::getXsiTypePrefix(), static::getXsiTypeNamespaceURI());
        $e->setAttributeNS(C::NS_XSI, 'xsi:type', $this->getXsiType());

        if ($this->NameQualifier !== null) {
            $e->setAttribute('NameQualifier', $this->NameQualifier);
        }

        if ($this->SPNameQualifier !== null) {
            $e->setAttribute('SPNameQualifier', $this->SPNameQualifier);
        }

        return $e;
    }
}
