<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\Condition;
use SimpleSAML\Assert\Assert;

/**
 * @covers \SimpleSAML\Test\SAML2\CustomCondition
 * @package simplesamlphp\saml2
 */
final class CustomCondition extends Condition
{
    protected const XSI_TYPE = 'ssp:CustomCondition';

    /** @var \SimpleSAML\SAML2\XML\saml\Audience[] */
    protected $audience = [];


    /**
     * CustomCondition constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $value
     */
    public function __construct(array $audience)
    {
        parent::__construct(self::XSI_TYPE);

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
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->getAttributeNS(Constants::NS_XSI, 'type'), self::XSI_TYPE);

        return new self($xml->textContent);
    }
     */


    /**
     * @inheritDoc
     */
    public static function getXsiType(): string
    {
        return self::XSI_TYPE;
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
