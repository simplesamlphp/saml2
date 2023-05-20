<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling the mdrpi:RegistrationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class RegistrationInfo extends AbstractMdrpiElement implements ArrayizableElementInterface
{
    /**
     * Create/parse a mdrpi:RegistrationInfo element.
     *
     * @param string $registrationAuthority
     * @param int|null $registrationInstant
     * @param \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy[] $RegistrationPolicy
     */
    public function __construct(
        protected string $registrationAuthority,
        protected ?int $registrationInstant = null,
        protected array $registrationPolicy = [],
    ) {
        Assert::allIsInstanceOf($registrationPolicy, RegistrationPolicy::class);

        /**
         * 2.1.1:  There MUST NOT be more than one <mdrpi:RegistrationPolicy>,
         *         within a given <mdrpi:RegistrationInfo>, for a given language
         */
        $languages = array_map(
            function ($rp) {
                return $rp->getLanguage();
            },
            $registrationPolicy,
        );
        Assert::uniqueValues(
            $languages,
            'There MUST NOT be more than one <mdrpi:RegistrationPolicy>,'
            . ' within a given <mdrpi:RegistrationInfo>, for a given language',
            ProtocolViolationException::class,
        );
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
     * Collect the value of the registrationInstant property
     *
     * @return int|null
     */
    public function getRegistrationInstant(): ?int
    {
        return $this->registrationInstant;
    }


    /**
     * Collect the value of the RegistrationPolicy property
     *
     * @return \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy[]
     */
    public function getRegistrationPolicy(): array
    {
        return $this->registrationPolicy;
    }


    /**
     * Convert XML into a RegistrationInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RegistrationInfo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RegistrationInfo::NS, InvalidDOMElementException::class);

        $registrationAuthority = self::getAttribute($xml, 'registrationAuthority');
        $registrationInstant = self::getOptionalAttribute($xml, 'registrationInstant', null);

        // 2.1.1:  Time values MUST be expressed in the UTC timezone using the 'Z' timezone identifier
        if ($registrationInstant !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $registrationInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $registrationInstant, 1);

            Assert::validDateTimeZulu($registrationInstant, ProtocolViolationException::class);
            $registrationInstant = XMLUtils::xsDateTimeToTimestamp($registrationInstant);
        }
        $RegistrationPolicy = RegistrationPolicy::getChildrenOfClass($xml);

        return new static($registrationAuthority, $registrationInstant, $RegistrationPolicy);
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
        $e->setAttribute('registrationAuthority', $this->getRegistrationAuthority());

        if ($this->getRegistrationInstant() !== null) {
            $e->setAttribute('registrationInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getRegistrationInstant()));
        }

        foreach ($this->getRegistrationPolicy() as $rp) {
            $rp->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::keyExists($data, 'registrationAuthority');

        $registrationAuthority = $data['registrationAuthority'];
        Assert::string($registrationAuthority);

        $registrationInstant = $data['registrationInstant'] ?? null;
        Assert::nullOrString($registrationInstant);
        $registrationInstant = is_null($registrationInstant) ? null : XMLUtils::xsDateTimeToTimestamp($registrationInstant);

        $rp = $data['registrationPolicy'] ?? [];
        Assert::isArray($rp);

        $registrationPolicy = [];
        foreach ($rp as $k => $v) {
            $registrationPolicy[] = RegistrationPolicy::fromArray([$k => $v]);
        }

        return new static($registrationAuthority, $registrationInstant, $registrationPolicy);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        $data['registrationAuthority'] = $this->getRegistrationAuthority();

        if ($this->getRegistrationInstant() !== null) {
            $data['registrationInstant'] = gmdate('Y-m-d\TH:i:s\Z', $this->getRegistrationInstant());
        }

        if (!empty($this->getRegistrationPolicy())) {
            $data['registrationPolicy'] = [];
            foreach ($this->getRegistrationPolicy() as $rp) {
                $data['registrationPolicy'] = array_merge($data['registrationPolicy'], $rp->toArray());
            }
        }

        return $data;
    }
}
