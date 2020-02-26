<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use Exception;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\XML\samlp\Extensions;
use Webmozart\Assert\Assert;

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
 * @package SimpleSAMLphp
 */
class AttributeQuery extends AbstractSubjectQuery
{
    /**
     * The attributes, as an associative array.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * The NameFormat used on all attributes.
     *
     * If more than one NameFormat is used, this will contain
     * the unspecified nameformat.
     *
     * @var string
     */
    private $nameFormat;


    /**
     * Constructor for SAML 2 attribute query messages.
     *
     * @param \SAML2\XML\saml\NameIDType $subject
     * @param array $attributes
     * @param string $nameFormat
     * @throws \Exception
     */
    public function __construct(NameID $subject, array $attributes, string $nameFormat = Constants::NAMEFORMAT_UNSPECIFIED)
    {
        parent::__construct($subject);

        $this->setAttributes($attributes);
        $this->setAttributeNameFormat($nameFormat);
    }


    /**
     * Retrieve all requested attributes.
     *
     * @return array All requested attributes, as an associative array.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * Set all requested attributes.
     *
     * @param array $attributes All requested attributes, as an associative array.
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }


    /**
     * Retrieve the NameFormat used on all attributes.
     *
     * If more than one NameFormat is used in the received attributes, this
     * returns the unspecified NameFormat.
     *
     * @return string The NameFormat used on all attributes.
     */
    public function getAttributeNameFormat(): string
    {
        return $this->nameFormat;
    }


    /**
     * Set the NameFormat used on all attributes.
     *
     * @param string $nameFormat The NameFormat used on all attributes.
     * @return void
     */
    public function setAttributeNameFormat(string $nameFormat): void
    {
        $this->nameFormat = $nameFormat;
    }


    /**
     * Convert XML into an AttributeQuery
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\AttributeQuery
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeQuery');
        Assert::same($xml->namespaceURI, AttributeQuery::NS);

        $ID = self::getAttribute($xml, 'ID');
        $Version = self::getAttribute($xml, 'Version');
        $IssueInstant = self::getAttribute($xml, 'IssueInstant');
        $Destination = self::getAttribute($xml, 'Destination', null);
        $Consent = self::getAttribute($xml, 'Consent', null);

        $issuer = Issuer::getChildrenOfClass($xml);
        $signature = Signature::getChildrenOfClass($xml);
        $extensions = Extensions::getChildrenOfClass($xml);

        $firstAttribute = true;
        $attributes = [];
        $nameFormat = Constants::NAMEFORMAT_UNSPECIFIED;

        /** @var \DOMElement[] $attributes */
        $attributeElts = Utils::xpQuery($xml, './saml_assertion:Attribute');
        foreach ($attributeElts as $attribute) {
            Assert::true($attribute->hasAttribute('Name'), 'Missing name on <saml:Attribute> element.');

            $name = $attribute->getAttribute('Name');

            $givenNameFormat = $nameFormat;
            if ($attribute->hasAttribute('NameFormat')) {
                $givenNameFormat = $attribute->getAttribute('NameFormat');
            }

            if ($firstAttribute) {
                $nameFormat = $givenNameFormat;
                $firstAttribute = false;
            } elseif ($nameFormat !== $givenNameFormat) {
                $nameFormat = Constants::NAMEFORMAT_UNSPECIFIED;
            }

            if (!array_key_exists($name, $attributes)) {
                $attributes[$name] = [];
            }

            $values = Utils::xpQuery($attribute, './saml_assertion:AttributeValue');
            foreach ($values as $value) {
                $attributes[$name][] = trim($value->textContent);
            }
        }
        $subject = AbstractSubjectQuery::parseSubject($xml);

        return new self($subject, $attributes, $nameFormat);
    }


    /**
     * Convert the attribute query message to an XML element.
     *
     * @return \DOMElement This attribute query.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->attributes as $name => $values) {
            $attribute = $e->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:Attribute');
            $e->appendChild($attribute);
            $attribute->setAttribute('Name', $name);

            if ($this->nameFormat !== Constants::NAMEFORMAT_UNSPECIFIED) {
                $attribute->setAttribute('NameFormat', $this->nameFormat);
            }

            foreach ($values as $value) {
                if (is_string($value)) {
                    $type = 'xs:string';
                } elseif (is_int($value)) {
                    $type = 'xs:integer';
                } else {
                    $type = null;
                }

                $attributeValue = Utils::addString(
                    $attribute,
                    Constants::NS_SAML,
                    'saml:AttributeValue',
                    strval($value)
                );
                if ($type !== null) {
                    $attributeValue->setAttributeNS(Constants::NS_XSI, 'xsi:type', $type);
                }
            }
        }

        return $e;
    }
}
