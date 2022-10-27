<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_map;
use function array_pop;
use function count;
use function filter_var;
use function preg_replace;
use function var_export;

/**
 * Class representing SAML 2 ContactPerson.
 *
 * @package simplesamlphp/saml2
 */
final class ContactPerson extends AbstractMdElement
{
    use ExtendableElementTrait;
    use ExtendableAttributesTrait;

    /**
     * The several different contact types as defined per specification
     */
    public const CONTACT_TYPES = [
        'technical',
        'support',
        'administrative',
        'billing',
        'other',
    ];

    /**
     * The contact type.
     *
     * @var string
     */
    protected string $contactType;

    /**
     * The Company of this contact.
     *
     * @var \SimpleSAML\SAML2\XML\md\Company|null
     */
    protected ?Company $Company = null;

    /**
     * The GivenName of this contact.
     *
     * @var \SimpleSAML\SAML2\XML\md\GivenName|null
     */
    protected ?GivenName $GivenName = null;

    /**
     * The SurName of this contact.
     *
     * @var \SimpleSAML\SAML2\XML\md\SurName|null
     */
    protected ?SurName $SurName = null;

    /**
     * The EmailAddresses of this contact.
     *
     * @var \SimpleSAML\SAML2\XML\md\EmailAddress[]
     */
    protected array $EmailAddresses = [];

    /**
     * The TelephoneNumbers of this contact.
     *
     * @var \SimpleSAML\SAML2\XML\md\TelephoneNumber[]
     */
    protected array $TelephoneNumbers = [];


    /**
     * ContactPerson constructor.
     *
     * @param string                                      $contactType
     * @param \SimpleSAML\SAML2\XML\md\Company|null       $company
     * @param \SimpleSAML\SAML2\XML\md\GivenName|null     $givenName
     * @param \SimpleSAML\SAML2\XML\md\SurName|null       $surName
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null    $extensions
     * @param \SimpleSAML\SAML2\XML\md\EmailAddress[]     $email
     * @param \SimpleSAML\SAML2\XML\md\TelephoneNumber[]  $telephone
     * @param \DOMAttr[]                                  $namespacedAttributes
     */
    public function __construct(
        string $contactType,
        ?Company $company = null,
        ?GivenName $givenName = null,
        ?SurName $surName = null,
        ?Extensions $extensions = null,
        array $email = [],
        array $telephone = [],
        array $namespacedAttributes = []
    ) {
        $this->setContactType($contactType);
        $this->setCompany($company);
        $this->setGivenName($givenName);
        $this->setSurName($surName);
        $this->setEmailAddresses($email);
        $this->setTelephoneNumbers($telephone);
        $this->setExtensions($extensions);
        $this->setAttributesNS($namespacedAttributes);
    }


    /**
     * Collect the value of the contactType-property
     *
     * @return string
     */
    public function getContactType(): string
    {
        return $this->contactType;
    }


    /**
     * Set the value of the contactType-property
     *
     * @param string $contactType
     * @throws \SimpleSAML\Assert\AssertionFailedException if $contactType is not one of the predefined values
     */
    protected function setContactType(string $contactType): void
    {
        Assert::oneOf($contactType, self::CONTACT_TYPES);
        $this->contactType = $contactType;
    }


    /**
     * Collect the value of the Company-property
     *
     * @return \SimpleSAML\SAML2\XML\md\Company|null
     */
    public function getCompany(): ?Company
    {
        return $this->Company;
    }


    /**
     * Set the value of the Company-property
     *
     * @param \SimpleSAML\SAML2\XML\md\Company|null $company
     */
    protected function setCompany(?Company $company): void
    {
        $this->Company = $company;
    }


    /**
     * Collect the value of the GivenName-property
     *
     * @return \SimpleSAML\SAML2\XML\md\GivenName|null
     */
    public function getGivenName(): ?GivenName
    {
        return $this->GivenName;
    }


    /**
     * Set the value of the GivenName-property
     *
     * @param \SimpleSAML\SAML2\XML\md\GivenName|null $givenName
     */
    protected function setGivenName(?GivenName $givenName): void
    {
        $this->GivenName = $givenName;
    }


    /**
     * Collect the value of the SurName-property
     *
     * @return \SimpleSAML\SAML2\XML\md\SurName|null
     */
    public function getSurName(): ?SurName
    {
        return $this->SurName;
    }


    /**
     * Set the value of the SurName-property
     *
     * @param \SimpleSAML\SAML2\XML\md\SurName|null $surName
     */
    protected function setSurName(?SurName $surName): void
    {
        $this->SurName = $surName;
    }


