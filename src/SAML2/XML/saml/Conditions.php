<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function array_pop;

/**
 * Class representing SAML 2 Conditions element.
 *
 * @package simplesamlphp/saml2
 */
final class Conditions extends AbstractSamlElement
{
    /**
     * Initialize a Conditions element.
     *
     * @param \DateTimeImmutable|null $notBefore
     * @param \DateTimeImmutable|null $notOnOrAfter
     * @param \SimpleSAML\SAML2\XML\saml\AbstractCondition[] $condition
     * @param \SimpleSAML\SAML2\XML\saml\AudienceRestriction[] $audienceRestriction
     * @param \SimpleSAML\SAML2\XML\saml\OneTimeUse|null $oneTimeUse
     * @param \SimpleSAML\SAML2\XML\saml\ProxyRestriction|null $proxyRestriction
     */
    public function __construct(
        protected ?DateTimeImmutable $notBefore = null,
        protected ?DateTimeImmutable $notOnOrAfter = null,
        protected array $condition = [],
        protected array $audienceRestriction = [],
        protected ?OneTimeUse $oneTimeUse = null,
        protected ?ProxyRestriction $proxyRestriction = null,
    ) {
        Assert::nullOrSame($notBefore?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::nullOrSame($notOnOrAfter?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::maxCount($condition, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($condition, AbstractCondition::class);
        Assert::maxCount($audienceRestriction, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($audienceRestriction, AudienceRestriction::class);
    }


    /**
     * Collect the value of the notBefore-property
     *
     * @return \DateTimeImmutable|null
     */
    public function getNotBefore(): ?DateTimeImmutable
    {
        return $this->notBefore;
    }


    /**
     * Collect the value of the notOnOrAfter-property
     *
     * @return \DateTimeImmutable|null
     */
    public function getNotOnOrAfter(): ?DateTimeImmutable
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
        return empty($this->notBefore)
            && empty($this->notOnOrAfter)
            && empty($this->condition)
            && empty($this->audienceRestriction)
            && empty($this->oneTimeUse)
            && empty($this->proxyRestriction);
    }


    /**
     * Convert XML into a Conditions object
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Conditions', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Conditions::NS, InvalidDOMElementException::class);

        $notBefore = self::getOptionalAttribute($xml, 'NotBefore', null);
        if ($notBefore !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $notBefore = preg_replace('/([.][0-9]+Z)$/', 'Z', $notBefore, 1);

            Assert::validDateTimeZulu($notBefore, ProtocolViolationException::class);
        }

        $notOnOrAfter = self::getOptionalAttribute($xml, 'NotOnOrAfter', null);
        if ($notOnOrAfter !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $notOnOrAfter = preg_replace('/([.][0-9]+Z)$/', 'Z', $notOnOrAfter, 1);

            Assert::validDateTimeZulu($notOnOrAfter, ProtocolViolationException::class);
        }

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
            $notBefore !== null ? new DateTimeImmutable($notBefore) : null,
            $notOnOrAfter !== null ? new DateTimeImmutable($notOnOrAfter) : null,
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
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getNotBefore() !== null) {
            $e->setAttribute('NotBefore', $this->getNotBefore()->format(C::DATETIME_FORMAT));
        }

        if ($this->getNotOnOrAfter() !== null) {
            $e->setAttribute('NotOnOrAfter', $this->getNotOnOrAfter()->format(C::DATETIME_FORMAT));
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
