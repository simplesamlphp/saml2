<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * SAML AudienceRestriction data type.
 *
 * @package simplesamlphp/saml2
 */
final class AudienceRestriction extends AbstractConditionType
{
    /** @var string[] */
    protected array $audience = [];


    /**
     * Initialize a saml:AudienceRestriction
     *
     * @param string[] $audience
     */
    public function __construct(array $audience)
    {
        parent::__construct('');

        $this->setAudience($audience);
    }


    /**
     * Collect the audience
     *
     * @return string[]
     */
    public function getAudience(): array
    {
        return $this->audience;
    }


    /**
     * Set the value of the Audience-property
     *
     * @param string[] $audience
     * @return void
     */
    private function setAudience(array $audience): void
    {
        Assert::allStringNotEmpty($audience);

        $this->audience = $audience;
    }


    /**
     * Convert XML into an AudienceRestriction
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AudienceRestriction', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AudienceRestriction::NS, InvalidDOMElementException::class);

        $audience = XMLUtils::extractStrings($xml, AbstractSamlElement::NS, 'Audience');

        return new self($audience);
    }


    /**
     * Convert this Audience to XML.
     *
     * @param \DOMElement|null $element The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this AudienceRestriction.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        XMLUtils::addStrings($e, AbstractSamlElement::NS, 'saml:Audience', false, $this->audience);

        return $e;
    }
}
