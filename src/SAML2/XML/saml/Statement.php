<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class implementing the <saml:Statement> extension point.
 *
 * @package simplesamlphp/saml2
 */
abstract class Statement extends AbstractStatement
{
    /** @var string */
    protected string $type;


    /**
     * Initialize a saml:Statement from scratch
     *
     * @param string $type
     */
    protected function __construct(string $type)
    {
        $this->setType($type);
    }


    /**
     * @inheritDoc
     */
    final public function getLocalName(): string
    {
        return 'Statement';
    }


    /**
     * Get the type of this BaseID (expressed in the xsi:type attribute).
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Set the type of this BaseID (in the xsi:type attribute)
     *
     * @param string $type
     *
     */
    protected function setType(string $type): void
    {
        Assert::notEmpty($type, 'The "xsi:type" attribute of an identifier cannot be empty.');
        $this->type = $type;
    }


    /**
     * Convert this Statement to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this BaseID.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $element = $this->instantiateParentElement($parent);

        $element->setAttributeNS(Constants::NS_XSI, 'xsi:type', $this->type);

        return $element;
    }
}