    /**
     * Collect the value of the EmailAddress-property.
     *
     * @return \SimpleSAML\SAML2\XML\md\EmailAddress[]
     */
    public function getEmailAddresses(): array
    {
        return $this->EmailAddresses;
    }


    /**
     * Set the value of the EmailAddress-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EmailAddress[] $emailAddresses
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setEmailAddresses(array $emailAddresses): void
    {
        Assert::allIsInstanceOf($emailAddresses, EmailAddress::class);
        $this->EmailAddresses = $emailAddresses;
    }


    /**
     * Collect the value of the TelephoneNumber property
     *
     * @return \SimpleSAML\SAML2\XML\md\TelephoneNumber[]
     */
    public function getTelephoneNumbers(): array
    {
        return $this->TelephoneNumbers;
    }


    /**
     * Set the value of the TelephoneNumber property
     *
     * @param \SimpleSAML\SAML2\XML\md\TelephoneNumber[] $telephoneNumbers
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setTelephoneNumbers(array $telephoneNumbers): void
    {
        Assert::allIsInstanceOf($telephoneNumbers, TelephoneNumber::class);
        $this->TelephoneNumbers = $telephoneNumbers;
    }


    /**
     * Initialize a ContactPerson element.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'ContactPerson', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, ContactPerson::NS, InvalidDOMElementException::class);

        $contactType = self::getAttribute($xml, 'contactType');

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
            self::getAttributesNSFromXML($xml)
        );
    }


    /**
     * Convert this ContactPerson to XML.
     *
     * @param \DOMElement|null $parent The element we should add this contact to.
     *
     * @return \DOMElement The new ContactPerson-element.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('contactType', $this->getContactType());

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

        $this->getExtensions()?->toXML($e);
        $this->getCompany()?->toXML($e);
        $this->getGivenName()?->toXML($e);
        $this->getSurName()?->toXML($e);

        foreach ($this->getEmailAddresses() as $mail) {
            $mail->toXML($e);
        }

        foreach ($this->getTelephoneNumbers() as $telephone) {
            $telephone->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): static
    {
        Assert::keyExists($data, 'ContactType');

        $ContactType = $data['ContactType'];
        $Company = isset($data['Company']) ? new Company($data['Company']) : null;
        $GivenName = isset($data['GivenName']) ? new GivenName($data['GivenName']) : null;
        $SurName = isset($data['SurName']) ? new SurName($data['SurName']) : null;
        $Extensions = $data['Extensions'] ?? null;

        $EmailAddresses = [];
        foreach ($data['EmailAddresses'] as $mail) {
            $EmailAddresses[] = new EmailAddress($mail);
        }

        $TelephoneNumbers = [];
        foreach ($data['TelephoneNumbers'] as $telephone) {
            $TelephoneNumbers[] = new TelephoneNumber($telephone);
        }

        // Anything after this should be (namespaced) attributes
        unset(
            $data['ContactType'],
            $data['Company'],
            $data['GivenName'],
            $data['SurName'],
            $data['Extensions'],
            $data['EmailAddresses'],
            $data['TelephoneNumbers']
        );

        $attributes = [];
        foreach ($data as $ns => $attribute) {
            $name = array_key_first($attribute);
            $value = $attribute[$name];

            $doc = DOMDocumentFactory::create();
            $elt = $doc->createElement("placeholder");
            $elt->setAttributeNS($ns, $name, $value);

            $attributes[] = $elt->getAttributeNode($name);
        }

        return new static(
            $ContactType,
            $Company,
            $GivenName,
            $SurName,
            $Extensions,
            $EmailAddresses,
            $TelephoneNumbers,
            $attributes
        );
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'ContactType' => $this->contactType,
            'Company' => $this?->Company->getContent(),
            'GivenName' => $this?->GivenName->getContent(),
            'SurName' => $this?->SurName->getContent(),
            'EmailAddresses' => $this->EmailAddresses,
            'TelephoneNumbers' => $this->TelephoneNumbers,
            'EmailAddresses' => [],
            'TelephoneNumbers' => [],
            'Extensions' => $this->Extensions,
        ];

        foreach ($this->EmailAddresses as $mail) {
            $data['EmailAddresses'] = array_merge($data['EmailAddresses'], $mail->toArray());
        }

        foreach ($this->TelephoneNumbers as $telephone) {
            $data['TelephoneNumbers'] = array_merge($data['TelephoneNumbers'], $telephone->toArray());
        }

        foreach ($this->getAttributesNS() as $a) {
            $data[$a['namespaceURI']] = [$a['qualifiedName'] => $a['value']];
        }

        return $data;
    }
}
