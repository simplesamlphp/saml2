<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\Condition;
use SimpleSAML\Assert\Assert;

/**
 * @covers \SimpleSAML\Test\SAML2\CustomCondition
 * @package simplesamlphp\saml2
 */
final class CustomCondition extends Condition
{
    /** @var string */
    protected const XSI_TYPE = 'ssp:CustomCondition';

    /** @var string */
    protected const XSI_TYPE_NS = 'urn:custom:ssp';

    /** @var string */
    protected const XSI_TYPE_PREFIX = 'ssp';

    /** @var \SimpleSAML\SAML2\XML\saml\Audience[] */
    protected $audience = [];


    /**
     * CustomCondition constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $value
     */
    public function __construct(array $audience)
    {
        parent::__construct('dummy', self::XSI_TYPE);

        $this->setAudience($audience);
    }


    /**
     * Get the value of the audience-attribute.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Audience[]
     */
    public function getAudience(): array
    {
        return $this->audience;
    }


    /**
     * Set the value of the audience-attribute
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     */
    protected function setAudience(array $audience): void
    {
        Assert::allIsInstanceOf($audience, Audience::class);

        $this->audience = $audience;
    }


    /**
     * @inheritDoc
     */
    public static function getXsiType(): string
    {
        return self::XSI_TYPE;
    }


    /**
     * Convert XML into an Condition
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\Condition
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Condition', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Condition::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Condition> element.',
            InvalidDOMElementException::class
        );

        $type = $xml->getAttributeNS(C::NS_XSI, 'type');
        list($prefix, $element) = explode(':', $type, 2);

        $ns = $xml->lookupNamespaceUri($prefix);
        $handler = Utils::getContainer()->getElementHandler($ns, $element);

        Assert::notNull($handler, 'Unknown Condition type `' . $type . '`.');
        Assert::isAOf($handler, Condition::class);

        $audience = Audience::getChildrenOfClass($xml);

        return new $handler($audience);
    }


    /**
     * Convert this Condition to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->audience as $audience) {
            $audience->toXML($e);
        }

        return $e;
    }

}
