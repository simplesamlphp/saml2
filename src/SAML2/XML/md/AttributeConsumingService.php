<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Exception;
use InvalidArgumentException;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 Metadata AttributeConsumingService element.
 *
 * @package SimpleSAMLphp
 */
final class AttributeConsumingService extends AbstractMdElement
{
    use IndexedElement;

    /**
     * The ServiceName of this AttributeConsumingService.
     *
     * @var ServiceName[]
     */
    protected $serviceNames = [];

    /**
     * The ServiceDescription of this AttributeConsumingService.
     *
     * @var ServiceDescription[]
     */
    protected $serviceDescriptions = [];

    /**
     * The RequestedAttribute elements.
     *
     * This is an array of SAML_RequestedAttributeType elements.
     *
     * @var RequestedAttribute[]
     */
    protected $requestedAttributes = [];


    /**
     * AttributeConsumingService constructor.
     *
     * @param int                  $index
     * @param string[]             $name
     * @param RequestedAttribute[] $requestedAttributes
     * @param bool|null            $isDefault
     * @param array|null           $description
     */
    public function __construct(
        int $index,
        array $name,
        array $requestedAttributes,
        ?bool $isDefault = null,
        ?array $description = null
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
     * @param DOMElement|null $xml The XML element we should load.
     *
     * @return self
     * @throws Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        $names = ServiceName::extractFromChildren($xml);
        Assert::minCount($names, 1, 'Missing at least one ServiceName in AttributeConsumingService.');

        $descriptions = ServiceDescription::extractFromChildren($xml);

        $requestedAttrs = [];
        /** @var DOMElement $ra */
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
     * @return ServiceName[]
     */
    public function getServiceNames(): array
    {
        return $this->serviceNames;
    }


    /**
     * Set the localized names of this service.
     *
     * @param ServiceName[] $serviceNames
     *
     * @return void
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
     * @return ServiceDescription[]
     */
    public function getServiceDescriptions(): array
    {
        return $this->serviceDescriptions;
    }


    /**
     * Set the value of the ServiceDescription-property
     *
     * @param ServiceDescription[] $serviceDescriptions
     *
     * @return void
     */
    protected function setServiceDescriptions(?array $serviceDescriptions): void
    {
        if ($serviceDescriptions === null) {
            return;
        }
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
     * @return RequestedAttribute[]
     */
    public function getRequestedAttributes(): array
    {
        return $this->requestedAttributes;
    }


    /**
     * Set the value of the RequestedAttribute-property
     *
     * @param RequestedAttribute[] $requestedAttributes
     *
     * @return void
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
     * @param DOMElement $parent The element we should append this AttributeConsumingService to.
     *
     * @return DOMElement
     *
     * @throws InvalidArgumentException if assertions are false
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
