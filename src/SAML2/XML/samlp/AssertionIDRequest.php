<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function preg_replace;
use function version_compare;

/**
 * @package simplesamlphp/saml2
 */
final class AssertionIDRequest extends AbstractRequest
{
    /**
     * Initialize an AssertionIDRequest.
     *
     * @param \SimpleSAML\SAML2\XML\saml\AssertionIDRef[] $assertionIDRef
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param \DateTimeImmutable|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     */
    public function __construct(
        protected array $assertionIDRef,
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?DateTimeImmutable $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::maxCount($assertionIDRef, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($assertionIDRef, AssertionIDRef::class, InvalidDOMElementException::class);

        parent::__construct(
            $issuer,
            $id,
            $version,
            $issueInstant,
            $destination,
            $consent,
            $extensions,
        );
    }



    /**
     * @return \SimpleSAML\SAML2\XML\saml\AssertionIDRef[]
     */
    public function getAssertionIDRef(): array
    {
        return $this->assertionIDRef;
    }


    /**
     * Convert XML into a AssertionIDRequest element
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AssertionIDRequest', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AssertionIDRequest::NS, InvalidDOMElementException::class);

        $assertionIDRef = AssertionIDRef::getChildrenOfClass($xml);
        Assert::minCount(
            $assertionIDRef,
            1,
            'At least one <samlp:AssertionIDRef> element is required.',
            TooManyElementsException::class,
        );

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::maxCount($issuer, 1, 'Only one <saml:Issuer> element is allowed.', TooManyElementsException::class);

        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version, '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version, '>='), RequestVersionTooHighException::class);

        $id = self::getAttribute($xml, 'ID');
        Assert::validNCName($id); // Covers the empty string

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = new DateTimeImmutable($issueInstant);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one <samlp:Extensions> element is allowed.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount(
            $signature,
            1,
            'Only one <ds:Signature> element is allowed.',
            TooManyElementsException::class,
        );

        $request = new static(
            $assertionIDRef,
            array_pop($issuer),
            $id,
            $version,
            $issueInstant,
            self::getOptionalAttribute($xml, 'Destination', null),
            self::getOptionalAttribute($xml, 'Consent', null),
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
     * Convert this AssertionIDRequest element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AssertionIDRequest element to.
     * @return \DOMElement
     */
    public function toUnsignedXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        foreach ($this->getAssertionIDRef() as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
