<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMElement;
use SimpleSAML\SAML2\XML\saml\Condition;
use SimpleSAML\Assert\Assert;

/**
 * @covers \SAML2\CustomCondition
 * @package simplesamlphp\saml2
 */
final class CustomCondition extends Condition
{
    protected const XSI_TYPE = 'CustomCondition';


    /**
     * CustomCondition constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        parent::__construct($value, self::XSI_TYPE);
    }


    /**
     * @inheritDoc
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->getAttributeNS(Constants::NS_XSI, 'type'), self::XSI_TYPE);

        return new self($xml->textContent);
    }


    /**
     * @inheritDoc
     */
    public static function getXsiType(): string
    {
        return self::XSI_TYPE;
    }
}
