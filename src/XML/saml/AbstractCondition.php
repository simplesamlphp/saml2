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
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

use function count;
use function explode;

/**
 * SAML Condition data type.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractCondition extends AbstractConditionType implements
    ExtensionPointInterface,
    SchemaValidatableElementInterface
{
    use ExtensionPointTrait;
    use SchemaValidatableElementTrait;

    /** @var string */
    public const LOCALNAME = 'Condition';


    /**
     * Initialize a custom saml:Condition element.
     *
     * @param string $type
     */
    protected function __construct(
        protected string $type,
    ) {
    }


    /**
     * @inheritDoc
     */
    public function getXsiType(): string
    {
        return $this->type;
    }


    /**
     * Convert an XML element into a Condition.
     *
     * @param \DOMElement $xml The root XML element
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Condition', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, C::NS_SAML, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Condition> element.',
            SchemaViolationException::class,
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
        $type = ($ns === null) ? $element : implode(':', [$ns, $element]);

        // now check if we have a handler registered for it
        $handler = Utils::getContainer()->getExtensionHandler($type);
        if ($handler === null) {
            // we don't have a handler, proceed with unknown condition
            return new UnknownCondition(new Chunk($xml), $type);
        }

        Assert::subclassOf(
            $handler,
            AbstractCondition::class,
            'Elements implementing Condition must extend \SimpleSAML\SAML2\XML\saml\AbstractCondition.',
        );
        return $handler::fromXML($xml);
    }


    /**
     * Convert this Condition to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:' . static::getXsiTypePrefix(),
            static::getXsiTypeNamespaceURI(),
        );

        $type = new XMLAttribute(C::NS_XSI, 'xsi', 'type', $this->getXsiType());
        $type->toXML($e);

        return $e;
    }
}
