<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\SAML2\Utils;

use function strval;

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
     * @var \SimpleSAML\SAML2\XML\md\ServiceName[]
     */
    protected array $serviceNames = [];

    /**
     * The ServiceDescription of this AttributeConsumingService.
     *
     * @var \SimpleSAML\SAML2\XML\md\ServiceDescription[]
     */
    protected array $serviceDescriptions = [];

    /**
     * The RequestedAttribute elements.
     *
     * @var \SimpleSAML\SAML2\XML\md\RequestedAttribute[]
     */
    protected array $requestedAttributes = [];


    /**
     * AttributeConsumingService constructor.
     *
     * @param int $index
     * @param \SimpleSAML\SAML2\XML\md\ServiceName[] $name
     * @param \SimpleSAML\SAML2\XML\md\RequestedAttribute[] $requestedAttributes
     * @param bool|null $isDefault
     * @param \SimpleSAML\SAML2\XML\md\ServiceDescription[] $description
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
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeConsumingService', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeConsumingService::NS, InvalidDOMElementException::class);

        $index = self::getIntegerAttribute($xml, 'index');
        $names = ServiceName::getChildrenOfClass($xml);
        Assert::minCount(
            $names,
            1,
            'Missing at least one ServiceName in AttributeConsumingService.',
            MissingElementException::class
        );

        $descriptions = ServiceDescription::getChildrenOfClass($xml);

        $requestedAttrs = RequestedAttribute::getChildrenOfClass($xml);

        return new self(
            $index,
            $names,
            $requestedAttrs,
            self::getBooleanAttribute($xml, 'isDefault', null),
            $descriptions
        );
    }


    /**
     * Get the localized names of this service.
     *
     * @return \SimpleSAML\SAML2\XML\md\ServiceName[]
     */
    public function getServiceNames(): array
    {
        return $this->serviceNames;
    }


    /**
     * Set the localized names of this service.
     *
     * @param \SimpleSAML\SAML2\XML\md\ServiceName[] $serviceNames
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setServiceNames(array $serviceNames): void
    {
        Assert::minCount(
            $serviceNames,
            1,
            'Missing at least one ServiceName in AttributeConsumingService.',
            MissingElementException::class,
        );
        Assert::allIsInstanceOf(
            $serviceNames,
            ServiceName::class,
            'Service names must be specified as ServiceName objects.',
        );
        $this->serviceNames = $serviceNames;
    }


    /**
     * Collect the value of the ServiceDescription-property
     *
     * @return \SimpleSAML\SAML2\XML\md\ServiceDescription[]
     */
    public function getServiceDescriptions(): array
    {
        return $this->serviceDescriptions;
    }


    /**
     * Set the value of the ServiceDescription-property
     *
     * @param \SimpleSAML\SAML2\XML\md\ServiceDescription[] $serviceDescriptions
     * @throws \SimpleSAML\Assert\AssertionFailedException
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
     * @return \SimpleSAML\SAML2\XML\md\RequestedAttribute[]
     */
    public function getRequestedAttributes(): array
    {
        return $this->requestedAttributes;
    }


    /**
     * Set the value of the RequestedAttribute-property
     *
     * @param \SimpleSAML\SAML2\XML\md\RequestedAttribute[] $requestedAttributes
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function setRequestedAttributes(array $requestedAttributes): void
    {
        Assert::allIsInstanceOf(
            $requestedAttributes,
            RequestedAttribute::class,
            'Requested attributes must be specified as RequestedAttribute objects.'
        );
        Assert::minCount(
            $requestedAttributes,
            1,
            'Missing at least one RequestedAttribute in AttributeConsumingService.',
            MissingElementException::class
        );
        $this->requestedAttributes = $requestedAttributes;
    }


    /**
     * Convert to \DOMElement.
     *
     * @param \DOMElement $parent The element we should append this AttributeConsumingService to.
     * @return \DOMElement
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
