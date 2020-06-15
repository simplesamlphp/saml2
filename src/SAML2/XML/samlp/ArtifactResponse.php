<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SAML2\XML\saml\Issuer;
use SimpleSAML\Assert\Assert;

/**
 * The \SAML2\ArtifactResponse, is the response to the \SAML2\ArtifactResolve.
 *
 * @author Danny Bollaert, UGent AS. <danny.bollaert@ugent.be>
 * @package SimpleSAMLphp
 */
class ArtifactResponse extends AbstractStatusResponse
{
    /** @var \SAML2\XML\samlp\AbstractMessage */
    protected $message;


    /**
     * Constructor for SAML 2 ArtifactResponse.
     *
     * @param \SAML2\XML\samlp\Status $status
     * @param \SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param int|null $issueInstant
     * @param string|null $inResponseTo
     * @param string|null $destination
     * @param string|null $consent
     * @param \SAML2\XML\samlp\Extensions|null $extensions
     * @param \SAML2\XML\samlp\AbstractMessage|null $message
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
     * @return \SAML2\XML\samlp\AbstractMessage|null
     */
    public function getMessage(): ?AbstractMessage
    {
        return $this->message;
    }


    /**
     * Set the value of the any-property
     *
     * @param \SAML2\XML\samlp\AbstractMessage|null $message
     * @return void
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
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ArtifactResponse', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ArtifactResponse::NS, InvalidDOMElementException::class);
        Assert::same('2.0', self::getAttribute($xml, 'Version'));

        $id = self::getAttribute($xml, 'ID');
        $issueInstant = Utils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));
        $inResponseTo = self::getAttribute($xml, 'InResponseTo', null);
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        // find message; it should come last, after the Status-element
        $status = Utils::xpQuery($xml, './saml_protocol:Status');
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
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.');

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.');

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
        }

        return $response;
    }


    /**
     * Convert the ArtifactResponse message to an XML element.
     *
     * @return \DOMElement This response.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        if ($this->message !== null) {
            $this->message->toXML($e);
        }

        return $this->signElement($e);
    }
}
