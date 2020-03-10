<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use InvalidArgumentException;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * SAML BaseID data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class BaseID extends AbstractBaseIDType
{
    /**
     * Convert XML into an BaseID
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\AbstractXMLElement
     * @throws \InvalidArgumentException  If xsi:type is not defined or matches the AbstractBaseIDType
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'BaseID');
        Assert::same($xml->namespaceURI, BaseID::NS);
        Assert::true($xml->hasAttributeNS(Constants::NS_XSI, 'type'), 'Missing required xsi:type in <saml:BaseID> element.');

        $type = $xml->getAttributeNS(Constants::NS_XSI, 'type');
        Assert::notSame($type, 'AbstractBaseIDType', 'Cannot inherit from AbstractBaseIDType directly;  please define your own sub-class.');

        $registeredClass = ContainerSingleton::getRegisteredClass($xml->namespaceURI, $type);
        if ($registeredClass !== false) {
            if (is_subclass_of($registeredClass, AbstractBaseIDType::class)) {
                return $registeredClass::fromXML($xml);
            }

            throw new InvalidArgumentException('The type \'' . $type . '\' was not found as a descendant of AbstractBaseIDType.');
        }

        throw new InvalidArgumentException('The type \'' . $type . '\' was not found as a registered extension.');
    }
}
