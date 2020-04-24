<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\saml\AuthnContext;
use SAML2\XML\saml\SubjectLocality;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 AuthnStatement
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp
 */
final class AuthnStatement extends AbstractSamlElement
{
    /** @var \SAML2\XML\saml\AuthnContext */
    protected $authnContext;

    /** @var int */
    protected $authnInstant;

    /** @var int|null */
    protected $sessionNotOnOrAfter;

    /** @var string|null */
    protected $sessionIndex = null;

    /** @var \SAML2\XML\saml\SubjectLocality|null */
    protected $subjectLocality = null;


    /**
     * Initialize an AuthnContext.
     *
     * @param \SAML2\XML\saml\AuthnContext $authnContext
     * @param int $authnInstant
     * @param int|null $sessionNotOnOrAfter
     * @param string|null $sessionIndex
     * @param \SAML2\XML\saml\SubjectLocality|null $subjectLocality
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
    }


    /**
     * Collect the value of the authnContext-property
     *
     * @return \SAML2\XML\saml\AuthnContext
     */
    public function getAuthnContext(): AuthnContext
    {
        return $this->authnContext;
    }


    /**
     * Set the value of the authnContext-property
     *
     * @param \SAML2\XML\saml\AuthnContext $authnContext
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
     * @throws \InvalidArgumentException
     */
    private function setSessionIndex(?string $sessionIndex): void
    {
        $this->sessionIndex = $sessionIndex;
    }


    /**
     * Collect the value of the subjectLocality-property
     *
     * @return \SAML2\XML\saml\SubjectLocality|null
     */
    public function getSubjectLocality(): ?SubjectLocality
    {
        return $this->subjectLocality;
    }


    /**
     * Set the value of the subjectLocality-property
     *
     * @param \SAML2\XML\saml\SubjectLocality|null $subjectLocality
     * @return void
     */
    private function setSubjectLocality(?SubjectLocality $subjectLocality): void
    {
        $this->subjectLocality = $subjectLocality;
    }


    /**
     * Convert XML into a AuthnContext
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AuthnContext
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnStatement');
        Assert::same($xml->namespaceURI, AuthnStatement::NS);

        $authnContext = AuthnContext::getChildrenOfClass($xml);
        Assert::minCount($authnContext, 1);

        $authnInstant = Utils::xsDateTimeToTimestamp(self::getAttribute($xml, 'AuthnInstant'));
        $sessionNotOnOrAfter = self::getAttribute($xml, 'SessionNotOnOrAfter', null);
        $subjectLocality = SubjectLocality::getChildrenOfClass($xml);

        return new self(
            array_pop($authnContext),
            $authnInstant,
            is_null($sessionNotOnOrAfter) ? $sessionNotOnOrAfter : Utils::xsDateTimeToTimestamp($sessionNotOnOrAfter),
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
