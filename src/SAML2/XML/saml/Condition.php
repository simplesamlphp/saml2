<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * SAML Condition data type.
 *
 * @package simplesamlphp/saml2
 */
abstract class Condition extends AbstractConditionType
{
    /** @var string */
    public const LOCALNAME = 'Condition';

    /** @var string */
    protected string $type;


    /**
     * Initialize a saml:Condition from scratch
     *
     * @param string $type
     */
    protected function __construct(
        string $type
    ) {
        $this->setType($type);
    }


    /**
     * Get the type of this Condition (expressed in the xsi:type attribute).
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Set the type of this Condition (in the xsi:type attribute)
     *
     * @param string $type
     */
    protected function setType(string $type): void
    {
        Assert::notWhitespaceOnly($type, 'The "xsi:type" attribute of a Condition cannot be empty.');
        Assert::contains($type, ':');

        $this->type = $type;
    }


    /**
     * Convert this Condition to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('xmlns:' . static::XSI_TYPE_PREFIX, static::XSI_TYPE_NS);
        $e->setAttributeNS(Constants::NS_XSI, 'xsi:type', $this->type);

        return $e;
    }
}
