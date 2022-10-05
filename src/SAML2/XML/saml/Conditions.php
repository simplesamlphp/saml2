<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_pop;
use function gmdate;
use function is_bool;

/**
 * Class representing SAML 2 Conditions element.
 *
 * @package simplesamlphp/saml2
 */
final class Conditions extends AbstractSamlElement
{
    /** @var int|null */
    protected ?int $notBefore;

    /** @var int|null */
    protected ?int $notOnOrAfter;

    /** @var \SimpleSAML\SAML2\XML\saml\Condition[] */
    protected array $condition;

    /** @var \SimpleSAML\SAML2\XML\saml\AudienceRestriction[] */
    protected array $audienceRestriction;

    /** @var bool */
    protected bool $oneTimeUse = false;

    /** @var \SimpleSAML\SAML2\XML\saml\ProxyRestriction|null */
    protected ?ProxyRestriction $proxyRestriction;


    /**
     * Initialize a Conditions element.
     *
     * @param int|null $notBefore
     * @param int|null $notOnOrAfter
     * @param \SimpleSAML\SAML2\XML\saml\Condition[] $condition
     * @param \SimpleSAML\SAML2\XML\saml\AudienceRestriction[] $audienceRestriction
     * @param bool|null $oneTimeUse
     * @param \SimpleSAML\SAML2\XML\saml\ProxyRestriction|null $proxyRestriction
     */
    public function __construct(
        ?int $notBefore = null,
        ?int $notOnOrAfter = null,
        array $condition = [],
        array $audienceRestriction = [],
        ?bool $oneTimeUse = null,
        ?ProxyRestriction $proxyRestriction = null
    ) {
        $this->setNotBefore($notBefore);
        $this->setNotOnOrAfter($notOnOrAfter);
        $this->setCondition($condition);
        $this->setAudienceRestriction($audienceRestriction);
        if (is_bool($oneTimeUse)) {
            $this->setOneTimeUse($oneTimeUse);
        }
        $this->setProxyRestriction($proxyRestriction);
    }


    /**
     * Collect the value of the notBefore-property
     *
     * @return int|null
     */
    public function getNotBefore(): ?int
    {
        return $this->notBefore;
    }


    /**
     * Set the value of the notBefore-property
     *
     * @param int|null $notBefore
     */
    private function setNotBefore(?int $notBefore): void
    {
        $this->notBefore = $notBefore;
    }


    /**
     * Collect the value of the notOnOrAfter-property
     *
     * @return int|null
     */
    public function getNotOnOrAfter(): ?int
    {
        return $this->notOnOrAfter;
    }


    /**
     * Set the value of the notOnOrAfter-property
     *
     * @param int|null $notOnOrAfter
     */
    private function setNotOnOrAfter(?int $notOnOrAfter): void
    {
        $this->notOnOrAfter = $notOnOrAfter;
    }


    /**
     * Collect the value of the condition-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\Condition[]
     */
    public function getCondition(): array
    {
        return $this->condition;
    }


    /**
     * Set the value of the condition-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\Condition[] $condition
     */
    private function setCondition(array $condition): void
    {
        Assert::allIsInstanceOf($condition, Condition::class);

        $this->condition = $condition;
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
     * Set the value of the audienceRestriction-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\AudienceRestriction[] $audienceRestriction
     */
    private function setAudienceRestriction(array $audienceRestriction): void
    {
        Assert::allIsInstanceOf($audienceRestriction, AudienceRestriction::class);

        $this->audienceRestriction = $audienceRestriction;
    }


    /**
     * Collect the value of the oneTimeUse-property
     *
     * @return bool
     */
    public function getOneTimeUse(): bool
    {
        return $this->oneTimeUse;
    }


    /**
     * Set the value of the oneTimeUse-property
     *
     * @param bool $oneTimeUse
     */
    private function setOneTimeUse(bool $oneTimeUse): void
    {
        $this->oneTimeUse = $oneTimeUse;
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
     * Set the value of the proxyRestriction-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\ProxyRestriction|null $proxyRestriction
     */
    private function setProxyRestriction(?ProxyRestriction $proxyRestriction): void
    {
        $this->proxyRestriction = $proxyRestriction;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return (
            empty($this->notBefore)
            && empty($this->notOnOrAfter)
            && empty($this->condition)
            && empty($this->audienceRestriction)
            && $this->oneTimeUse === false
            && empty($this->proxyRestriction)
        );
    }


    /**
     * Convert XML into a Conditions object
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Conditions', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Conditions::NS, InvalidDOMElementException::class);

        $notBefore = self::getAttribute($xml, 'NotBefore', null);
        if ($notBefore !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $notBefore = preg_replace('/([.][0-9]+Z)$/', 'Z', $notBefore, 1);

            Assert::validDateTimeZulu($notBefore, ProtocolViolationException::class);
        }

        $notOnOrAfter = self::getAttribute($xml, 'NotOnOrAfter', null);
        if ($notOnOrAfter !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $notOnOrAfter = preg_replace('/([.][0-9]+Z)$/', 'Z', $notOnOrAfter, 1);

            Assert::validDateTimeZulu($notOnOrAfter, ProtocolViolationException::class);
        }

        $condition = AbstractCondition::getChildrenOfClass($xml);
        $audienceRestriction = AudienceRestriction::getChildrenOfClass($xml);
        $oneTimeUse = XMLUtils::extractStrings($xml, AbstractSamlElement::NS, 'OneTimeUse');
        $proxyRestriction = ProxyRestriction::getChildrenOfClass($xml);

        Assert::maxCount(
            $oneTimeUse,
            1,
            'There MUST occur at most one <saml:OneTimeUse> element inside a <saml:Conditions>',
            ProtocolViolationException::class
        );
        Assert::maxCount(
            $proxyRestriction,
            1,
            'There MUST occur at most one <saml:ProxyRestriction> element inside a <saml:Conditions>',
            ProtocolViolationException::class
        );

        return new static(
            $notBefore !== null ? XMLUtils::xsDateTimeToTimestamp($notBefore) : null,
            $notOnOrAfter !== null ? XMLUtils::xsDateTimeToTimestamp($notOnOrAfter) : null,
            $condition,
            $audienceRestriction,
            !empty($oneTimeUse),
            array_pop($proxyRestriction)
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

        if ($this->notBefore !== null) {
            $e->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', $this->notBefore));
        }

        if ($this->notOnOrAfter !== null) {
            $e->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->notOnOrAfter));
        }

        foreach ($this->condition as $condition) {
            $condition->toXML($e);
        }

        foreach ($this->audienceRestriction as $audienceRestriction) {
            $audienceRestriction->toXML($e);
        }

        if ($this->oneTimeUse !== false) {
            /** @psalm-suppress PossiblyNullReference */
            $e->appendChild(
                $e->ownerDocument->createElementNS(AbstractSamlElement::NS, 'saml:OneTimeUse')
            );
        }

        if ($this->proxyRestriction !== null) {
            $this->proxyRestriction->toXML($e);
        }

        return $e;
    }
}
