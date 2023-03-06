<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\IdentifierTrait;
use SimpleSAML\SAML2\XML\saml\IdentifierInterface;
use SimpleSAML\SAML2\XML\saml\BaseID;
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
     * @param int|null $notOnOrAfter
     * @param string|null $reason
     * @param \SimpleSAML\SAML2\XML\samlp\SessionIndex[] $sessionIndexes
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param int|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     * @throws \Exception
     */
    public function __construct(
        IdentifierInterface $identifier,
        protected ?int $notOnOrAfter = null,
        protected ?string $reason = null,
        protected array $sessionIndexes = [],
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::nullOrValidURI($reason, SchemaViolationException::class);
        Assert::allIsInstanceOf($sessionIndexes, SessionIndex::class);

        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);

        $this->setIdentifier($identifier);
    }


    /**
     * Retrieve the expiration time of this request.
     *
     * @return int|null The expiration time of this request.
     */
    public function getNotOnOrAfter(): ?int
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
        Assert::nullOrValidNCName($id); // Covers the empty string

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = XMLUtils::xsDateTimeToTimestamp($issueInstant);

        $notOnOrAfter = self::getAttribute($xml, 'NotOnOrAfter', null);
        if ($notOnOrAfter !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $notOnOrAfter = preg_replace('/([.][0-9]+Z)$/', 'Z', $notOnOrAfter, 1);

            Assert::validDateTimeZulu($notOnOrAfter, ProtocolViolationException::class);
            $notOnOrAfter = XMLUtils::xsDateTimeToTimestamp($notOnOrAfter);
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
        Assert::isInstanceOfAny($identifier, [BaseID::class, NameID::class, EncryptedID::class]);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.');

        $sessionIndex = SessionIndex::getChildrenOfClass($xml);

        $request = new static(
            $identifier,
            $notOnOrAfter,
            self::getAttribute($xml, 'Reason', null),
            $sessionIndex,
            array_pop($issuer),
            $id,
            $version,
            $issueInstant,
            self::getAttribute($xml, 'Destination', null),
            self::getAttribute($xml, 'Consent', null),
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
            $e->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->getNotOnOrAfter()));
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
