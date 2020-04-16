<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 Conditions element.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
final class Conditions extends AbstractSamlElement
{
    /**
     * @var int|null
     */
    protected $notBefore;

    /**
     * @var int|null
     */
    protected $notOnOrAfter;

    /**
     * @var \SAML2\XML\saml\Condition[]
     */
    protected $condition;

    /**
     * @var \SAML2\XML\saml\AudienceRestriction[]
     */
    protected $audienceRestriction;

    /**
     * @var \SAML2\XML\saml\OneTimeUse|null
     */
    protected $oneTimeUse;

    /**
     * @var \SAML2\XML\saml\ProxyRestriction|null
     */
    protected $proxyRestriction;


    /**
     * Initialize a Conditions element.
     *
     * @param int|null $notBefore
     * @param int|null $notOnOrAfter
     * @param \SAML2\XML\saml\Condition[] $condition
     * @param \SAML2\XML\saml\AudienceRestriction[] $audienceRestriction
     * @param \SAML2\XML\saml\OneTimeUse|null $oneTimeUse
     * @param \SAML2\XML\saml\ProxyRestriction|null $proxyRestriction
     */
    public function __construct(
        ?int $notBefore = null,
        ?int $notOnOrAfter = null,
        array $condition = [],
        array $audienceRestriction = [],
        ?OneTimeUse $oneTimeUse = null,
        ?ProxyRestriction $proxyRestriction = null
    ) {
        $this->setNotBefore($notBefore);
        $this->setNotOnOrAfter($notOnOrAfter);
        $this->setCondition($condition);
        $this->setAudienceRestriction($audienceRestriction);
        $this->setOneTimeUse($oneTimeUse);
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
     * @return void
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
     * @return void
     */
    private function setNotOnOrAfter(?int $notOnOrAfter): void
    {
        $this->notOnOrAfter = $notOnOrAfter;
    }


    /**
     * Collect the value of the condition-property
     *
     * @return \SAML2\XML\saml\Condition[]
     */
    public function getCondition(): array
    {
        return $this->condition;
    }


    /**
     * Set the value of the condition-property
     *
     * @param \SAML2\XML\saml\Condition[] $condition
     * @return void
     */
    private function setCondition(array $condition): void
    {
        Assert::allIsInstanceOf($condition, Condition::class);

        $this->condition = $condition;
    }


    /**
     * Collect the value of the audienceRestriction-property
     *
     * @return \SAML2\XML\saml\AudienceRestriction[]
     */
    public function getAudienceRestriction(): array
    {
        return $this->audienceRestriction;
    }


    /**
     * Set the value of the audienceRestriction-property
     *
     * @param \SAML2\XML\saml\AudienceRestriction[] $audienceRestriction
     * @return void
     */
    private function setAudienceRestriction(array $audienceRestriction): void
    {
        Assert::allIsInstanceOf($audienceRestriction, AudienceRestriction::class);

        $this->audienceRestriction = $audienceRestriction;
    }


    /**
     * Collect the value of the oneTimeUse-property
     *
     * @return \SAML2\XML\saml\OneTimeUse|null
     */
    public function getOneTimeUse(): ?OneTimeUse
    {
        return $this->oneTimeUse;
    }


    /**
     * Set the value of the oneTimeUse-property
     *
     * @param \SAML2\XML\saml\OneTimeUse|null $oneTimeUse
     * @return void
     */
    private function setOneTimeUse(?OneTimeUse $oneTimeUse): void
    {
        $this->oneTimeUse = $oneTimeUse;
    }


    /**
     * Collect the value of the proxyRestriction-property
     *
     * @return \SAML2\XML\saml\ProxyRestriction|null
     */
    public function getProxyRestriction(): ?ProxyRestriction
    {
        return $this->proxyRestriction;
    }


    /**
     * Set the value of the proxyRestriction-property
     *
     * @param \SAML2\XML\saml\ProxyRestriction|null $proxyRestriction
     * @return void
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
            && empty($this->oneTimeUse)
            && empty($this->proxyRestriction)
        );
    }


    /**
     * Convert XML into a Conditions object
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Conditions');
        Assert::same($xml->namespaceURI, Conditions::NS);

        $notBefore = self::getAttribute($xml, 'NotBefore', null);
        $notOnOrAfter = self::getAttribute($xml, 'NotOnOrAfter', null);

        $condition = Condition::getChildrenOfClass($xml);
        $audienceRestriction = AudienceRestriction::getChildrenOfClass($xml);
        $oneTimeUse = OneTimeUse::getChildrenOfClass($xml);
        $proxyRestriction = ProxyRestriction::getChildrenOfClass($xml);

        return new self(
            $notBefore !== null ? Utils::xsDateTimeToTimestamp($notBefore) : null,
            $notOnOrAfter !== null ? Utils::xsDateTimeToTimestamp($notOnOrAfter) : null,
            $condition,
            $audienceRestriction,
            array_pop($oneTimeUse),
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

        if ($this->oneTimeUse !== null) {
            $this->oneTimeUse->toXML($e);
        }

        if ($this->proxyRestriction !== null) {
            $this->proxyRestriction->toXML($e);
        }

        return $e;
    }
}
