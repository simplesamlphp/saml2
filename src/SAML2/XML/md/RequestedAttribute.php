<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 metadata RequestedAttribute.
 *
 * @package SimpleSAMLphp
 */
class RequestedAttribute extends AbstractMdElement
{
    /**
     * Whether this attribute is required.
     *
     * @var bool|null
     */
    protected $isRequired = null;


    /**
     * The actual attribute
     *
     * @var \SAML2\XML\saml\Attribute
     */
    protected $attribute;


    /**
     * Initialize an RequestedAttribute.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct($xml);

        if ($xml === null) {
            return;
        }

        $this->isRequired = Utils::parseBoolean($xml, 'isRequired', null);
    }


    /**
     * Collect the value of the isRequired-property
     *
     * @return bool|null
     */
    public function getIsRequired(): ?bool
    {
        return $this->isRequired;
    }


    /**
     * Set the value of the isRequired-property
     *
     * @param bool|null $flag
     * @return void
     */
    private function setIsRequired(bool $flag = null): void
    {
        $this->isRequired = $flag;
    }


    /**
     * Convert XML into a RequestedAttribute
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\md\RequestedAttribute
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'RequestedAttribute');
        Assert::same($xml->namespaceURI, Constants::NS_MD);

        $attribute = Attribute::fromXML($xml);
        $isRequired = Utils::parseBoolean($xml, 'isRequired', null);

        return new self($attribute, $isRequired);
    }


    /**
     * Convert this RequestedAttribute to XML.
     *
     * @param \DOMElement|null $parent The element we should append this RequestedAttribute to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $attribute = $this->attribute;

        if (is_bool($this->isRequired)) {
            $e->setAttribute('isRequired', $this->isRequired ? 'true' : 'false');
        }

        $e->setAttribute('Name', $attribute->getName());

        $nameFormat = $attribute->getNameFormat();
        if ($nameFormat !== null) {
            $e->setAttribute('NameFormat', $nameFormat());
        }

        $friendlyName = $attribute->getFriendlyName();
        if ($friendlyName !== null) {
            $e->setAttribute('FriendlyName', $friendlyName);
        }

        foreach ($attribute->getAttributeValue() as $av) {
            $e->appendChild($e->ownerDocument->importNode($av->toXML(), true));
        }

        return $e;
    }
}
