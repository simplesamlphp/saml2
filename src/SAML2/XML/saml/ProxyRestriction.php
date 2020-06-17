<?php

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Utils;
use SimpleSAML\Assert\Assert;

final class ProxyRestriction extends AbstractConditionType
{
    protected const XSI_TYPE = 'ProxyRestriction';

    /**
     * @param string[]
     */
    protected $audience = [];

    /**
     * @param int|null
     */
    protected $count;


    /**
     * ProxyRestriction constructor.
     *
     * @param string[] $audience
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
     * @return string[]
     */
    public function getAudience(): array
    {
        return $this->audience;
    }


    /**
     * Set the value of the audience-attribute
     *
     * @param string[] $audience
     * @return void
     */
    protected function setAudience(array $audience): void
    {
        Assert::allStringNotEmpty($audience);

        $this->audience = $audience;
    }


    /**
     * @param \DOMElement $xml
     * @return self
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ProxyRestriction', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ProxyRestriction::NS, InvalidDOMElementException::class);

        $count = self::getIntegerAttribute($xml, 'Count', null);
        $audience = Utils::extractStrings($xml, AbstractSamlElement::NS, 'Audience');

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
        $e = parent::toXML($parent);

        if ($this->count !== null) {
            $e->setAttribute('Count', $this->count);
        }

        Utils::addStrings($e, AbstractSamlElement::NS, 'saml:Audience', false, $this->audience);

        return $e;
    }
}
