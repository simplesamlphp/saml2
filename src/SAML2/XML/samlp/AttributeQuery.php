<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\Subject;
use SimpleSAML\Assert\Assert;

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
     * @var \SAML2\XML\saml\Attribute[]
     */
    protected $attributes = [];


    /**
     * Constructor for SAML 2 AttributeQuery.
     *
     * @param \SAML2\XML\saml\Subject $subject
     * @param \SAML2\XML\saml\Attribute[] $attributes
     * @param \SAML2\XML\saml\Issuer $issuer
     * @param string $id
     * @param int $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SAML2\XML\samlp\Extensions $extensions
     */
    public function __construct(
        Subject $subject,
        array $attributes = [],
        ?Issuer $issuer = null,
        ?string $id = null,
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null
    ) {
        parent::__construct($subject, $issuer, $id, $issueInstant, $destination, $consent, $extensions);

        $this->setAttributes($attributes);
    }


    /**
     * Retrieve all requested attributes.
     *
     * @return \SAML2\XML\saml\Attribute[] All requested attributes, as an associative array.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * Set all requested attributes.
     *
     * @param \SAML2\XML\saml\Attribute[] $attributes All requested attributes, as an associative array.
     * @return void
     */
    public function setAttributes(array $attributes): void
    {
        Assert::allIsInstanceOf($attributes, Attribute::class);
        $this->attributes = $attributes;
    }


    /**
     * Create a class from XML
     *
     * @param \DOMElement $xml
     * @return self
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeQuery', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeQuery::NS, InvalidDOMElementException::class);
        Assert::same('2.0', self::getAttribute($xml, 'Version'));

        $id = self::getAttribute($xml, 'ID');
        $issueInstant = Utils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.');

        $subject = Subject::getChildrenOfClass($xml);
        Assert::notEmpty($subject, 'Missing subject in subject query.');
        Assert::maxCount($subject, 1, 'More than one <saml:Subject> in AttributeQuery');

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.');

        $request = new self(
            array_pop($subject),
            Attribute::getChildrenOfClass($xml),
            array_pop($issuer),
            $id,
            $issueInstant,
            $destination,
            $consent,
            array_pop($extensions)
        );

        if (!empty($signature)) {
            $request->setSignature($signature[0]);
        }

        return $request;
    }


    /**
     * Convert the attribute query message to an XML element.
     *
     * @return \DOMElement This attribute query.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $parent = parent::toXML($parent);

        foreach ($this->attributes as $attribute) {
            $attribute->toXML($parent);
        }

        return $this->signElement($parent);
    }
}
