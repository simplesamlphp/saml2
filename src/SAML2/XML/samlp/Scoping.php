<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Utils;

/**
 * Class for handling SAML2 Scoping.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class Scoping extends AbstractSamlpElement
{
    /** @var \SimpleSAML\SAML2\XML\samlp\IDPList|null */
    protected $IDPList;

    /** @var string[] */
    protected $requesterId;

    /** @var int|null */
    protected $proxyCount;


    /**
     * Initialize a Scoping element.
     *
     * @param int|null $proxyCount
     * @param \SimpleSAML\SAML2\XML\samlp\IDPList|null $idpList
     * @param string[] $requesterId
     */
    public function __construct(?int $proxyCount = null, ?IDPList $idpList = null, array $requesterId = [])
    {
        $this->setProxyCount($proxyCount);
        $this->setIDPList($idpList);
        $this->setRequesterId($requesterId);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\IDPList|null
     */
    public function getIDPList(): ?IDPList
    {
        return $this->IDPList;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\IDPList|null $idpList
     * @return void
     */
    private function setIDPList(?IDPList $idpList): void
    {
        $this->IDPList = $idpList;
    }


    /**
     * @return string[]
     */
    public function getRequesterId(): array
    {
        return $this->requesterId;
    }


    /**
     * @param string[] $requesterId
     * @return void
     */
    private function setRequesterId(array $requesterId): void
    {
        Assert::allStringNotEmpty($requesterId);

        $this->requesterId = $requesterId;
    }


    /**
     * @return int|null
     */
    public function getProxyCount(): ?int
    {
        return $this->proxyCount;
    }


    /**
     * @param int|null $proxyCount
     * @return void
     */
    private function setProxyCount(?int $proxyCount): void
    {
        $this->proxyCount = $proxyCount;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return (
            empty($this->proxyCount)
            && empty($this->IDPList)
            && empty($this->requesterId)
        );
    }


    /**
     * Convert XML into a Scoping-element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\samlp\Scoping
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Scoping', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Scoping::NS, InvalidDOMElementException::class);

        $proxyCount = self::getAttribute($xml, 'ProxyCount', null);
        $idpList = IDPList::getChildrenOfClass($xml);
        $requesterId = Utils::extractStrings($xml, AbstractSamlpElement::NS, 'RequesterID');

        return new self(
            is_null($proxyCount) ? null : intval($proxyCount),
            array_pop($idpList),
            $requesterId
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

        if ($this->proxyCount !== null) {
            $e->setAttribute('ProxyCount', strval($this->proxyCount));
        }

        if ($this->IDPList !== null) {
            $this->IDPList->toXML($e);
        }

        if (!empty($this->requesterId)) {
            Utils::addStrings($e, AbstractSamlpElement::NS, 'samlp:RequesterID', false, $this->requesterId);
        }

        return $e;
    }
}
