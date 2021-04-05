<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\IdentifierTrait;
use SimpleSAML\SAML2\XML\saml\IdentifierInterface;
use SimpleSAML\SAML2\XML\saml\BaseID;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

/**
 * Class for SAML 2 logout request messages.
 *
 * @package simplesamlphp/saml2
 */
class LogoutRequest extends AbstractRequest
{
    use IdentifierTrait;

    /**
     * The expiration time of this request.
     *
     * @var int|null
     */
    protected ?int $notOnOrAfter = null;

    /**
     * The SessionIndexes of the sessions that should be terminated.
     *
     * @var string[]
     */
    protected array $sessionIndexes = [];

    /**
     * The optional reason for the logout, typically a URN
     * See \SimpleSAML\SAML2\Constants::LOGOUT_REASON_*
     * From the standard section 3.7.3: "other values MAY be agreed on between participants"
     *
     * @var string|null
     */
    protected ?string $reason = null;


    /**
     * Constructor for SAML 2 AttributeQuery.
     *
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface $identifier
     * @param int|null $notOnOrAfter
     * @param string|null $reason
     * @param string[] $sessionIndexes
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param int|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     * @throws \Exception
     */
    public function __construct(
        IdentifierInterface $identifier,
        ?int $notOnOrAfter = null,
        ?string $reason = null,
        array $sessionIndexes = [],
        ?Issuer $issuer = null,
        ?string $id = null,
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null
    ) {
        parent::__construct($issuer, $id, $issueInstant, $destination, $consent, $extensions);

        $this->setIdentifier($identifier);
        $this->setNotOnOrAfter($notOnOrAfter);
        $this->setReason($reason);
        $this->setSessionIndexes($sessionIndexes);
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
     * Set the expiration time of this request.
     *
     * @param int|null $notOnOrAfter The expiration time of this request.
     */
    public function setNotOnOrAfter(?int $notOnOrAfter = null): void
    {
        $this->notOnOrAfter = $notOnOrAfter;
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
     * Set the reason for this request.
     *
     * @param string|null $reason The optional reason for this request in URN format
     */
    public function setReason(?string $reason = null): void
    {
        $this->reason = $reason;
    }


    /**
     * Retrieve the SessionIndexes of the sessions that should be terminated.
     *
     * @return string[] The SessionIndexes, or an empty array if all sessions should be terminated.
     */
    public function getSessionIndexes(): array
    {
        return $this->sessionIndexes;
    }


    /**
     * Set the SessionIndexes of the sessions that should be terminated.
     *
     * @param string[] $sessionIndexes The SessionIndexes, or an empty array if all sessions should be terminated.
     */
    public function setSessionIndexes(array $sessionIndexes): void
    {
        Assert::allStringNotEmpty($sessionIndexes);
        $this->sessionIndexes = $sessionIndexes;
    }


    /**
     * Convert XML into a LogoutRequest
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\samlp\LogoutRequest
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'LogoutRequest', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, LogoutRequest::NS, InvalidDOMElementException::class);
        Assert::same('2.0', self::getAttribute($xml, 'Version'));

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        Assert::same(
            substr($issueInstant, -1),
            'Z',
            "Time values MUST be expressed in the UTC timezone using the 'Z' timezone identifier.",
            ProtocolViolationException::class
        );
        $issueInstant = XMLUtils::xsDateTimeToTimestamp($issueInstant);

        $notOnOrAfter = self::getAttribute($xml, 'NotOnOrAfter', null);
        if ($notOnOrAfter !== null) {
            Assert::same(
                substr($notOnOrAfter, -1),
                'Z',
                "Time values MUST be expressed in the UTC timezone using the 'Z' timezone identifier.",
                ProtocolViolationException::class
            );
            $notOnOrAfter = XMLUtils::xsDateTimeToTimestamp($notOnOrAfter);
        }

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.', TooManyElementsException::class);

        $identifier = self::getIdentifierFromXML($xml);
        Assert::notNull(
            $identifier,
            'Missing <saml:NameID>, <saml:BaseID> or <saml:EncryptedID> in <samlp:LogoutRequest>.',
            MissingElementException::class
        );
        Assert::isInstanceOfAny($identifier, [BaseID::class, NameID::class, EncryptedID::class]);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.');

        $request = new self(
            $identifier,
            $notOnOrAfter,
            self::getAttribute($xml, 'Reason', null),
            XMLUtils::extractStrings($xml, AbstractSamlpElement::NS, 'SessionIndex'),
            array_pop($issuer),
            self::getAttribute($xml, 'ID'),
            $issueInstant,
            self::getAttribute($xml, 'Destination', null),
            self::getAttribute($xml, 'Consent', null),
            array_pop($extensions)
        );

        if (!empty($signature)) {
            $request->setSignature($signature[0]);
            $request->messageContainedSignatureUponConstruction = true;
        }

        return $request;
    }


    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = parent::toXML($parent);

        if ($this->notOnOrAfter !== null) {
            $e->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->notOnOrAfter));
        }

        if ($this->reason !== null) {
            $e->setAttribute('Reason', $this->reason);
        }

        /** @var \SimpleSAML\SAML2\XML\saml\IdentifierInterface $this->identifier */
        $this->identifier->toXML($e);

        foreach ($this->sessionIndexes as $sessionIndex) {
            $e->appendChild(
                $e->ownerDocument->createElementNS(AbstractSamlpElement::NS, 'samlp:SessionIndex', $sessionIndex)
            );
        }

        return $this->signElement($e);
    }
}
