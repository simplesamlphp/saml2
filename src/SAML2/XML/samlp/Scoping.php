<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_pop;
use function is_null;
use function intval;

/**
 * Class for handling SAML2 Scoping.
 *
 * @package simplesamlphp/saml2
 */
final class Scoping extends AbstractSamlpElement
{
    /**
     * Initialize a Scoping element.
     *
     * @param int|null $proxyCount
     * @param \SimpleSAML\SAML2\XML\samlp\IDPList|null $IDPList
     * @param \SimpleSAML\SAML2\XML\samlp\RequesterID[] $requesterId
     */
    public function __construct(
        protected ?int $proxyCount = null,
        protected ?IDPList $IDPList = null,
        protected array $requesterId = [],
    ) {
        Assert::maxCount($requesterId, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($requesterId, RequesterID::class);
        Assert::nullOrNatural($proxyCount);
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
     * @return int|null
     */
    public function getProxyCount(): ?int
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
        return empty($this->proxyCount)
            && empty($this->IDPList)
            && empty($this->requesterId);
    }


    /**
     * Convert XML into a Scoping-element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Scoping', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Scoping::NS, InvalidDOMElementException::class);

        $proxyCount = self::getOptionalIntegerAttribute($xml, 'ProxyCount', null);
        $idpList = IDPList::getChildrenOfClass($xml);
        $requesterId = RequesterID::getChildrenOfClass($xml);

        return new static(
            $proxyCount,
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
    public function toXML(DOMElement $parent = null): DOMElement
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
