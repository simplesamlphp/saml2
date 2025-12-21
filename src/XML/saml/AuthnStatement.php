<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;

use function array_pop;

/**
 * Class representing a SAML2 AuthnStatement
 *
 * @package simplesamlphp/saml2
 */
final class AuthnStatement extends AbstractStatementType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize an AuthnStatement.
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContext $authnContext
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $authnInstant
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $sessionNotOnOrAfter
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $sessionIndex
     * @param \SimpleSAML\SAML2\XML\saml\SubjectLocality|null $subjectLocality
     */
    public function __construct(
        protected AuthnContext $authnContext,
        protected SAMLDateTimeValue $authnInstant,
        protected ?SAMLDateTimeValue $sessionNotOnOrAfter = null,
        protected ?SAMLStringValue $sessionIndex = null,
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
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue
     */
    public function getAuthnInstant(): SAMLDateTimeValue
    {
        return $this->authnInstant;
    }


    /**
     * Collect the value of the sessionNotOnOrAfter-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null
     */
    public function getSessionNotOnOrAfter(): ?SAMLDateTimeValue
    {
        return $this->sessionNotOnOrAfter;
    }


    /**
     * Collect the value of the sessionIndex-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getSessionIndex(): ?SAMLStringValue
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
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

        $subjectLocality = SubjectLocality::getChildrenOfClass($xml);

        return new static(
            array_pop($authnContext),
            self::getAttribute($xml, 'AuthnInstant', SAMLDateTimeValue::class),
            self::getOptionalAttribute($xml, 'SessionNotOnOrAfter', SAMLDateTimeValue::class, null),
            self::getOptionalAttribute($xml, 'SessionIndex', SAMLStringValue::class, null),
            array_pop($subjectLocality),
        );
    }


    /**
     * Convert this AuthnStatement to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getSubjectLocality() !== null && !$this->getSubjectLocality()->isEmptyElement()) {
            $this->getSubjectLocality()->toXML($e);
        }

        $this->getAuthnContext()->toXML($e);

        $e->setAttribute('AuthnInstant', $this->getAuthnInstant()->getValue());

        if ($this->getSessionIndex() !== null) {
            $e->setAttribute('SessionIndex', $this->getSessionIndex()->getValue());
        }

        if ($this->getSessionNotOnOrAfter() !== null) {
            $e->setAttribute('SessionNotOnOrAfter', $this->getSessionNotOnOrAfter()->getValue());
        }

        return $e;
    }
}
