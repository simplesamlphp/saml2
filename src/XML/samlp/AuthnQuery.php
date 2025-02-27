<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\{RequestVersionTooHighException, RequestVersionTooLowException};
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\{Issuer, Subject};
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\XML\Exception\{InvalidDOMElementException, MissingElementException, TooManyElementsException};
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\IDValue;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function version_compare;

/**
 * Class for SAML 2 AuthnQuery query messages.
 *
 * @package simplesamlphp/saml2
 */
final class AuthnQuery extends AbstractSubjectQuery implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Constructor for SAML 2 AuthnQuery.
     *
     * @param \SimpleSAML\XML\Type\IDValue $id
     * @param \SimpleSAML\SAML2\XML\saml\Subject $subject
     * @param \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null $requestedAuthnContext
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $sessionIndex
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    public function __construct(
        IDValue $id,
        Subject $subject,
        SAMLDateTimeValue $issueInstant,
        protected ?RequestedAuthnContext $requestedAuthnContext = null,
        protected ?SAMLStringValue $sessionIndex = null,
        ?Issuer $issuer = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
    ) {
        parent::__construct($id, $subject, $issuer, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Retrieve RequestedAuthnContext.
     *
     * @return \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null
     */
    public function getRequestedAuthnContext(): ?RequestedAuthnContext
    {
        return $this->requestedAuthnContext;
    }


    /**
     * Retrieve session index.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getSessionIndex(): ?SAMLStringValue
    {
        return $this->sessionIndex;
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
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnQuery', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnQuery::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version->getValue(), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version->getValue(), '>='), RequestVersionTooHighException::class);

        $requestedAuthnContext = RequestedAuthnContext::getChildrenOfClass($xml);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::countBetween($issuer, 0, 1);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one saml:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $subject = Subject::getChildrenOfClass($xml);
        Assert::notEmpty($subject, 'Missing subject in subject query.', MissingElementException::class);
        Assert::maxCount(
            $subject,
            1,
            'More than one <saml:Subject> in AttributeQuery',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $request = new static(
            self::getAttribute($xml, 'ID', IDValue::class),
            array_pop($subject),
            self::getAttribute($xml, 'IssueInstant', SAMLDateTimeValue::class),
            array_pop($requestedAuthnContext),
            self::getOptionalAttribute($xml, 'SessionIndex', SAMLStringValue::class, null),
            array_pop($issuer),
            self::getOptionalAttribute($xml, 'Destination', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'Consent', SAMLAnyURIValue::class, null),
            array_pop($extensions),
        );

        if (!empty($signature)) {
            $request->setSignature($signature[0]);
            $request->setXML($xml);
        }

        return $request;
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

        $sessionIndex = $this->getSessionIndex();
        if ($sessionIndex !== null) {
            $e->setAttribute('SessionIndex', $sessionIndex->getValue());
        }

        $this->getRequestedAuthnContext()?->toXML($e);

        return $e;
    }
}
