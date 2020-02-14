<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 Metadata AttributeConsumingService element.
 *
 * @package simplesamlphp/saml2
 */
final class AttributeConsumingService extends AbstractMdElement
{
    use IndexedElementTrait;

    /**
     * The ServiceName of this AttributeConsumingService.
     *
     * @var \SAML2\XML\md\ServiceName[]
     */
    protected $serviceNames = [];

    /**
     * The ServiceDescription of this AttributeConsumingService.
     *
     * @var \SAML2\XML\md\ServiceDescription[]
     */
    protected $serviceDescriptions = [];

    /**
     * The RequestedAttribute elements.
     *
     * @var \SAML2\XML\md\RequestedAttribute[]
     */
    protected $requestedAttributes = [];


    /**
     * AttributeConsumingService constructor.
     *
     * @param int $index
     * @param \SAML2\XML\md\ServiceName[] $name
     * @param \SAML2\XML\md\RequestedAttribute[] $requestedAttributes
     * @param bool|null $isDefault
     * @param \SAML2\XML\md\ServiceDescription[] $description
     */
    public function __construct(
        int $index,
        array $name,
        array $requestedAttributes,
        ?bool $isDefault = null,
        array $description = []
    ) {
        $this->setIndex($index);
        $this->setServiceNames($name);
        $this->setRequestedAttributes($requestedAttributes);
        $this->setIsDefault($isDefault);
        $this->setServiceDescriptions($description);
    }


    /**
     * Initialize / parse an AttributeConsumingService.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeConsumingService');
        Assert::same($xml->namespaceURI, AttributeConsumingService::NS);

        $names = ServiceName::getChildrenOfClass($xml);
        Assert::minCount($names, 1, 'Missing at least one ServiceName in AttributeConsumingService.');

        $descriptions = ServiceDescription::getChildrenOfClass($xml);

        $requestedAttrs = [];
        /** @var \DOMElement $ra */
        foreach (Utils::xpQuery($xml, './saml_metadata:RequestedAttribute') as $ra) {
            $requestedAttrs[] = RequestedAttribute::fromXML($ra);
        }

        return new self(
            self::getIntegerAttribute($xml, 'index'),
            $names,
            $requestedAttrs,
            self::getBooleanAttribute($xml, 'isDefault', null),
            $descriptions
        );
    }


    /**
     * Get the localized names of this service.
     *
     * @return \SAML2\XML\md\ServiceName[]
     */
    public function getServiceNames(): array
    {
        return $this->serviceNames;
    }


    /**
     * Set the localized names of this service.
     *
     * @param \SAML2\XML\md\ServiceName[] $serviceNames
     * @throws \InvalidArgumentException
     */
    protected function setServiceNames(array $serviceNames): void
    {
        Assert::minCount($serviceNames, 1, 'Missing at least one ServiceName in AttributeConsumingService.');
        Assert::allIsInstanceOf(
            $serviceNames,
            ServiceName::class,
            'Service names must be specified as ServiceName objects.'
        );
        $this->serviceNames = $serviceNames;
    }


    /**
     * Collect the value of the ServiceDescription-property
     *
     * @return \SAML2\XML\md\ServiceDescription[]
     */
    public function getServiceDescriptions(): array
    {
        return $this->serviceDescriptions;
    }


    /**
     * Set the value of the ServiceDescription-property
     *
     * @param \SAML2\XML\md\ServiceDescription[] $serviceDescriptions
     * @throws \InvalidArgumentException
     */
    protected function setServiceDescriptions(array $serviceDescriptions): void
    {
        Assert::allIsInstanceOf(
            $serviceDescriptions,
            ServiceDescription::class,
            'Service descriptions must be specified as ServiceDescription objects.'
        );
        $this->serviceDescriptions = $serviceDescriptions;
    }


    /**
     * Collect the value of the RequestedAttribute-property
     *
     * @return \SAML2\XML\md\RequestedAttribute[]
     */
    public function getRequestedAttributes(): array
    {
        return $this->requestedAttributes;
    }


    /**
     * Set the value of the RequestedAttribute-property
     *
     * @param \SAML2\XML\md\RequestedAttribute[] $requestedAttributes
     * @throws \InvalidArgumentException
     */
    public function setRequestedAttributes(array $requestedAttributes): void
    {
        Assert::minCount(
            $requestedAttributes,
            1,
            'Missing at least one RequestedAttribute in AttributeConsumingService.'
        );
        $this->requestedAttributes = $requestedAttributes;
    }


    /**
     * Convert to \DOMElement.
     *
     * @param \DOMElement $parent The element we should append this AttributeConsumingService to.
     * @return \DOMElement
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('index', strval($this->index));

        if ($this->isDefault === true) {
            $e->setAttribute('isDefault', 'true');
        } elseif ($this->isDefault === false) {
            $e->setAttribute('isDefault', 'false');
        }

        foreach ($this->serviceNames as $name) {
            $name->toXML($e);
        }
        foreach ($this->serviceDescriptions as $description) {
            $description->toXML($e);
        }
        foreach ($this->requestedAttributes as $ra) {
            $ra->toXML($e);
        }

        return $e;
    }
}
