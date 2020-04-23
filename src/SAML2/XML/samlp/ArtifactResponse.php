<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use DOMNode;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SAML2\XML\saml\Issuer;
use Webmozart\Assert\Assert;

/**
 * The \SAML2\ArtifactResponse, is the response to the \SAML2\ArtifactResolve.
 *
 * @author Danny Bollaert, UGent AS. <danny.bollaert@ugent.be>
 * @package SimpleSAMLphp
 */
class ArtifactResponse extends AbstractStatusResponse
{
    /** @var \DOMElement */
    protected $any;


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
     * @param \DOMElement $any
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
        ?DOMElement $any = null
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

        $this->setAny($any);
    }


    /**
     * Collect the value of the any-property
     *
     * @return \DOMElement|null
     */
    public function getAny(): ?DOMElement
    {
        return $this->any;
    }


    /**
     * Set the value of the any-property
     *
     * @param \DOMElement|null $any
     * @return void
     */
    private function setAny(?DOMElement $any): void
    {
        $this->any = $any;
    }


    /**
     * Convert XML into an ArtifactResponse
     *
     * @param \DOMElement $xml
     * @return self
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ArtifactResponse');
        Assert::same($xml->namespaceURI, ArtifactResponse::NS);
        Assert::same('2.0', self::getAttribute($xml, 'Version'));

        $id = self::getAttribute($xml, 'ID');
        $issueInstant = Utils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));
        $inResponseTo = self::getAttribute($xml, 'InResponseTo', null);
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        // Find children; they should come last, after the Status-element
        $status = Utils::xpQuery($xml, './saml_protocol:Status');
        $status = $status[0];

        /** @psalm-suppress RedundantCondition */
        for ($any = $status->nextSibling; $any instanceof DOMNode; $any = $any->nextSibling) {
            if ($any instanceof DOMElement) {
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
            $any
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

        if ($this->any !== null) {
            $node = $e->ownerDocument->importNode($this->any, true);
            $e->appendChild($node);
        }

        return $this->signElement($e);
    }
}
