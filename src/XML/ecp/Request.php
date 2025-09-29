<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

use function intval;
use function strval;

/**
 * Class representing the ECP Request element.
 *
 * @package simplesamlphp/saml2
 */
final class Request extends AbstractEcpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Create a ECP Request element.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param \SimpleSAML\SAML2\XML\samlp\IDPList|null $idpList
     * @param string|null $providerName
     * @param bool|null $isPassive
     */
    public function __construct(
        protected Issuer $issuer,
        protected ?IDPList $idpList = null,
        protected ?string $providerName = null,
        protected ?bool $isPassive = null,
    ) {
    }


    /**
     * Collect the value of the isPassive-property
     *
     * @return bool|null
     */
    public function getIsPassive(): ?bool
    {
        return $this->isPassive;
    }


    /**
     * Collect the value of the providerName-property
     *
     * @return string|null
     */
    public function getProviderName(): ?string
    {
        return $this->providerName;
    }


    /**
     * Collect the value of the issuer-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\Issuer
     */
    public function getIssuer(): Issuer
    {
        return $this->issuer;
    }


    /**
     * Collect the value of the idpList-property
     *
     * @return \SimpleSAML\SAML2\XML\samlp\IDPList|null
     */
    public function getIDPList(): ?IDPList
    {
        return $this->idpList;
    }


    /**
     * Convert XML into a Request
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Request', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Request::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'actor'),
            'Missing env:actor attribute in <ecp:Request>.',
            MissingAttributeException::class,
        );
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand'),
            'Missing env:mustUnderstand attribute in <ecp:Request>.',
            MissingAttributeException::class,
        );

        $mustUnderstand = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand');
        Assert::same(
            $mustUnderstand,
            '1',
            'Invalid value of env:mustUnderstand attribute in <ecp:Request>.',
            ProtocolViolationException::class,
        );

        $actor = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'actor');
        Assert::same(
            $actor,
            C::SOAP_ACTOR_NEXT,
            'Invalid value of env:actor attribute in <ecp:Request>.',
            ProtocolViolationException::class,
        );

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::count(
            $issuer,
            1,
            'More than one <saml:Issuer> in <ecp:Request>.',
            TooManyElementsException::class,
        );

        $idpList = IDPList::getChildrenOfClass($xml);

        return new static(
            array_pop($issuer),
            array_pop($idpList),
            self::getOptionalAttribute($xml, 'ProviderName', null),
            self::getOptionalBooleanAttribute($xml, 'IsPassive', null),
        );
    }


    /**
     * Convert this ECP SubjectConfirmation to XML.
     *
     * @param \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:mustUnderstand', '1');
        $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:actor', C::SOAP_ACTOR_NEXT);

        if ($this->getProviderName() !== null) {
            $e->setAttribute('ProviderName', $this->getProviderName());
        }

        if ($this->getIsPassive() !== null) {
            $e->setAttribute('IsPassive', strval(intval($this->getIsPassive())));
        }

        $this->getIssuer()->toXML($e);
        $this->getIDPList()?->toXML($e);

        return $e;
    }
}
