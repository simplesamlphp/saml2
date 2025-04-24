<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function preg_replace;

/**
 * The Artifact is part of the SAML 2.0 IdP code, and it builds an artifact object.
 * I am using strings, because I find them easier to work with.
 * I want to use this, to be consistent with the other saml2_requests
 *
 * @package simplesamlphp/saml2
 */
class ArtifactResolve extends AbstractRequest implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;

    /**
     * Initialize an ArtifactResolve.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Artifact $artifact
     * @param \DateTimeImmutable $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \Exception
     */
    final public function __construct(
        protected Artifact $artifact,
        DateTimeImmutable $issueInstant,
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Retrieve the Artifact in this response.
     */
    public function getArtifact(): Artifact
    {
        return $this->artifact;
    }


    /**
     * Create a class from XML
     *
     * @param \DOMElement $xml
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'ArtifactResolve', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ArtifactResolve::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version, '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version, '>='), RequestVersionTooHighException::class);

        $id = self::getAttribute($xml, 'ID');
        Assert::validNCName($id); // Covers the empty string

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTime($issueInstant, ProtocolViolationException::class);
        $issueInstant = new DateTimeImmutable($issueInstant);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::maxCount($issuer, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one saml:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $artifact = Artifact::getChildrenOfClass($xml);
        Assert::minCount($artifact, 1, 'At least one samlp:Artifact is required.', MissingElementException::class);
        Assert::maxCount($artifact, 1, 'Only one samlp:Artifact is allowed.', TooManyElementsException::class);

        $resolve = new static(
            $artifact[0],
            $issueInstant,
            array_pop($issuer),
            $id,
            $version,
            self::getOptionalAttribute($xml, 'Destination', null),
            self::getOptionalAttribute($xml, 'Consent', null),
            array_pop($extensions),
        );

        if (!empty($signature)) {
            $resolve->setSignature($signature[0]);
            $resolve->setXML($xml);
        }

        return $resolve;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        Assert::notEmpty($this->artifact, 'Cannot convert ArtifactResolve to XML without an Artifact set.');

        $e = parent::toUnsignedXML($parent);
        $this->getArtifact()->toXML($e);

        return $e;
    }
}
