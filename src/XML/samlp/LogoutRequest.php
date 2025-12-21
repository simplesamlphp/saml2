<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\IdentifierTrait;
use SimpleSAML\SAML2\XML\saml\AbstractBaseID;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\IdentifierInterface;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;

/**
 * Class for SAML 2 logout request messages.
 *
 * @package simplesamlphp/saml2
 */
final class LogoutRequest extends AbstractRequest implements SchemaValidatableElementInterface
{
    use IdentifierTrait;
    use SchemaValidatableElementTrait;


    /**
     * Constructor for SAML 2 AttributeQuery.
     *
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface $identifier
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $notOnOrAfter
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $reason
     * @param \SimpleSAML\SAML2\XML\samlp\SessionIndex[] $sessionIndexes
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \Exception
     */
    public function __construct(
        IDValue $id,
        IdentifierInterface $identifier,
        SAMLDateTimeValue $issueInstant,
        protected ?SAMLDateTimeValue $notOnOrAfter = null,
        protected ?SAMLStringValue $reason = null,
        protected array $sessionIndexes = [],
        ?Issuer $issuer = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::maxCount($sessionIndexes, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($sessionIndexes, SessionIndex::class);

        parent::__construct($id, $issuer, $issueInstant, $destination, $consent, $extensions);

        $this->setIdentifier($identifier);
    }


    /**
     * Retrieve the expiration time of this request.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null The expiration time of this request.
     */
    public function getNotOnOrAfter(): ?SAMLDateTimeValue
    {
        return $this->notOnOrAfter;
    }


    /**
     * Retrieve the reason for this request.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null The reason for this request.
     */
    public function getReason(): ?SAMLStringValue
    {
        return $this->reason;
    }


    /**
     * Retrieve the SessionIndexes of the sessions that should be terminated.
     *
     * @return \SimpleSAML\SAML2\XML\samlp\SessionIndex[]
     *   The SessionIndexes, or an empty array if all sessions should be terminated.
     */
    public function getSessionIndexes(): array
    {
        return $this->sessionIndexes;
    }


    /**
     * Convert XML into a LogoutRequest
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'LogoutRequest', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, LogoutRequest::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version', SAMLStringValue::class);
        Assert::true(version_compare('2.0', strval($version), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', strval($version), '>='), RequestVersionTooHighException::class);

        $id = self::getAttribute($xml, 'ID', IDValue::Class);
        $issueInstant = self::getAttribute($xml, 'IssueInstant', SAMLDateTimeValue::class);

        $notOnOrAfter = self::getOptionalAttribute($xml, 'NotOnOrAfter', SAMLDateTimeValue::class, null);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one saml:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $identifier = self::getIdentifierFromXML($xml);
        Assert::notNull(
            $identifier,
            'Missing <saml:NameID>, <saml:BaseID> or <saml:EncryptedID> in <samlp:LogoutRequest>.',
            MissingElementException::class,
        );
        Assert::isInstanceOfAny($identifier, [AbstractBaseID::class, NameID::class, EncryptedID::class]);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.');

        $sessionIndex = SessionIndex::getChildrenOfClass($xml);

        $request = new static(
            $id,
            $identifier,
            $issueInstant,
            $notOnOrAfter,
            self::getOptionalAttribute($xml, 'Reason', SAMLStringValue::class, null),
            $sessionIndex,
            array_pop($issuer),
            self::getOptionalAttribute($xml, 'Destination', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'Consent', SAMLAnyURIValue::class, null),
            array_pop($extensions),
        );

        if (!empty($signature)) {
            $request->setSignature($signature[0]);
            $request->messageContainedSignatureUponConstruction = true;
            $request->setXML($xml);
        }

        return $request;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        if ($this->getNotOnOrAfter() !== null) {
            $e->setAttribute('NotOnOrAfter', $this->getNotOnOrAfter()->getValue());
        }

        if ($this->getReason() !== null) {
            $e->setAttribute('Reason', $this->getReason()->getValue());
        }

        /** @var \SimpleSAML\XML\SerializableElementInterface&\SimpleSAML\SAML2\XML\saml\IdentifierInterface $identifier */
        $identifier = $this->getIdentifier();
        $identifier->toXML($e);

        foreach ($this->getSessionIndexes() as $sessionIndex) {
            $sessionIndex->toXML($e);
        }

        return $e;
    }
}
