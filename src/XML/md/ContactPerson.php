<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\XML\Constants\NS;

use function array_change_key_case;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_pop;
use function count;

/**
 * Class representing SAML 2 ContactPerson.
 *
 * @package simplesamlphp/saml2
 */
final class ContactPerson extends AbstractMdElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;
    use SchemaValidatableElementTrait;


    /** The namespace-attribute for the xs:anyAttribute element */
    public const string XS_ANY_ATTR_NAMESPACE = NS::OTHER;

    /**
     * The several different contact types as defined per specification
     *
     * @var string[]
     */
    public const array CONTACT_TYPES = [
        'technical',
        'support',
        'administrative',
        'billing',
        'other',
    ];


    /**
     * ContactPerson constructor.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $contactType
     * @param \SimpleSAML\SAML2\XML\md\Company|null $company
     * @param \SimpleSAML\SAML2\XML\md\GivenName|null $givenName
     * @param \SimpleSAML\SAML2\XML\md\SurName|null $surName
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\XML\md\EmailAddress[] $emailAddress
     * @param \SimpleSAML\SAML2\XML\md\TelephoneNumber[] $telephoneNumber
     * @param \SimpleSAML\XML\Attribute[] $namespacedAttribute
     */
    public function __construct(
        protected SAMLStringValue $contactType,
        protected ?Company $company = null,
        protected ?GivenName $givenName = null,
        protected ?SurName $surName = null,
        ?Extensions $extensions = null,
        protected array $emailAddress = [],
        protected array $telephoneNumber = [],
        array $namespacedAttribute = [],
    ) {
        Assert::oneOf($contactType->getValue(), self::CONTACT_TYPES);
        Assert::maxCount($emailAddress, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($emailAddress, EmailAddress::class);
        Assert::maxCount($telephoneNumber, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($telephoneNumber, TelephoneNumber::class);

        $this->setExtensions($extensions);
        $this->setAttributesNS($namespacedAttribute);
    }


    /**
     * Collect the value of the contactType-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue
     */
    public function getContactType(): SAMLStringValue
    {
        return $this->contactType;
    }


    /**
     * Collect the value of the Company-property
     *
     * @return \SimpleSAML\SAML2\XML\md\Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }


    /**
     * Collect the value of the GivenName-property
     *
     * @return \SimpleSAML\SAML2\XML\md\GivenName|null
     */
    public function getGivenName(): ?GivenName
    {
        return $this->givenName;
    }


    /**
     * Collect the value of the SurName-property
     *
     * @return \SimpleSAML\SAML2\XML\md\SurName|null
     */
    public function getSurName(): ?SurName
    {
        return $this->surName;
    }


    /**
     * Collect the value of the EmailAddress-property.
     *
     * @return \SimpleSAML\SAML2\XML\md\EmailAddress[]
     */
    public function getEmailAddress(): array
    {
        return $this->emailAddress;
    }


    /**
     * Collect the value of the TelephoneNumber property
     *
     * @return \SimpleSAML\SAML2\XML\md\TelephoneNumber[]
     */
    public function getTelephoneNumber(): array
    {
        return $this->telephoneNumber;
    }


    /**
     * Initialize a ContactPerson element.
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'ContactPerson', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ContactPerson::NS, InvalidDOMElementException::class);

        $contactType = self::getAttribute($xml, 'contactType', SAMLStringValue::class);

        $company = Company::getChildrenOfClass($xml);
        Assert::maxCount($company, 1, 'More than one Company in md:ContactPerson');

        $givenName = GivenName::getChildrenOfClass($xml);
        Assert::maxCount($givenName, 1, 'More than one GivenName in md:ContactPerson');

        $surName = SurName::getChildrenOfClass($xml);
        Assert::maxCount($surName, 1, 'More than one SurName in md:ContactPerson');

        $email = EmailAddress::getChildrenOfClass($xml);
        $telephone = TelephoneNumber::getChildrenOfClass($xml);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        return new static(
            $contactType,
            array_pop($company),
            array_pop($givenName),
            array_pop($surName),
            (count($extensions) === 1) ? $extensions[0] : null,
            $email,
            $telephone,
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Convert this ContactPerson to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('contactType', $this->getContactType()->getValue());

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        $this->getExtensions()?->toXML($e);
        $this->getCompany()?->toXML($e);
        $this->getGivenName()?->toXML($e);
        $this->getSurName()?->toXML($e);

        foreach ($this->getEmailAddress() as $mail) {
            $mail->toXML($e);
        }

        foreach ($this->getTelephoneNumber() as $telephone) {
            $telephone->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array{
     *   'contactType': string,
     *   'Company'?: string,
     *   'GivenName'?: string,
     *   'SurName'?: string,
     *   'Extensions'?: array,
     *   'EmailAddress'?: array,
     *   'TelephoneNumber'?: array,
     *   'attributes'?: array,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            SAMLStringValue::fromString($data['contactType']),
            $data['Company'] !== null ? new Company(SAMLStringValue::fromString($data['Company'])) : null,
            $data['GivenName'] !== null ? new GivenName(SAMLStringValue::fromString($data['GivenName'])) : null,
            $data['SurName'] !== null ? new SurName(SAMLStringValue::fromString($data['SurName'])) : null,
            $data['Extensions'] ?? null,
            $data['EmailAddress'] !== null
                ? array_map([EmailAddress::class, 'fromArray'], [$data['EmailAddress']])
                : [],
            $data['TelephoneNumber'] !== null
                ? array_map([TelephoneNumber::class, 'fromArray'], [$data['TelephoneNumber']])
                : [],
            $data['attributes'] ?? [],
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'contactType': string,
     *   'Company'?: string,
     *   'GivenName'?: string,
     *   'SurName'?: string,
     *   'Extensions'?: array,
     *   'EmailAddress'?: array,
     *   'TelephoneNumber'?: array,
     *   'attributes'?: array,
     * } $data
     * @return array{
     *   'contactType': string,
     *   'Company'?: string,
     *   'GivenName'?: string,
     *   'SurName'?: string,
     *   'Extensions'?: array,
     *   'EmailAddress'?: array,
     *   'TelephoneNumber'?: array,
     *   'attributes'?: array,
     * }
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'contacttype',
                'company',
                'givenname',
                'surname',
                'emailaddress',
                'telephonenumber',
                'extensions',
                'attributes',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'contacttype', ArrayValidationException::class);
        Assert::string($data['contacttype'], ArrayValidationException::class);

        $retval = ['contactType' => $data['contacttype']];

        if (array_key_exists('company', $data)) {
            Assert::string($data['company'], ArrayValidationException::class);
            $retval['Company'] = $data['company'];
        }

        if (array_key_exists('givenname', $data)) {
            Assert::string($data['givenname'], ArrayValidationException::class);
            $retval['GivenName'] = $data['givenname'];
        }

        if (array_key_exists('surname', $data)) {
            Assert::string($data['surname'], ArrayValidationException::class);
            $retval['SurName'] = $data['surname'];
        }

        if (array_key_exists('emailaddress', $data)) {
            Assert::isArray($data['emailaddress'], ArrayValidationException::class);
            Assert::allString($data['emailaddress'], ArrayValidationException::class);
            foreach ($data['emailaddress'] as $email) {
                $retval['EmailAddress'][] = $email;
            }
        }

        if (array_key_exists('telephonenumber', $data)) {
            Assert::isArray($data['telephonenumber'], ArrayValidationException::class);
            Assert::allString($data['telephonenumber'], ArrayValidationException::class);
            foreach ($data['telephonenumber'] as $telephone) {
                $retval['TelephoneNumber'][] = $telephone;
            }
        }

        if (array_key_exists('extensions', $data)) {
            Assert::isArray($data['extensions'], ArrayValidationException::class);
            $retval['Extensions'] = new Extensions($data['extensions']);
        }

        if (array_key_exists('attributes', $data)) {
            Assert::isArray($data['attributes'], ArrayValidationException::class);
            Assert::allIsArray($data['attributes'], ArrayValidationException::class);
            foreach ($data['attributes'] as $i => $attr) {
                $retval['attributes'][] = XMLAttribute::fromArray($attr);
            }
        }

        return $retval;
    }


    /**
     * Create an array from this class
     *
     * @return array{
     *   'contactType': string,
     *   'Company'?: string,
     *   'GivenName'?: string,
     *   'SurName'?: string,
     *   'Extensions'?: array,
     *   'EmailAddress'?: array,
     *   'TelephoneNumber'?: array,
     *   'attributes'?: array,
     * }
     */
    public function toArray(): array
    {
        $data = [
            'ContactType' => $this->getContactType()->getValue(),
            'Company' => $this->getCompany()?->getContent()->getValue(),
            'GivenName' => $this->getGivenName()?->getContent()->getValue(),
            'SurName' => $this->getSurName()?->getContent()->getValue(),
            'EmailAddress' => [],
            'TelephoneNumber' => [],
            'Extensions' => $this->Extensions?->getElements(),
            'attributes' => [],
        ];

        foreach ($this->getEmailAddress() as $mail) {
            $data['EmailAddress'] = array_merge($data['EmailAddress'], $mail->toArray());
        }

        foreach ($this->getTelephoneNumber() as $telephone) {
            $data['TelephoneNumber'] = array_merge($data['TelephoneNumber'], $telephone->toArray());
        }

        foreach ($this->getAttributesNS() as $attr) {
            $data['attributes'][] = $attr->toArray();
        }

        return array_filter($data);
    }
}
