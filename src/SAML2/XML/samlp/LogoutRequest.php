<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\IdentifierTrait;
use SimpleSAML\SAML2\XML\saml\IdentifierInterface;
use SimpleSAML\SAML2\XML\saml\AbstractBaseID;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function gmdate;

/**
 * Class for SAML 2 logout request messages.
 *
 * @package simplesamlphp/saml2
 */
final class LogoutRequest extends AbstractRequest
{
    use IdentifierTrait;


    /**
     * Constructor for SAML 2 AttributeQuery.
     *
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface $identifier
     * @param \DateTimeImmutable $issueInstant
     * @param \DateTimeImmutable|null $notOnOrAfter
     * @param string|null $reason
     * @param \SimpleSAML\SAML2\XML\samlp\SessionIndex[] $sessionIndexes
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     * @throws \Exception
     */
    public function __construct(
        IdentifierInterface $identifier,
        DateTimeImmutable $issueInstant,
        protected ?DateTimeImmutable $notOnOrAfter = null,
        protected ?string $reason = null,
        protected array $sessionIndexes = [],
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::allIsInstanceOf($sessionIndexes, SessionIndex::class);

        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);

        $this->setIdentifier($identifier);
    }


    /**
     * Retrieve the expiration time of this request.
     *
     * @return \DateTimeImmutable|null The expiration time of this request.
     */
    public function getNotOnOrAfter(): ?DateTimeImmutable
    {
        return $this->notOnOrAfter;
    }


    /**
     * Retrieve the reason for this request.
     *
     * @return string|null The reason for this request.
     */
    public function getReason(): ?string
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
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'LogoutRequest', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, LogoutRequest::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version, '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version, '>='), RequestVersionTooHighException::class);

        $id = self::getAttribute($xml, 'ID');
        Assert::validNCName($id); // Covers the empty string

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = new DateTimeImmutable($issueInstant);

        $notOnOrAfter = self::getOptionalAttribute($xml, 'NotOnOrAfter', null);
        if ($notOnOrAfter !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $notOnOrAfter = preg_replace('/([.][0-9]+Z)$/', 'Z', $notOnOrAfter, 1);

            Assert::validDateTimeZulu($notOnOrAfter, ProtocolViolationException::class);
            $notOnOrAfter = new DateTimeImmutable($notOnOrAfter);
        }

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
            $identifier,
            $issueInstant,
            $notOnOrAfter,
            self::getOptionalAttribute($xml, 'Reason', null),
            $sessionIndex,
            array_pop($issuer),
            $id,
            $version,
            self::getOptionalAttribute($xml, 'Destination', null),
            self::getOptionalAttribute($xml, 'Consent', null),
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
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = parent::toUnsignedXML($parent);

        if ($this->getNotOnOrAfter() !== null) {
            $e->setAttribute('NotOnOrAfter', $this->getNotOnOrAfter()->format(C::DATETIME_FORMAT));
        }

        if ($this->getReason() !== null) {
            $e->setAttribute('Reason', $this->getReason());
        }

        /** @psalm-var \SimpleSAML\XML\SerializableElementInterface $identifier */
        $identifier = $this->getIdentifier();
        $identifier->toXML($e);

        foreach ($this->getSessionIndexes() as $sessionIndex) {
            $sessionIndex->toXML($e);
        }

        return $e;
    }
}
