<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
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

/**
 * Class implementing the <saml:Statement> extension point.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractStatement extends AbstractStatementType implements
    ExtensionPointInterface,
    SchemaValidatableElementInterface
{
    use ExtensionPointTrait;
    use SchemaValidatableElementTrait;


    public const string LOCALNAME = 'Statement';


    /**
     * Initialize a custom saml:Statement element.
     *
     * @param \SimpleSAML\XMLSchema\Type\QNameValue $type
     */
    protected function __construct(
        protected QNameValue $type,
    ) {
    }


    /**
     * @return \SimpleSAML\XMLSchema\Type\QNameValue
     */
    public function getXsiType(): QNameValue
    {
        return $this->type;
    }


    /**
     * Convert an XML element into a Statement.
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Statement', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, C::NS_SAML, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C_XSI::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Statement> element.',
            SchemaViolationException::class,
        );

        $type = QNameValue::fromDocument($xml->getAttributeNS(C_XSI::NS_XSI, 'type'), $xml);

        // now check if we have a handler registered for it
        $handler = Utils::getContainer()->getExtensionHandler($type);
        if ($handler === null) {
            // we don't have a handler, proceed with unknown statement
            return new UnknownStatement(new Chunk($xml), $type);
        }

        Assert::subclassOf(
            $handler,
            AbstractStatement::class,
            'Elements implementing Statement must extend \SimpleSAML\SAML2\XML\saml\AbstractStatement.',
        );
        return $handler::fromXML($xml);
    }


    /**
     * Convert this Statement to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:' . static::getXsiTypePrefix(),
            static::getXsiTypeNamespaceURI()->getValue(),
        );

        $type = new XMLAttribute(C_XSI::NS_XSI, 'xsi', 'type', $this->getXsiType());
        $type->toXML($e);

        return $e;
    }
}
