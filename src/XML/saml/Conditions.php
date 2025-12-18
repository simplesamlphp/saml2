<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;

use function array_pop;

/**
 * Class representing SAML 2 Conditions element.
 *
 * @package simplesamlphp/saml2
 */
final class Conditions extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize a Conditions element.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $notBefore
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $notOnOrAfter
     * @param \SimpleSAML\SAML2\XML\saml\AbstractCondition[] $condition
     * @param \SimpleSAML\SAML2\XML\saml\AudienceRestriction[] $audienceRestriction
     * @param \SimpleSAML\SAML2\XML\saml\OneTimeUse|null $oneTimeUse
     * @param \SimpleSAML\SAML2\XML\saml\ProxyRestriction|null $proxyRestriction
     */
    public function __construct(
        protected ?SAMLDateTimeValue $notBefore = null,
        protected ?SAMLDateTimeValue $notOnOrAfter = null,
        protected array $condition = [],
        protected array $audienceRestriction = [],
        protected ?OneTimeUse $oneTimeUse = null,
        protected ?ProxyRestriction $proxyRestriction = null,
    ) {
        /** SAML 2.0 Core specifications paragraph 2.5.1.2 */
        if ($notBefore !== null && $notOnOrAfter !== null) {
            Assert::true(
                $notBefore->toDateTime() < $notOnOrAfter->toDateTime(),
                "The value for NotBefore MUST be less than (earlier than) the value for NotOnOrAfter.",
                ProtocolViolationException::class,
            );
        }

        Assert::maxCount($condition, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($condition, AbstractCondition::class);
        Assert::maxCount($audienceRestriction, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($audienceRestriction, AudienceRestriction::class);
    }


    /**
     * Collect the value of the notBefore-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null
     */
    public function getNotBefore(): ?SAMLDateTimeValue
    {
        return $this->notBefore;
    }


    /**
     * Collect the value of the notOnOrAfter-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null
     */
    public function getNotOnOrAfter(): ?SAMLDateTimeValue
    {
        return $this->notOnOrAfter;
    }


    /**
     * Collect the value of the condition-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AbstractCondition[]
     */
    public function getCondition(): array
    {
        return $this->condition;
    }


    /**
     * Collect the value of the audienceRestriction-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AudienceRestriction[]
     */
    public function getAudienceRestriction(): array
    {
        return $this->audienceRestriction;
    }


    /**
     * Collect the value of the oneTimeUse-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\OneTimeUse|null
     */
    public function getOneTimeUse(): ?OneTimeUse
    {
        return $this->oneTimeUse;
    }


    /**
     * Collect the value of the proxyRestriction-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\ProxyRestriction|null
     */
    public function getProxyRestriction(): ?ProxyRestriction
    {
        return $this->proxyRestriction;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->getNotBefore())
            && empty($this->getNotOnOrAfter())
            && empty($this->getCondition())
            && empty($this->getAudienceRestriction())
            && empty($this->getOneTimeUse())
            && empty($this->getProxyRestriction());
    }


    /**
     * Convert XML into a Conditions object
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Conditions', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Conditions::NS, InvalidDOMElementException::class);

        $condition = AbstractCondition::getChildrenOfClass($xml);
        $audienceRestriction = AudienceRestriction::getChildrenOfClass($xml);
        $oneTimeUse = OneTimeUse::getChildrenOfClass($xml);
        $proxyRestriction = ProxyRestriction::getChildrenOfClass($xml);

        Assert::maxCount(
            $oneTimeUse,
            1,
            'There MUST occur at most one <saml:OneTimeUse> element inside a <saml:Conditions>',
            ProtocolViolationException::class,
        );
        Assert::maxCount(
            $proxyRestriction,
            1,
            'There MUST occur at most one <saml:ProxyRestriction> element inside a <saml:Conditions>',
            ProtocolViolationException::class,
        );

        return new static(
            self::getOptionalAttribute($xml, 'NotBefore', SAMLDateTimeValue::class, null),
            self::getOptionalAttribute($xml, 'NotOnOrAfter', SAMLDateTimeValue::class, null),
            $condition,
            $audienceRestriction,
            array_pop($oneTimeUse),
            array_pop($proxyRestriction),
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement|null $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getNotBefore() !== null) {
            $e->setAttribute('NotBefore', $this->getNotBefore()->getValue());
        }

        if ($this->getNotOnOrAfter() !== null) {
            $e->setAttribute('NotOnOrAfter', $this->getNotOnOrAfter()->getValue());
        }

        foreach ($this->getCondition() as $condition) {
            $condition->toXML($e);
        }

        foreach ($this->getAudienceRestriction() as $audienceRestriction) {
            $audienceRestriction->toXML($e);
        }

        $this->getOneTimeUse()?->toXML($e);
        $this->getProxyRestriction()?->toXML($e);

        return $e;
    }
}
