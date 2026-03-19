<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function in_array;

/**
 * Class for SAML 2 attribute query messages.
 *
 * An attribute query asks for a set of attributes. The following
 * rules apply:
 *
 * - If no attributes are present in the query, all attributes should be
 *   returned.
 * - If any attributes are present, only those attributes which are present
 *   in the query should be returned.
 * - If an attribute contains any attribute values, only the attribute values
 *   which match those in the query should be returned.
 *
 * @package simplesamlphp/saml2
 */
class AttributeQuery extends AbstractSubjectQuery implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Constructor for SAML 2 AttributeQuery.
     *
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\saml\Subject $subject
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attributes
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    final public function __construct(
        IDValue $id,
        Subject $subject,
        SAMLDateTimeValue $issueInstant,
        protected array $attributes = [],
        ?Issuer $issuer = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::maxCount($attributes, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($attributes, Attribute::class);

        $cache = [];
        foreach ($attributes as $attribute) {
            $name = $attribute->getName();
            $nameFormat = $attribute->getNameFormat()?->getValue() ?? C::NAMEFORMAT_UNSPECIFIED;

            if (isset($cache[$nameFormat])) {
                Assert::true(
                    !in_array($name, $cache[$nameFormat], true),
                    'A single query MUST NOT contain two <saml:Attribute> elements with the same Name and NameFormat.',
                    ProtocolViolationException::class,
                );
            }
            $cache[$nameFormat][] = $name;
        }
        unset($cache);

        parent::__construct($id, $subject, $issuer, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Retrieve all requested attributes.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Attribute[] All requested attributes, as an associative array.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * Create a class from XML
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AttributeQuery', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeQuery::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version', SAMLStringValue::class);
        Assert::true(version_compare('2.0', strval($version), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', strval($version), '>='), RequestVersionTooHighException::class);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one saml:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $subject = Subject::getChildrenOfClass($xml);
        Assert::notEmpty($subject, 'Missing subject in subject query.', MissingElementException::class);
        Assert::maxCount(
            $subject,
            1,
            'More than one <saml:Subject> in AttributeQuery',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $request = new static(
            self::getAttribute($xml, 'ID', IDValue::class),
            array_pop($subject),
            self::getAttribute($xml, 'IssueInstant', SAMLDateTimeValue::class),
            Attribute::getChildrenOfClass($xml),
            array_pop($issuer),
            self::getOptionalAttribute($xml, 'Destination', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'Consent', SAMLAnyURIValue::class, null),
            array_pop($extensions),
        );

        if (!empty($signature)) {
            $request->setSignature($signature[0]);
            $request->setXML($xml);
        }

        return $request;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        foreach ($this->getAttributes() as $attribute) {
            $attribute->toXML($e);
        }

        return $e;
    }
}
