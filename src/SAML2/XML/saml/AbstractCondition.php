<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\ExtensionPointInterface;
use SimpleSAML\SAML2\XML\ExtensionPointTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function count;
use function explode;

/**
 * SAML Condition data type.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractCondition extends AbstractConditionType implements ExtensionPointInterface
{
    use ExtensionPointTrait;

    /** @var string */
    public const LOCALNAME = 'Condition';

    /** @var string */
    protected string $type;


    /**
     * Initialize a custom saml:Condition element.
     *
     * @param string $type
     */
    protected function __construct(string $type)
    {
        $this->type = $type;
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
     * @return \SimpleSAML\SAML2\XML\saml\AbstractCondition The condition
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Condition', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, C::NS_SAML, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Condition> element.',
            SchemaViolationException::class
        );

        $type = $xml->getAttributeNS(C::NS_XSI, 'type');
        Assert::validQName($type, SchemaViolationException::class);

        $qname = explode(':', $type, 2);
        if (count($qname) === 2) {
            list($prefix, $element) = $qname;
        } else {
            $prefix = null;
            list($element) = $qname;
        }
        $ns = $xml->lookupNamespaceUri($prefix);
        $handler = Utils::getContainer()->getElementHandler($ns, $element);
        if ($handler === null) {
            // we don't have a handler, proceed with unknown condition
            return new UnknownCondition(new Chunk($xml), $type);
        }

        Assert::subclassOf(
            $handler,
            AbstractCondition::class,
            'Elements implementing Condition must extend \SimpleSAML\SAML2\XML\saml\AbstractCondition.'
        );
        return $handler::fromXML($xml);
    }


    /**
     * Convert this Condition to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('xmlns:' . static::getXsiTypePrefix(), static::getXsiTypeNamespaceURI());
        $e->setAttributeNS(C::NS_XSI, 'xsi:type', $this->getXsiType());

        return $e;
    }
}
