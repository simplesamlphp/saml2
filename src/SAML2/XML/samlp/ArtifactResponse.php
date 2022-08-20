<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;

/**
 * The \SimpleSAML\SAML2\XML\samlp\ArtifactResponse,
 *  is the response to the \SimpleSAML\SAML2\XML\samlp\ArtifactResolve.
 *
 * @package simplesamlphp/saml2
 */
class ArtifactResponse extends AbstractStatusResponse
{
    /** @var \SimpleSAML\SAML2\XML\samlp\AbstractMessage|null */
    protected ?AbstractMessage $message;


    /**
     * Constructor for SAML 2 ArtifactResponse.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param int|null $issueInstant
     * @param string|null $inResponseTo
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage|null $message
     */
    public function __construct(
        Status $status,
        ?Issuer $issuer = null,
        ?string $id = null,
        ?int $issueInstant = null,
        ?string $inResponseTo = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
        ?AbstractMessage $message = null
    ) {
        parent::__construct(
            $status,
            $issuer,
            $id,
            $issueInstant,
            $inResponseTo,
            $destination,
            $consent,
            $extensions
        );

        $this->setMessage($message);
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
     * Set the value of the any-property
     *
     * @param \SimpleSAML\SAML2\XML\samlp\AbstractMessage|null $message
     */
    private function setMessage(?AbstractMessage $message): void
    {
        $this->message = $message;
    }


    /**
     * Convert XML into an ArtifactResponse
     *
     * @param \DOMElement $xml
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ArtifactResponse', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ArtifactResponse::NS, InvalidDOMElementException::class);

        Assert::true(version_compare('2.0', self::getAttribute($xml, 'Version'), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', self::getAttribute($xml, 'Version'), '>='), RequestVersionTooHighException::class);

        $id = self::getAttribute($xml, 'ID');
        $inResponseTo = self::getAttribute($xml, 'InResponseTo', null);
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = XMLUtils::xsDateTimeToTimestamp($issueInstant);

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
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $response = new self(
            array_pop($status),
            empty($issuer) ? null : array_pop($issuer),
            $id,
            $issueInstant,
            $inResponseTo,
            $destination,
            $consent,
            empty($extensions) ? null : array_pop($extensions),
            $message
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

        if ($this->message !== null) {
            $this->message->toXML($e);
        }

        return $e;
    }
}
