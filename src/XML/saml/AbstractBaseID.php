<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\EncryptableElementTrait;
use SimpleSAML\SAML2\XML\ExtensionPointInterface;
use SimpleSAML\SAML2\XML\ExtensionPointTrait;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Constants as C_XSI;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\QNameValue;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\XML\EncryptableElementInterface;

/**
 * SAML BaseID data type.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractBaseID extends AbstractBaseIDType implements
    EncryptableElementInterface,
    ExtensionPointInterface,
    SchemaValidatableElementInterface
{
    use EncryptableElementTrait;
    use ExtensionPointTrait;
    use SchemaValidatableElementTrait;


    public const string LOCALNAME = 'BaseID';


    /**
     * Initialize a saml:BaseID from scratch
     *
     * @param \SimpleSAML\XMLSchema\Type\QNameValue $type
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $NameQualifier
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $SPNameQualifier
     */
    protected function __construct(
        protected QNameValue $type,
        ?SAMLStringValue $NameQualifier = null,
        ?SAMLStringValue $SPNameQualifier = null,
    ) {
        parent::__construct($NameQualifier, $SPNameQualifier);
    }


    /**
     * @return \SimpleSAML\XMLSchema\Type\QNameValue
     */
    public function getXsiType(): QNameValue
    {
        return $this->type;
    }


    /**
     * Convert XML into an BaseID
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'BaseID', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, C::NS_SAML, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C_XSI::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:BaseID> element.',
            SchemaViolationException::class,
        );

        $type = QNameValue::fromDocument($xml->getAttributeNS(C_XSI::NS_XSI, 'type'), $xml);

        // now check if we have a handler registered for it
        $handler = Utils::getContainer()->getExtensionHandler($type);
        if ($handler === null) {
            // we don't have a handler, proceed with unknown identifier
            return new UnknownID(
                new Chunk($xml),
                $type,
                self::getOptionalAttribute($xml, 'NameQualifier', SAMLStringValue::class, null),
                self::getOptionalAttribute($xml, 'SPNameQualifier', SAMLStringValue::class, null),
            );
        }

        Assert::subclassOf(
            $handler,
            AbstractBaseID::class,
            'Elements implementing BaseID must extend \SimpleSAML\SAML2\XML\saml\AbstractBaseID.',
        );
        return $handler::fromXML($xml);
    }


    /**
     * Convert this BaseID to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);
        $e->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:' . static::getXsiTypePrefix(),
            static::getXsiTypeNamespaceURI()->getValue(),
        );

        $type = new XMLAttribute(C_XSI::NS_XSI, 'xsi', 'type', $this->getXsiType());
        $type->toXML($e);

        if ($this->getNameQualifier() !== null) {
            $e->setAttribute('NameQualifier', $this->getNameQualifier()->getValue());
        }

        if ($this->getSPNameQualifier() !== null) {
            $e->setAttribute('SPNameQualifier', $this->getSPNameQualifier()->getValue());
        }

        return $e;
    }


    /**
     * @return \SimpleSAML\XMLSecurity\Backend\EncryptionBackend|null
     */
    public function getEncryptionBackend(): ?EncryptionBackend
    {
        // return the encryption backend you want to use,
        // or null if you are fine with the default
        return null;
    }
}
