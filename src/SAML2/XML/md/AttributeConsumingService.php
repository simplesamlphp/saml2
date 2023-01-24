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
     * AttributeConsumingService constructor.
     *
     * @param int $index
     * @param \SimpleSAML\SAML2\XML\md\ServiceName[] $serviceName
     * @param \SimpleSAML\SAML2\XML\md\RequestedAttribute[] $requestedAttribute
     * @param bool|null $isDefault
     * @param \SimpleSAML\SAML2\XML\md\ServiceDescription[] $serviceDescription
     */
    public function __construct(
        int $index,
        protected array $serviceName,
        protected array $requestedAttribute,
        ?bool $isDefault = null,
        protected array $serviceDescription = []
    ) {
        Assert::minCount(
            $serviceName,
            1,
            'Missing at least one ServiceName in AttributeConsumingService.',
            MissingElementException::class,
        );
        Assert::allIsInstanceOf(
            $serviceName,
            ServiceName::class,
            'Service names must be specified as ServiceName objects.',
        );
        Assert::allIsInstanceOf(
            $serviceDescription,
            ServiceDescription::class,
            'Service descriptions must be specified as ServiceDescription objects.'
        );
        Assert::allIsInstanceOf(
            $requestedAttribute,
            RequestedAttribute::class,
            'Requested attributes must be specified as RequestedAttribute objects.'
        );
        Assert::minCount(
            $requestedAttribute,
            1,
            'Missing at least one RequestedAttribute in AttributeConsumingService.',
            MissingElementException::class
        );

        $this->setIndex($index);
        $this->setIsDefault($isDefault);
    }


    /**
     * Initialize / parse an AttributeConsumingService.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): static
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

        return new static(
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
    public function getServiceName(): array
    {
        return $this->serviceName;
    }


    /**
     * Collect the value of the ServiceDescription-property
     *
     * @return \SimpleSAML\SAML2\XML\md\ServiceDescription[]
     */
    public function getServiceDescription(): array
    {
        return $this->serviceDescription;
    }


    /**
     * Collect the value of the RequestedAttribute-property
     *
     * @return \SimpleSAML\SAML2\XML\md\RequestedAttribute[]
     */
    public function getRequestedAttribute(): array
    {
        return $this->requestedAttribute;
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
        $e->setAttribute('index', strval($this->getIndex()));

        if ($this->getIsDefault() === true) {
            $e->setAttribute('isDefault', 'true');
        } elseif ($this->getIsDefault() === false) {
            $e->setAttribute('isDefault', 'false');
        }

        foreach ($this->getServiceName() as $name) {
            $name->toXML($e);
        }
        foreach ($this->getServiceDescription() as $description) {
            $description->toXML($e);
        }
        foreach ($this->getRequestedAttribute() as $ra) {
            $ra->toXML($e);
        }

        return $e;
    }
}
