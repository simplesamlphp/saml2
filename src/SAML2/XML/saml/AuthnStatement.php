<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class representing a SAML2 AuthnStatement
 *
 * @package simplesamlphp/saml2
 */
final class AuthnStatement extends AbstractStatement
{
    /** @var \SimpleSAML\SAML2\XML\saml\AuthnContext */
    protected AuthnContext $authnContext;

    /** @var int */
    protected int $authnInstant;

    /** @var int|null */
    protected ?int $sessionNotOnOrAfter;

    /** @var string|null */
    protected ?string $sessionIndex = null;

    /** @var \SimpleSAML\SAML2\XML\saml\SubjectLocality|null */
    protected ?SubjectLocality $subjectLocality = null;


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
        AuthnContext $authnContext,
        int $authnInstant,
        ?int $sessionNotOnOrAfter = null,
        ?string $sessionIndex = null,
        SubjectLocality $subjectLocality = null
    ) {
        $this->setAuthnContext($authnContext);
        $this->setAuthnInstant($authnInstant);
        $this->setSessionNotOnOrAfter($sessionNotOnOrAfter);
        $this->setSessionIndex($sessionIndex);
        $this->setSubjectLocality($subjectLocality);
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
     * Set the value of the authnContext-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContext $authnContext
     */
    private function setAuthnContext(AuthnContext $authnContext): void
    {
        $this->authnContext = $authnContext;
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
     * Set the value of the authnInstant-property
     *
     * @param int $authnInstant
     */
    private function setAuthnInstant(int $authnInstant): void
    {
        $this->authnInstant = $authnInstant;
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
     * Set the value of the sessionNotOnOrAfter-property
     *
     * @param int|null $sessionNotOnOrAfter
     */
    private function setSessionNotOnOrAfter(?int $sessionNotOnOrAfter): void
    {
        $this->sessionNotOnOrAfter = $sessionNotOnOrAfter;
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
     * Set the value of the sessionIndex-property
     *
     * @param string|null $sessionIndex
     */
    private function setSessionIndex(?string $sessionIndex): void
    {
        $this->sessionIndex = $sessionIndex;
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
     * Set the value of the subjectLocality-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\SubjectLocality|null $subjectLocality
     */
    private function setSubjectLocality(?SubjectLocality $subjectLocality): void
    {
        $this->subjectLocality = $subjectLocality;
    }


    /**
     * Convert XML into an AuthnStatement
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SimpleSAML\SAML2\XML\saml\AuthnStatement
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     * @throws \Exception if the authentication instant is not a valid timestamp.
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnStatement', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnStatement::NS, InvalidDOMElementException::class);

        $authnContext = AuthnContext::getChildrenOfClass($xml);
        Assert::minCount(
            $authnContext,
            1,
            'Missing <saml:AuthnContext> in <saml:AuthnStatement>',
            MissingElementException::class
        );
        Assert::maxCount(
            $authnContext,
            1,
            'More than one <saml:AuthnContext> in <saml:AuthnStatement>',
            TooManyElementsException::class
        );

        $authnInstant = XMLUtils::xsDateTimeToTimestamp(self::getAttribute($xml, 'AuthnInstant'));
        $sessionNotOnOrAfter = self::getAttribute($xml, 'SessionNotOnOrAfter', null);
        $subjectLocality = SubjectLocality::getChildrenOfClass($xml);

        return new self(
            array_pop($authnContext),
            $authnInstant,
            is_null($sessionNotOnOrAfter) ? $sessionNotOnOrAfter : XMLUtils::xsDateTimeToTimestamp($sessionNotOnOrAfter),
            self::getAttribute($xml, 'SessionIndex', null),
            array_pop($subjectLocality)
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

        if ($this->subjectLocality !== null) {
            $this->subjectLocality->toXML($e);
        }

        $this->authnContext->toXML($e);

        $e->setAttribute('AuthnInstant', gmdate('Y-m-d\TH:i:s\Z', $this->authnInstant));

        if ($this->sessionIndex !== null) {
            $e->setAttribute('SessionIndex', $this->sessionIndex);
        }

        if ($this->sessionNotOnOrAfter !== null) {
            $e->setAttribute('SessionNotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->sessionNotOnOrAfter));
        }

        return $e;
    }
}
