<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AbstractCondition;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Example class to demonstrate how Condition can be extended.
 *
 * @package simplesamlphp\saml2
 */
final class CustomCondition extends AbstractCondition
{
    /** @var string */
    protected const XSI_TYPE_NAME = 'CustomConditionType';

    /** @var string */
    protected const XSI_TYPE_NAMESPACE = C::NAMESPACE;

    /** @var string */
    protected const XSI_TYPE_PREFIX = 'ssp';


    /**
     * CustomCondition constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     */
    public function __construct(
        protected array $audience,
    ) {
        Assert::allIsInstanceOf($audience, Audience::class);

        parent::__construct(self::XSI_TYPE_PREFIX . ':' . self::XSI_TYPE_NAME);
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
     * Convert XML into a Condition
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\AbstractCondition
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Condition', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AbstractCondition::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Condition> element.',
            InvalidDOMElementException::class,
        );

        $type = $xml->getAttributeNS(C::NS_XSI, 'type');
        Assert::same($type, self::XSI_TYPE_PREFIX . ':' . self::XSI_TYPE_NAME);

        $audience = Audience::getChildrenOfClass($xml);

        return new static($audience);
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
