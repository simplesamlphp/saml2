<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function preg_replace;
use function version_compare;

/**
 * The \SimpleSAML\SAML2\XML\samlp\ArtifactResponse,
 *  is the response to the \SimpleSAML\SAML2\XML\samlp\ArtifactResolve.
 *
 * @package simplesamlphp/saml2
 */
class ArtifactResponse extends AbstractStatusResponse implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;

    /**
     * Constructor for SAML 2 ArtifactResponse.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status
     * @param \DateTimeImmutable $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param string|null $inResponseTo
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage|null $message
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
        protected ?AbstractMessage $message = null,
    ) {
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
     * Collect the value of the any-property
     *
     * @return \SimpleSAML\SAML2\XML\samlp\AbstractMessage|null
     */
    public function getMessage(): ?AbstractMessage
    {
        return $this->message;
    }


    /**
     * Convert XML into an ArtifactResponse
     *
     * @param \DOMElement $xml
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'ArtifactResponse', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ArtifactResponse::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version, '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version, '>='), RequestVersionTooHighException::class);

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

        // find message; it should come last, after the Status-element
        $status = XPath::xpQuery($xml, './saml_protocol:Status', XPath::getXPath($xml));
        $status = $status[0];
        $message = null;

        /** @psalm-suppress RedundantCondition */
        for ($child = $status->nextSibling; $child !== null; $child = $child->nextSibling) {
            if ($child instanceof DOMElement) {
                $message = MessageFactory::fromXML($child);
                break;
            }
            /* Ignore comments and text nodes. */
        }

        $status = Status::getChildrenOfClass($xml);
        Assert::count($status, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one saml:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount(
            $signature,
            1,
            'Only one ds:Signature element is allowed.',
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
            $message,
        );

        if (!empty($signature)) {
            $response->setSignature($signature[0]);
            $response->setXML($xml);
        }

        return $response;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        $this->getMessage()?->toXML($e);

        return $e;
    }
}
