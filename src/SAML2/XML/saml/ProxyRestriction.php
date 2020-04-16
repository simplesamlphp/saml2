<?php

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use Webmozart\Assert\Assert;

final class ProxyRestriction extends AbstractConditionType
{
    protected const XSI_TYPE = 'ProxyRestriction';

    /**
     * @param \SAML2\XML\saml\Audience[]
     */
    protected $audience = [];

    /**
     * @param int|null
     */
    protected $count;


    /**
     * ProxyRestriction constructor.
     *
     * @param \SAML2\XML\saml\Audience[] $audience
     * @param int|null $count
     */
    public function __construct(array $audience = [], ?int $count = null)
    {
        parent::__construct('');

        $this->setCount($count);
        $this->setAudience($audience);
    }


    /**
     * Get the value of the count-attribute.
     *
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }


    /**
     * Set the value of the count-attribute
     *
     * @param int|null $count
     * @return void
     */
    protected function setCount(?int $count): void
    {
        Assert::nullOrNatural($count, 'Count must be a non-negative integer.');
        $this->count = $count;
    }


    /**
     * Get the value of the audience-attribute.
     *
     * @return \SAML2\XML\saml\Audience[]
     */
    public function getAudience(): array
    {
        return $this->audience;
    }


    /**
     * Set the value of the audience-attribute
     *
     * @param \SAML2\XML\saml\Audience[] $audience
     * @return void
     */
    protected function setAudience(array $audience): void
    {
        Assert::allIsInstanceOf($audience, Audience::class);

        $this->audience = $audience;
    }


    /**
     * @param \DOMElement $xml
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ProxyRestriction');
        Assert::same($xml->namespaceURI, ProxyRestriction::NS);

        $count = self::getAttribute($xml, 'Count', null);
        if ($count !== null) {
            $count = intval($count);
        }

        $audience = Audience::getChildrenOfClass($xml);

        return new self($audience, $count);
    }


    /**
     * Convert this Condition to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $element = parent::toXML($parent);

        if ($this->count !== null) {
            $element->setAttribute('Count', $this->count);
        }

        foreach ($this->audience as $audience) {
            $audience->toXML($element);
        }

        return $element;
    }
}
