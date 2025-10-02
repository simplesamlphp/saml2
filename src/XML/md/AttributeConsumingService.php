<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\UnsignedShortValue;

use function var_export;

/**
 * Class representing SAML 2 Metadata AttributeConsumingService element.
 *
 * @package simplesamlphp/saml2
 */
final class AttributeConsumingService extends AbstractMdElement implements SchemaValidatableElementInterface
{
    use IndexedElementTrait;
    use SchemaValidatableElementTrait;


    /**
     * AttributeConsumingService constructor.
     *
     * @param \SimpleSAML\XMLSchema\Type\UnsignedShortValue $index
     * @param \SimpleSAML\SAML2\XML\md\ServiceName[] $serviceName
     * @param \SimpleSAML\SAML2\XML\md\RequestedAttribute[] $requestedAttribute
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $isDefault
     * @param \SimpleSAML\SAML2\XML\md\ServiceDescription[] $serviceDescription
     */
    public function __construct(
        UnsignedShortValue $index,
        protected array $serviceName,
        protected array $requestedAttribute,
        ?BooleanValue $isDefault = null,
        protected array $serviceDescription = [],
    ) {
        Assert::maxCount($serviceName, C::UNBOUNDED_LIMIT);
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
        Assert::maxCount($serviceDescription, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $serviceDescription,
            ServiceDescription::class,
            'Service descriptions must be specified as ServiceDescription objects.',
        );
        Assert::maxCount($requestedAttribute, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $requestedAttribute,
            RequestedAttribute::class,
            'Requested attributes must be specified as RequestedAttribute objects.',
        );
        Assert::minCount(
            $requestedAttribute,
            1,
            'Missing at least one RequestedAttribute in AttributeConsumingService.',
            MissingElementException::class,
        );

        $this->setIndex($index);
        $this->setIsDefault($isDefault);
    }


    /**
     * Initialize / parse an AttributeConsumingService.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AttributeConsumingService', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeConsumingService::NS, InvalidDOMElementException::class);

        $names = ServiceName::getChildrenOfClass($xml);
        Assert::minCount(
            $names,
            1,
            'Missing at least one ServiceName in AttributeConsumingService.',
            MissingElementException::class,
        );

        $descriptions = ServiceDescription::getChildrenOfClass($xml);

        $requestedAttrs = RequestedAttribute::getChildrenOfClass($xml);

        return new static(
            self::getAttribute($xml, 'index', UnsignedShortValue::class),
            $names,
            $requestedAttrs,
            self::getOptionalAttribute($xml, 'isDefault', BooleanValue::class, null),
            $descriptions,
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
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('index', $this->getIndex()->getValue());

        if ($this->getIsDefault() !== null) {
            $e->setAttribute('isDefault', var_export($this->getIsDefault()->toBoolean(), true));
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
