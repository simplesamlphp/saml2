<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use Webmozart\Assert\Assert;

/**
 * Class implementing the <saml:Statement> extension point.
 *
 * @package simplesamlphp/saml2
 */
class Statement extends AbstractStatement
{

    /** @var string */
    protected $type;


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
     * @return void
     */
    protected function setType(string $type): void
    {
        Assert::notEmpty($type, 'The "xsi:type" attribute of an identifier cannot be empty.');
        $this->type = $type;
    }


    /**
     * Convert XML into an Statement
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\saml\Statement
     * @throws \InvalidArgumentException  If xsi:type is not defined or does not implement IdentifierInterface
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Statement');
        Assert::notNull($xml->namespaceURI);
        Assert::same($xml->namespaceURI, Statement::NS);
        Assert::true(
            $xml->hasAttributeNS(Constants::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Statement> element.'
        );

        return new self($xml->getAttributeNS(Constants::NS_XSI, 'type'));
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
