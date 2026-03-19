<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SOAP11\Constants as C;
use SimpleSAML\SOAP11\Type\MustUnderstandValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\BooleanValue;

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
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $providerName
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $isPassive
     */
    public function __construct(
        protected Issuer $issuer,
        protected ?IDPList $idpList = null,
        protected ?SAMLStringValue $providerName = null,
        protected ?BooleanValue $isPassive = null,
    ) {
    }


    /**
     * Collect the value of the isPassive-property
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function getIsPassive(): ?BooleanValue
    {
        return $this->isPassive;
    }


    /**
     * Collect the value of the providerName-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getProviderName(): ?SAMLStringValue
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
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Request', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Request::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV, 'actor'),
            'Missing env:actor attribute in <ecp:Request>.',
            MissingAttributeException::class,
        );
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV, 'mustUnderstand'),
            'Missing env:mustUnderstand attribute in <ecp:Request>.',
            MissingAttributeException::class,
        );

        MustUnderstandValue::fromString($xml->getAttributeNS(C::NS_SOAP_ENV, 'mustUnderstand'));

        Assert::same(
            $xml->getAttributeNS(C::NS_SOAP_ENV, 'actor'),
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
            self::getOptionalAttribute($xml, 'ProviderName', SAMLStringValue::class, null),
            self::getOptionalAttribute($xml, 'IsPassive', BooleanValue::class, null),
        );
    }


    /**
     * Convert this ECP SubjectConfirmation to XML.
     *
     * @param \DOMElement|null $parent The element we should append this element to.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(C::NS_SOAP_ENV, 'SOAP-ENV:mustUnderstand', '1');
        $e->setAttributeNS(C::NS_SOAP_ENV, 'SOAP-ENV:actor', C::SOAP_ACTOR_NEXT);

        if ($this->getProviderName() !== null) {
            $e->setAttribute('ProviderName', $this->getProviderName()->getValue());
        }

        if ($this->getIsPassive() !== null) {
            $e->setAttribute('IsPassive', strval(intval($this->getIsPassive()->toBoolean())));
        }

        $this->getIssuer()->toXML($e);
        $this->getIDPList()?->toXML($e);

        return $e;
    }
}
