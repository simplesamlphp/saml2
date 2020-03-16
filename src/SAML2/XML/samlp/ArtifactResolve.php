<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SAML2\XML\saml\Issuer;
use Webmozart\Assert\Assert;

/**
 * The Artifact is part of the SAML 2.0 IdP code, and it builds an artifact object.
 * I am using strings, because I find them easier to work with.
 * I want to use this, to be consistent with the other saml2_requests
 *
 * @author Danny Bollaert, UGent AS. <danny.bollaert@ugent.be>
 * @package SimpleSAMLphp
 */
class ArtifactResolve extends AbstractRequest
{
    /** @var string */
    protected $artifact;


    /**
     * Initialize an ArtifactResolve.
     *
     * @param string $artifact
     * @param \SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string|null $version
     * @param int|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \Exception
     */
    public function __construct(
        string $artifact,
        ?Issuer $issuer = null,
        ?string $id = null,
        ?string $version = null,
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null
    ) {
        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);

        $this->setArtifact($artifact);
    }


    /**
     * Retrieve the Artifact in this response.
     *
     * @return string artifact.
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getArtifact(): string
    {
        Assert::notEmpty($this->artifact, 'Artifact not set.');

        return $this->artifact;
    }


    /**
     * Set the artifact that should be included in this response.
     *
     * @param string $artifact
     * @return void
     */
    public function setArtifact(string $artifact): void
    {
        $this->artifact = $artifact;
    }


    /**
     * Create a class from XML
     *
     * @param \DOMElement $xml
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ArtifactResolve');
        Assert::same($xml->namespaceURI, ArtifactResolve::NS);

        $id = self::getAttribute($xml, 'ID');
        $version = self::getAttribute($xml, 'Version');
        $issueInstant = Utils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.');

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.');

        $results = Utils::xpQuery($xml, './saml_protocol:Artifact');
        $artifact = $results[0]->textContent;

        $resolve = new self(
            $artifact,
            empty($issuer) ? null : array_pop($issuer),
            $id,
            $version,
            $issueInstant,
            $destination,
            $consent,
            empty($extensions) ? null : array_pop($extensions)
        );

        if (!empty($signature)) {
            $resolve->setSignature($signature[0]);
        }

        return $resolve;
    }


    /**
     * Convert the response message to an XML element.
     *
     * @return \DOMElement This response.
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        Assert::notEmpty($this->artifact, 'Cannot convert ArtifactResolve to XML without an Artifact set.');

        $e = parent::toXML($parent);
        $artifactelement = $e->ownerDocument->createElementNS(Constants::NS_SAMLP, 'Artifact', $this->artifact);
        $e->appendChild($artifactelement);

        return $this->signElement($e);
    }
}
