<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\SAML2\XML\saml\BaseID;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Assert\Assert;

use function floatval;
use function strval;

/**
 * @covers \SimpleSAML\Test\SAML2\CustomBaseID
 * @package simplesamlphp\saml2
 */
final class CustomBaseID extends BaseID
{
    /** @var string */
    protected const NS_XSI_TYPE_NAME = 'CustomBaseID';

    /** @var string */
    protected const NS_XSI_TYPE_NAMESPACE = C::NAMESPACE;

    /** @var string */
    protected const NS_XSI_TYPE_PREFIX = 'ssp';


    /**
     * CustomBaseID constructor.
     *
     * @param float $value
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    public function __construct(float $value, string $NameQualifier = null, string $SPNameQualifier = null)
    {
        parent::__construct(self::getXsiType(), $NameQualifier, $SPNameQualifier);
    }


    /**
     * @inheritDoc
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->getAttributeNS(C::NS_XSI, 'type'), 'CustomBaseID');

        $baseID = BaseID::fromXML($xml);
        return new self(floatval($xml->textContent), $baseID->getNameQualifier(), $baseID->getSPNameQualifier());
    }
}
