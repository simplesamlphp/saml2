<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

use function preg_replace;

/**
 * Class for handling the mdrpi:RegistrationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class RegistrationInfo extends AbstractMdrpiElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;

    /**
     * Create/parse a mdrpi:RegistrationInfo element.
     *
     * @param string $registrationAuthority
     * @param \DateTimeImmutable|null $registrationInstant
     * @param \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy[] $registrationPolicy
     */
    public function __construct(
        protected string $registrationAuthority,
        protected ?DateTimeImmutable $registrationInstant = null,
        protected array $registrationPolicy = [],
    ) {
        Assert::nullOrSame($registrationInstant?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::maxCount($registrationPolicy, C::UNBOUNDED_LIMIT);
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
     * @return \DateTimeImmutable|null
     */
    public function getRegistrationInstant(): ?DateTimeImmutable
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

            SAMLAssert::validDateTime($registrationInstant, ProtocolViolationException::class);
            $registrationInstant = new DateTimeImmutable($registrationInstant);
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
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('registrationAuthority', $this->getRegistrationAuthority());

        if ($this->getRegistrationInstant() !== null) {
            $e->setAttribute('registrationInstant', $this->getRegistrationInstant()->format(C::DATETIME_FORMAT));
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
        $data = self::processArrayContents($data);

        return new static(
            $data['registrationAuthority'],
            $data['registrationInstant'] ?? null,
            $data['RegistrationPolicy'] ?? [],
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array $data
     * @return array $data
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        Assert::allOneOf(
            array_keys($data),
            ['registrationauthority', 'registrationinstant', 'registrationpolicy'],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'registrationauthority', ArrayValidationException::class);
        Assert::string($data['registrationauthority'], ArrayValidationException::class);
        $retval = ['registrationAuthority' => $data['registrationauthority']];

        if (array_key_exists('registrationinstant', $data)) {
            Assert::string($data['registrationinstant'], ArrayValidationException::class);
            SAMLAssert::validDateTime($data['registrationinstant'], ArrayValidationException::class);
            $retval['registrationInstant'] = new DateTimeImmutable($data['registrationinstant']);
        }

        if (array_key_exists('registrationpolicy', $data)) {
            Assert::isArray($data['registrationpolicy'], ArrayValidationException::class);
            foreach ($data['registrationpolicy'] as $lang => $rp) {
                $retval['RegistrationPolicy'][] = RegistrationPolicy::fromArray([$lang => $rp]);
            }
        }

        return $retval;
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
            $data['registrationInstant'] = $this->getRegistrationInstant()->format(C::DATETIME_FORMAT);
        }

        if (!empty($this->getRegistrationPolicy())) {
            $data['RegistrationPolicy'] = [];
            foreach ($this->getRegistrationPolicy() as $rp) {
                $data['RegistrationPolicy'] = array_merge($data['RegistrationPolicy'], $rp->toArray());
            }
        }

        return $data;
    }
}
