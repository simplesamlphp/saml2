<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling the mdrpi:RegistrationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class RegistrationInfo extends AbstractMdrpiElement
{
    /**
     * The identifier of the metadata registration authority.
     *
     * @var string
     */
    protected string $registrationAuthority;

    /**
     * The registration timestamp for the metadata, as a UNIX timestamp.
     *
     * @var int|null
     */
    protected ?int $registrationInstant = null;

    /**
     * Link to registration policy for this metadata.
     *
     * This is an associative array with language=>URL.
     *
     * @var \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy[]
     */
    protected array $RegistrationPolicy = [];


    /**
     * Create/parse a mdrpi:RegistrationInfo element.
     *
     * @param string $registrationAuthority
     * @param int|null $registrationInstant
     * @param \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy[] $RegistrationPolicy
     */
    public function __construct(
        string $registrationAuthority,
        int $registrationInstant = null,
        array $RegistrationPolicy = []
    ) {
        $this->setRegistrationAuthority($registrationAuthority);
        $this->setRegistrationInstant($registrationInstant);
        $this->setRegistrationPolicy($RegistrationPolicy);
    }


    /**
     * Collect the value of the RegistrationAuthority property
     *
     * @return string
     */
    public function getRegistrationAuthority(): string
    {
        return $this->registrationAuthority;
    }


    /**
     * Set the value of the registrationAuthority property
     *
     * @param string $registrationAuthority
     */
    private function setRegistrationAuthority(string $registrationAuthority): void
    {
        $this->registrationAuthority = $registrationAuthority;
    }


    /**
     * Collect the value of the registrationInstant property
     *
     * @return int|null
     */
    public function getRegistrationInstant(): ?int
    {
        return $this->registrationInstant;
    }


    /**
     * Set the value of the registrationInstant property
     *
     * @param int|null $registrationInstant
     */
    private function setRegistrationInstant(?int $registrationInstant): void
    {
        $this->registrationInstant = $registrationInstant;
    }


    /**
     * Collect the value of the RegistrationPolicy property
     *
     * @return \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy[]
     */
    public function getRegistrationPolicy(): array
    {
        return $this->RegistrationPolicy;
    }


    /**
     * Set the value of the RegistrationPolicy property
     *
     * @param \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy[] $registrationPolicy
     */
    private function setRegistrationPolicy(array $registrationPolicy): void
    {
        Assert::allIsInstanceOf($registrationPolicy, RegistrationPolicy::class);

        $this->RegistrationPolicy = $registrationPolicy;
    }


    /**
     * Convert XML into a RegistrationInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'RegistrationInfo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RegistrationInfo::NS, InvalidDOMElementException::class);

        $registrationAuthority = self::getAttribute($xml, 'registrationAuthority');
        $registrationInstant = self::getAttribute($xml, 'registrationInstant', null);
        if ($registrationInstant !== null) {
            $registrationInstant = XMLUtils::xsDateTimeToTimestamp($registrationInstant);
        }
        $RegistrationPolicy = RegistrationPolicy::getChildrenOfClass($xml);

        return new self($registrationAuthority, $registrationInstant, $RegistrationPolicy);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('registrationAuthority', $this->registrationAuthority);

        if ($this->registrationInstant !== null) {
            $e->setAttribute('registrationInstant', gmdate('Y-m-d\TH:i:s\Z', $this->registrationInstant));
        }

        foreach ($this->RegistrationPolicy as $rp) {
            $rp->toXML($e);
        }

        return $e;
    }
}
