<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\NCNameValue;

/**
 * Class representing SAML 2 SubjectConfirmationData element.
 *
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationData extends AbstractSubjectConfirmationData implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Convert XML into a SubjectConfirmationData
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     * @throws \SimpleSAML\Assert\AssertionFailedException
     *   if NotBefore or NotOnOrAfter contain an invalid date.
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SubjectConfirmationData', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SubjectConfirmationData::NS, InvalidDOMElementException::class);

        return new static(
            self::getOptionalAttribute($xml, 'NotBefore', SAMLDateTimeValue::class, null),
            self::getOptionalAttribute($xml, 'NotOnOrAfter', SAMLDateTimeValue::class, null),
            self::getOptionalAttribute($xml, 'Recipient', EntityIDValue::class, null),
            self::getOptionalAttribute($xml, 'InResponseTo', NCNameValue::class, null),
            self::getOptionalAttribute($xml, 'Address', SAMLStringValue::class, null),
            self::getChildElementsFromXML($xml),
            self::getAttributesNSFromXML($xml),
        );
    }
}
