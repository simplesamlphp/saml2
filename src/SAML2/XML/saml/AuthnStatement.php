<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\XML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;

use function array_pop;
use function preg_replace;

/**
 * Class representing a SAML2 AuthnStatement
 *
 * @package simplesamlphp/saml2
 */
final class AuthnStatement extends AbstractStatementType
{
    /**
     * Initialize an AuthnStatement.
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContext $authnContext
     * @param \DateTimeImmutable $authnInstant
     * @param \DateTimeImmutable|null $sessionNotOnOrAfter
     * @param string|null $sessionIndex
     * @param \SimpleSAML\SAML2\XML\saml\SubjectLocality|null $subjectLocality
     */
    public function __construct(
        protected AuthnContext $authnContext,
        protected DateTimeImmutable $authnInstant,
        protected ?DateTimeImmutable $sessionNotOnOrAfter = null,
        protected ?string $sessionIndex = null,
        protected ?SubjectLocality $subjectLocality = null,
    ) {
        Assert::same($authnInstant->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::nullOrSame($sessionNotOnOrAfter?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::nullOrNotWhitespaceOnly($sessionIndex);
    }


    /**
     * Collect the value of the authnContext-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContext
     */
    public function getAuthnContext(): AuthnContext
    {
        return $this->authnContext;
    }


    /**
     * Collect the value of the AuthnInstant-property
     *
     * @return \DateTimeImmutable
     */
    public function getAuthnInstant(): DateTimeImmutable
    {
        return $this->authnInstant;
    }


    /**
     * Collect the value of the sessionNotOnOrAfter-property
     *
     * @return \DateTimeImmutable|null
     */
    public function getSessionNotOnOrAfter(): ?DateTimeImmutable
    {
        return $this->sessionNotOnOrAfter;
    }


    /**
     * Collect the value of the sessionIndex-property
     *
     * @return ?string
     */
    public function getSessionIndex(): ?string
    {
        return $this->sessionIndex;
    }


    /**
     * Collect the value of the subjectLocality-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\SubjectLocality|null
     */
    public function getSubjectLocality(): ?SubjectLocality
    {
        return $this->subjectLocality;
    }


    /**
     * Convert XML into an AuthnStatement
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return static
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \Exception if the authentication instant is not a valid timestamp.
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnStatement', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnStatement::NS, InvalidDOMElementException::class);

        $authnContext = AuthnContext::getChildrenOfClass($xml);
        Assert::minCount(
            $authnContext,
            1,
            'Missing <saml:AuthnContext> in <saml:AuthnStatement>',
            MissingElementException::class,
        );
        Assert::maxCount(
            $authnContext,
            1,
            'More than one <saml:AuthnContext> in <saml:AuthnStatement>',
            TooManyElementsException::class,
        );

        $authnInstant = self::getAttribute($xml, 'AuthnInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $authnInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $authnInstant, 1);

        Assert::validDateTime($authnInstant, ProtocolViolationException::class);
        $authnInstant = new DateTimeImmutable($authnInstant);

        $sessionNotOnOrAfter = self::getOptionalAttribute($xml, 'SessionNotOnOrAfter', null);
        if ($sessionNotOnOrAfter !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $sessionNotOnOrAfter = preg_replace('/([.][0-9]+Z)$/', 'Z', $sessionNotOnOrAfter, 1);

            Assert::validDateTime($sessionNotOnOrAfter, ProtocolViolationException::class);
            $sessionNotOnOrAfter = new DateTimeImmutable($sessionNotOnOrAfter);
        }

        $subjectLocality = SubjectLocality::getChildrenOfClass($xml);

        return new static(
            array_pop($authnContext),
            $authnInstant,
            $sessionNotOnOrAfter,
            self::getOptionalAttribute($xml, 'SessionIndex', null),
            array_pop($subjectLocality),
        );
    }


    /**
     * Convert this AuthnStatement to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnStatement to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getSubjectLocality() !== null && !$this->getSubjectLocality()->isEmptyElement()) {
            $this->getSubjectLocality()->toXML($e);
        }

        $this->getAuthnContext()->toXML($e);

        $e->setAttribute('AuthnInstant', $this->getAuthnInstant()->format(C::DATETIME_FORMAT));

        if ($this->getSessionIndex() !== null) {
            $e->setAttribute('SessionIndex', $this->getSessionIndex());
        }

        if ($this->getSessionNotOnOrAfter() !== null) {
            $e->setAttribute('SessionNotOnOrAfter', $this->getSessionNotOnOrAfter()->format(C::DATETIME_FORMAT));
        }

        return $e;
    }
}
