<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\BaseID;
use SimpleSAML\SAML2\XML\saml\CustomIdentifierInterface;

/**
 * @covers \SimpleSAML\Test\SAML2\CustomBaseID
 * @package simplesamlphp\saml2
 */
final class CustomBaseID extends BaseID implements CustomIdentifierInterface
{
    /** @var string */
    protected const XSI_TYPE = 'ssp:CustomBaseID';

    /** @var string */
    protected const XSI_TYPE_NS = 'urn:custom:ssp';

    /** @var string */
    protected const XSI_TYPE_PREFIX = 'ssp';


    /**
     * CustomBaseID constructor.
     *
     * @param float $value
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    public function __construct(float $value, string $NameQualifier = null, string $SPNameQualifier = null)
    {
        parent::__construct(self::XSI_TYPE, strval($value), $NameQualifier, $SPNameQualifier);
    }


    /**
     * @inheritDoc
     */
    public static function getXsiType(): string
    {
        return self::XSI_TYPE;
    }


    /**
     * @inheritDoc
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'BaseID', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, CustomBaseID::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(Constants::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:BaseID> element.',
            InvalidDOMElementException::class
        );

        $type = $xml->getAttributeNS(Constants::NS_XSI, 'type');
        list($prefix, $element) = explode(':', $type, 2);

        $ns = $xml->lookupNamespaceUri($prefix);
        $handler = Utils::getContainer()->getElementHandler($ns, $element);

        Assert::notNull($handler, 'Unknown BaseID type `' . $type . '`.');
        Assert::isAOf($handler, BaseID::class);

        $baseID = BaseID::getChildrenOfClass($xml);
        Assert::count($baseID, 1);

        return new $handler(floatval($xml->textContent), $baseID[0]->getNameQualifier(), $baseID[0]->getSPNameQualifier());
    }
}
