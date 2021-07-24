<?php

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

use function strval;

/**
 * @package simplesamlphp/saml2
 */
final class ProxyRestriction extends AbstractSamlElement
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\Audience[]
     */
    protected array $audience = [];

    /**
     * @param int|null
     */
    protected ?int $count;


    /**
     * ProxyRestriction constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audience
     * @param int|null $count
     */
    public function __construct(array $audience = [], ?int $count = null)
    {
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
     */
    protected function setCount(?int $count): void
    {
        Assert::nullOrNatural($count, 'Count must be a non-negative integer.');
        $this->count = $count;
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
     * @param \DOMElement $xml
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ProxyRestriction', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ProxyRestriction::NS, InvalidDOMElementException::class);

        $count = self::getIntegerAttribute($xml, 'Count', null);
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
        $e = $this->instantiateParentElement($parent);

        if ($this->count !== null) {
            $e->setAttribute('Count', strval($this->count));
        }

        foreach ($this->audience as $audience) {
            $audience->toXML($e);
        }

        return $e;
    }
}
