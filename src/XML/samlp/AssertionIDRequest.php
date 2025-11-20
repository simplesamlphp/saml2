<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function version_compare;

/**
 * @package simplesamlphp/saml2
 */
final class AssertionIDRequest extends AbstractRequest implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize an AssertionIDRequest.
     *
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\saml\AssertionIDRef[] $assertionIDRef
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $issueInstant
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     */
    public function __construct(
        IDValue $id,
        protected array $assertionIDRef,
        ?Issuer $issuer = null,
        ?SAMLDateTimeValue $issueInstant = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::maxCount($assertionIDRef, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($assertionIDRef, AssertionIDRef::class, InvalidDOMElementException::class);

        parent::__construct(
            $id,
            $issuer,
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
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

        $version = self::getAttribute($xml, 'Version', SAMLStringValue::class);
        Assert::true(version_compare('2.0', strval($version), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', strval($version), '>='), RequestVersionTooHighException::class);

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
            self::getAttribute($xml, 'ID', IDValue::class),
            $assertionIDRef,
            array_pop($issuer),
            self::getAttribute($xml, 'IssueInstant', SAMLDateTimeValue::class),
            self::getOptionalAttribute($xml, 'Destination', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'Consent', SAMLAnyURIValue::class, null),
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
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        foreach ($this->getAssertionIDRef() as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
