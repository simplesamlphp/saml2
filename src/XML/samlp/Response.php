<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\Protocol\{RequestVersionTooHighException, RequestVersionTooLowException};
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\{Assertion, EncryptedAssertion, Issuer};
use SimpleSAML\XML\Exception\{InvalidDOMElementException, MissingElementException, TooManyElementsException};
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\{IDValue, NCNameValue};
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_merge;
use function array_pop;
use function strval;

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
     * @param \SimpleSAML\SAML2\IDValue $id
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param \SimpleSAML\XML\Type\NCNameValue|null $inResponseTo
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     * @param (\SimpleSAML\SAML2\XML\saml\Assertion|\SimpleSAML\SAML2\XML\saml\EncryptedAssertion)[] $assertions
     */
    final public function __construct(
        IDValue $id,
        Status $status,
        SAMLDateTimeValue $issueInstant,
        ?Issuer $issuer = null,
        ?NCNameValue $inResponseTo = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
        protected array $assertions = [],
    ) {
        Assert::maxCount($assertions, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOfAny($assertions, [Assertion::class, EncryptedAssertion::class]);

        parent::__construct(
            $id,
            $status,
            $issueInstant,
            $issuer,
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

        $version = self::getAttribute($xml, 'Version', SAMLStringValue::class);
        Assert::true(version_compare('2.0', strval($version), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', strval($version), '>='), RequestVersionTooHighException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

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
            self::getAttribute($xml, 'ID', IDValue::class),
            array_pop($status),
            self::getAttribute($xml, 'IssueInstant', SAMLDateTimeValue::class),
            empty($issuer) ? null : array_pop($issuer),
            self::getOptionalAttribute($xml, 'InResponseTo', NCNameValue::class, null),
            self::getOptionalAttribute($xml, 'Destination', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'Consent', SAMLAnyURIValue::class, null),
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
