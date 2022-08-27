<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\ExtensionPointInterface;
use SimpleSAML\SAML2\XML\ExtensionPointTrait;
use SimpleSAML\SAML2\XML\saml\Statement;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function count;
use function explode;

/**
 * Class implementing the <saml:Statement> extension point.
 *
 * @package simplesamlphp/saml2
 */
abstract class Statement extends AbstractStatementType implements ExtensionPointInterface
{
    use ExtensionPointTrait;

    /** @var string */
    public const LOCALNAME = 'Statement';


    /**
     * Convert an XML element into a Statement.
     *
     * @param \DOMElement $xml The root XML element
     * @return \SimpleSAML\SAML2\XML\saml\Statement The condition
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Statement', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, C::NS_SAML, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Statement> element.',
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

        Assert::notNull($handler, 'Unknown Statement type `' . $type . '`.');
        Assert::isAOf($handler, Statement::class);

        return $handler::fromXML($xml);
    }


    /**
     * Convert this Statement to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Statement.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('xmlns:' . static::NS_XSI_TYPE_PREFIX, static::NS_XSI_TYPE_NAMESPACE);
        $e->setAttributeNS(C::NS_XSI, 'xsi:type', static::getXsiType());

        return $e;
    }
}
