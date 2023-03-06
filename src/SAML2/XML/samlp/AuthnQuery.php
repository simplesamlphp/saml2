<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function in_array;

/**
 * Class for SAML 2 AuthnQuery query messages.
 *
 * @package simplesamlphp/saml2
 */
final class AuthnQuery extends AbstractSubjectQuery
{
    /**
     * Constructor for SAML 2 AuthnQuery.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Subject $subject
     * @param \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null $requestedAuthnContext
     * @param string|null $sessionIndex
     * @param \SimpleSAML\SAML2\XML\saml\Issuer $issuer
     * @param string|null $id
     * @param string $version
     * @param int $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions $extensions
     */
    public function __construct(
        Subject $subject,
        protected ?RequestedAuthnContext $requestedAuthnContext = null,
        protected ?string $sessionIndex = null,
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        parent::__construct($subject, $issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);
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
     * @return string|null
     */
    public function getSessionIndex(): ?string
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

        /** @psalm-var string $version */
        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version, '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version, '>='), RequestVersionTooHighException::class);

        $id = self::getAttribute($xml, 'ID');
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);
        $sessionIndex = self::getAttribute($xml, 'SessionIndex', null);

        /** @psalm-var string $issueInstant */
        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = XMLUtils::xsDateTimeToTimestamp($issueInstant);

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
            array_pop($subject),
            array_pop($requestedAuthnContext),
            $sessionIndex,
            array_pop($issuer),
            $id,
            $version,
            $issueInstant,
            $destination,
            $consent,
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
            $e->setAttribute('SessionIndex', $sessionIndex);
        }

        $this->getRequestedAuthnContext()?->toXML($e);

        return $e;
    }
}
