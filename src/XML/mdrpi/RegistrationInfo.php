<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;

/**
 * Class for handling the mdrpi:RegistrationInfo element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 *
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
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $registrationAuthority
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $registrationInstant
     * @param \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy[] $registrationPolicy
     */
    public function __construct(
        protected SAMLStringValue $registrationAuthority,
        protected ?SAMLDateTimeValue $registrationInstant = null,
        protected array $registrationPolicy = [],
    ) {
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
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue
     */
    public function getRegistrationAuthority(): SAMLStringValue
    {
        return $this->registrationAuthority;
    }


    /**
     * Collect the value of the registrationInstant property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null
     */
    public function getRegistrationInstant(): ?SAMLDateTimeValue
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RegistrationInfo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RegistrationInfo::NS, InvalidDOMElementException::class);

        $registrationAuthority = self::getAttribute($xml, 'registrationAuthority', SAMLStringValue::class);
        $registrationInstant = self::getOptionalAttribute($xml, 'registrationInstant', SAMLDateTimeValue::class, null);
        $RegistrationPolicy = RegistrationPolicy::getChildrenOfClass($xml);

        return new static($registrationAuthority, $registrationInstant, $RegistrationPolicy);
    }


    /**
     * Convert this element to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('registrationAuthority', $this->getRegistrationAuthority()->getValue());

        if ($this->getRegistrationInstant() !== null) {
            $e->setAttribute('registrationInstant', $this->getRegistrationInstant()->getValue());
        }

        foreach ($this->getRegistrationPolicy() as $rp) {
            $rp->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array{
     *   'registrationAuthority': string,
     *   'registrationInstant'?: string,
     *   'RegistrationPolicy'?: array,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            SAMLStringValue::fromString($data['registrationAuthority']),
            $data['registrationInstant'] !== null ? SAMLDateTimeValue::fromString($data['registrationInstant']) : null,
            $data['RegistrationPolicy'] ?? [],
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'registrationAuthority': string,
     *   'registrationInstant'?: string,
     *   'RegistrationPolicy'?: array,
     * } $data
     * @return array{
     *   'registrationAuthority': string,
     *   'registrationInstant'?: string,
     *   'RegistrationPolicy'?: array,
     * }
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
            Assert::validSAMLDateTime($data['registrationinstant'], ArrayValidationException::class);
            $retval['registrationInstant'] = $data['registrationinstant'];
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
     * @return array{
     *   'registrationAuthority': string,
     *   'registrationInstant'?: string,
     *   'RegistrationPolicy'?: array,
     * }
     */
    public function toArray(): array
    {
        $data = [];
        $data['registrationAuthority'] = $this->getRegistrationAuthority()->getValue();

        if ($this->getRegistrationInstant() !== null) {
            $data['registrationInstant'] = $this->getRegistrationInstant()->getValue();
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
