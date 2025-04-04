<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_merge;
use function array_pop;

/**
 * Class for SAML 2 Response messages.
 *
 * @package simplesamlphp/saml2
 */
class Response extends AbstractStatusResponse implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;

    /**
     * Constructor for SAML 2 response messages.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status
     * @param \DateTimeImmutable $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param string $inResponseTo
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     * @param (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\EncryptedAssertion)[] $assertions
     */
    final public function __construct(
        Status $status,
        DateTimeImmutable $issueInstant,
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?string $inResponseTo = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
        protected array $assertions = [],
    ) {
        Assert::maxCount($assertions, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOfAny($assertions, [Assertion::class, EncryptedAssertion::class]);

        parent::__construct(
            $status,
            $issueInstant,
            $issuer,
            $id,
            $version,
            $inResponseTo,
            $destination,
            $consent,
            $extensions,
        );
    }


    /**
     * Retrieve the assertions in this response.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Assertion[]|\SimpleSAML\SAML2\XML\saml\EncryptedAssertion[]
     */
    public function getAssertions(): array
    {
        return $this->assertions;
    }


    /**
     * Convert XML into a Response element.
     *
     * @param \DOMElement $xml The input message.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Response', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Response::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version, '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version, '>='), RequestVersionTooHighException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $id = self::getAttribute($xml, 'ID');
        Assert::validNCName($id); // Covers the empty string

        $inResponseTo = self::getOptionalAttribute($xml, 'InResponseTo', null);
        $destination = self::getOptionalAttribute($xml, 'Destination', null);
        $consent = self::getOptionalAttribute($xml, 'Consent', null);

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTime($issueInstant, ProtocolViolationException::class);
        $issueInstant = new DateTimeImmutable($issueInstant);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $status = Status::getChildrenOfClass($xml);
        Assert::minCount($status, 1, MissingElementException::class);
        Assert::maxCount($status, 1, TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one saml:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $response = new static(
            array_pop($status),
            $issueInstant,
            empty($issuer) ? null : array_pop($issuer),
            $id,
            $version,
            $inResponseTo,
            $destination,
            $consent,
            empty($extensions) ? null : array_pop($extensions),
            array_merge(Assertion::getChildrenOfClass($xml), EncryptedAssertion::getChildrenOfClass($xml)),
        );

        if (!empty($signature)) {
            $response->setSignature($signature[0]);
            $response->messageContainedSignatureUponConstruction = true;
            $response->setXML($xml);
        }

        return $response;
    }


    /**
     * Convert the response message to an XML element.
     *
     * @return \DOMElement This response.
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        foreach ($this->getAssertions() as $assertion) {
            $assertion->toXML($e);
        }

        return $e;
    }
}
