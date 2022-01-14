<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;

/**
 * The Artifact is part of the SAML 2.0 IdP code, and it builds an artifact object.
 * I am using strings, because I find them easier to work with.
 * I want to use this, to be consistent with the other saml2_requests
 *
 * @package simplesamlphp/saml2
 */
class ArtifactResolve extends AbstractRequest
{
    /** @var string */
    protected string $artifact;


    /**
     * Initialize an ArtifactResolve.
     *
     * @param string $artifact
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param int|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \Exception
     */
    public function __construct(
        string $artifact,
        ?Issuer $issuer = null,
        ?string $id = null,
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null
    ) {
        parent::__construct($issuer, $id, $issueInstant, $destination, $consent, $extensions);

        $this->setArtifact($artifact);
    }


    /**
     * Retrieve the Artifact in this response.
     *
     * @return string artifact.
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
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
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ArtifactResolve', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ArtifactResolve::NS, InvalidDOMElementException::class);

        Assert::true(version_compare('2.0', self::getAttribute($xml, 'Version'), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', self::getAttribute($xml, 'Version'), '>='), RequestVersionTooHighException::class);

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = XMLUtils::xsDateTimeToTimestamp($issueInstant);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::maxCount($issuer, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $results = XMLUtils::xpQuery($xml, './saml_protocol:Artifact');
        $artifact = $results[0]->textContent;

        $resolve = new self(
            $artifact,
            array_pop($issuer),
            self::getAttribute($xml, 'ID'),
            $issueInstant,
            self::getAttribute($xml, 'Destination', null),
            self::getAttribute($xml, 'Consent', null),
            array_pop($extensions)
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
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        Assert::notEmpty($this->artifact, 'Cannot convert ArtifactResolve to XML without an Artifact set.');

        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = parent::toXML($parent);
        $artifactelement = $e->ownerDocument->createElementNS(Constants::NS_SAMLP, 'Artifact', $this->artifact);
        $e->appendChild($artifactelement);

        return $this->signElement($e);
    }
}
