<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_pop;
use function gmdate;

/**
 * Class representing a SAML2 AuthnStatement
 *
 * @package simplesamlphp/saml2
 */
final class AuthnStatement extends AbstractStatementType
{
    /**
     * Initialize an AuthnContext.
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContext $authnContext
     * @param int $authnInstant
     * @param int|null $sessionNotOnOrAfter
     * @param string|null $sessionIndex
     * @param \SimpleSAML\SAML2\XML\saml\SubjectLocality|null $subjectLocality
     */
    public function __construct(
        protected AuthnContext $authnContext,
        protected int $authnInstant,
        protected ?int $sessionNotOnOrAfter = null,
        protected ?string $sessionIndex = null,
        protected ?SubjectLocality $subjectLocality = null,
    ) {
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
     * @return int
     */
    public function getAuthnInstant(): int
    {
        return $this->authnInstant;
    }


    /**
     * Collect the value of the sessionNotOnOrAfter-property
     *
     * @return int|null
     */
    public function getSessionNotOnOrAfter(): ?int
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
     * @return \SimpleSAML\SAML2\XML\saml\AuthnStatement
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

        Assert::validDateTimeZulu($authnInstant, ProtocolViolationException::class);
        $authnInstant = XMLUtils::xsDateTimeToTimestamp($authnInstant);

        $sessionNotOnOrAfter = self::getAttribute($xml, 'SessionNotOnOrAfter', null);
        if ($sessionNotOnOrAfter !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $sessionNotOnOrAfter = preg_replace('/([.][0-9]+Z)$/', 'Z', $sessionNotOnOrAfter, 1);

            Assert::validDateTimeZulu($sessionNotOnOrAfter, ProtocolViolationException::class);
            $sessionNotOnOrAfter = XMLUtils::xsDateTimeToTimestamp($sessionNotOnOrAfter);
        }

        $subjectLocality = SubjectLocality::getChildrenOfClass($xml);

        return new static(
            array_pop($authnContext),
            $authnInstant,
            $sessionNotOnOrAfter,
            self::getAttribute($xml, 'SessionIndex', null),
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

        $e->setAttribute('AuthnInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getAuthnInstant()));

        if ($this->getSessionIndex() !== null) {
            $e->setAttribute('SessionIndex', $this->getSessionIndex());
        }

        if ($this->getSessionNotOnOrAfter() !== null) {
            $e->setAttribute('SessionNotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->getSessionNotOnOrAfter()));
        }

        return $e;
    }
}
