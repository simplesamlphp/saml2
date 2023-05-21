<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Utils as XMLUtils;

use function intval;
use function strval;

/**
 * Class representing SAML 2 Metadata AttributeConsumingService element.
 *
 * @package SimpleSAMLphp
 */
class AttributeConsumingService
{
    /**
     * The index of this AttributeConsumingService.
     *
     * @var int
     */
    private int $index;

    /**
     * Whether this is the default AttributeConsumingService.
     *
     * @var bool|null
     */
    private ?bool $isDefault = null;

    /**
     * The ServiceName of this AttributeConsumingService.
     *
     * This is an array of ServiceName objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\ServiceName[]
     */
    private array $ServiceName = [];

    /**
     * The ServiceDescription of this AttributeConsumingService.
     *
     * This is an array of ServiceDescription objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\ServiceDescription[]
     */
    private array $ServiceDescription = [];

    /**
     * The RequestedAttribute elements.
     *
     * This is an array of SAML_RequestedAttributeType elements.
     *
     * @var \SimpleSAML\SAML2\XML\md\RequestedAttribute[]
     */
    private array $RequestedAttribute = [];


    /**
     * Initialize / parse an AttributeConsumingService.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('index')) {
            throw new MissingAttributeException('Missing index on AttributeConsumingService.');
        }
        $this->setIndex(intval($xml->getAttribute('index')));

        $this->setIsDefault(Utils::parseBoolean($xml, 'isDefault', null));

        $this->setServiceName(ServiceName::getChildrenOfClass($xml));
        if ($this->getServiceName() === []) {
            throw new MissingElementException('Missing ServiceName in AttributeConsumingService.');
        }

        $this->setServiceDescription(ServiceDescriptor::getChildrenOfClass($xml));

        /** @var \DOMElement $ra */
        foreach (XPath::xpQuery($xml, './saml_metadata:RequestedAttribute', XPath::getXPath($xml)) as $ra) {
            $this->addRequestedAttribute(new RequestedAttribute($ra));
        }
    }


    /**
     * Collect the value of the index-property
     *
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }


    /**
     * Set the value of the index-property
     *
     * @param int $index
     * @return void
     */
    public function setIndex(int $index): void
    {
        $this->index = $index;
    }


    /**
     * Collect the value of the isDefault-property
     *
     * @return bool|null
     */
    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }


    /**
     * Set the value of the isDefault-property
     *
     * @param bool|null $flag
     * @return void
     */
    public function setIsDefault(bool $flag = null): void
    {
        $this->isDefault = $flag;
    }


    /**
     * Collect the value of the ServiceName-property
     *
     * @return \SimpleSAML\SAML2\XML\md\ServiceName[]
     */
    public function getServiceName(): array
    {
        return $this->ServiceName;
    }


    /**
     * Set the value of the ServiceName-property
     *
     * @param \SimpleSAML\SAML2\XML\md\ServiceName[] $serviceName
     * @return void
     */
    public function setServiceName(array $serviceName): void
    {
        Assert::allIsInstanceOf($serviceName, ServiceName::class);
        $this->ServiceName = $serviceName;
    }


    /**
     * Collect the value of the ServiceDescription-property
     *
     * @return \SimpleSAML\SAML2\XML\md\ServiceDescription[]
     */
    public function getServiceDescription(): array
    {
        return $this->ServiceDescription;
    }


    /**
     * Set the value of the ServiceDescription-property
     *
     * @param \SimpleSAML\SAML2\XML\md\ServiceDescription[] $serviceDescription
     * @return void
     */
    public function setServiceDescription(array $serviceDescription): void
    {
        Assert::allIsInstanceOf($serviceDescription, ServiceDescription::class);
        $this->ServiceDescription = $serviceDescription;
    }


    /**
     * Collect the value of the RequestedAttribute-property
     *
     * @return \SimpleSAML\SAML2\XML\md\RequestedAttribute[]
     */
    public function getRequestedAttribute(): array
    {
        return $this->RequestedAttribute;
    }


    /**
     * Set the value of the RequestedAttribute-property
     *
     * @param \SimpleSAML\SAML2\XML\md\RequestedAttribute[] $requestedAttribute
     * @return void
     */
    public function setRequestedAttribute(array $requestedAttribute): void
    {
        $this->RequestedAttribute = $requestedAttribute;
    }


    /**
     * Add the value to the RequestedAttribute-property
     *
     * @param \SimpleSAML\SAML2\XML\md\RequestedAttribute $requestedAttribute
     * @return void
     */
    public function addRequestedAttribute(RequestedAttribute $requestedAttribute): void
    {
        $this->RequestedAttribute[] = $requestedAttribute;
    }


    /**
     * Convert to \DOMElement.
     *
     * @param \DOMElement $parent The element we should append this AttributeConsumingService to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(C::NS_MD, 'md:AttributeConsumingService');
        $parent->appendChild($e);

        $e->setAttribute('index', strval($this->getIndex()));

        if ($this->getIsDefault() === true) {
            $e->setAttribute('isDefault', 'true');
        } elseif ($this->getIsDefault() === false) {
            $e->setAttribute('isDefault', 'false');
        }

        foreach ($this->getServiceName() as $sn) {
            $sn->toXML($e);
        }

        foreach ($this->getServiceDescription() as $sd) {
            $sd->toXML($e);
        }

        foreach ($this->getRequestedAttribute() as $ra) {
            $ra->toXML($e);
        }

        return $e;
    }
}
