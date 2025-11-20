<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\NonNegativeIntegerValue;

use function array_pop;
use function strval;

/**
 * Class for handling SAML2 Scoping.
 *
 * @package simplesamlphp/saml2
 */
final class Scoping extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize a Scoping element.
     *
     * @param \SimpleSAML\XMLSchema\Type\NonNegativeIntegerValue|null $proxyCount
     * @param \SimpleSAML\SAML2\XML\samlp\IDPList|null $IDPList
     * @param \SimpleSAML\SAML2\XML\samlp\RequesterID[] $requesterId
     */
    public function __construct(
        protected ?NonNegativeIntegerValue $proxyCount = null,
        protected ?IDPList $IDPList = null,
        protected array $requesterId = [],
    ) {
        Assert::maxCount($requesterId, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($requesterId, RequesterID::class);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\IDPList|null
     */
    public function getIDPList(): ?IDPList
    {
        return $this->IDPList;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\RequesterID[]
     */
    public function getRequesterId(): array
    {
        return $this->requesterId;
    }


    /**
     * @return \SimpleSAML\XMLSchema\Type\NonNegativeIntegerValue|null
     */
    public function getProxyCount(): ?NonNegativeIntegerValue
    {
        return $this->proxyCount;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->getProxyCount())
            && empty($this->getIDPList())
            && empty($this->getRequesterId());
    }


    /**
     * Convert XML into a Scoping-element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Scoping', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Scoping::NS, InvalidDOMElementException::class);

        $idpList = IDPList::getChildrenOfClass($xml);
        $requesterId = RequesterID::getChildrenOfClass($xml);

        return new static(
            self::getOptionalAttribute($xml, 'ProxyCount', NonNegativeIntegerValue::class, null),
            array_pop($idpList),
            $requesterId,
        );
    }


    /**
     * Convert this Scoping to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Scoping to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getProxyCount() !== null) {
            $e->setAttribute('ProxyCount', strval($this->getProxyCount()));
        }

        $this->getIDPList()?->toXML($e);

        foreach ($this->getRequesterId() as $rid) {
            $rid->toXML($e);
        }

        return $e;
    }
}
