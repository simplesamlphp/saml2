<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use Exception;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
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
     * @var string[]
     */
    protected array $EmailAddresses = [];

    /**
     * The TelephoneNumbers of this contact.
     *
     * @var string[]
     */
    protected array $TelephoneNumbers = [];


    /**
     * ContactPerson constructor.
     *
     * @param string                                    $contactType
     * @param \SimpleSAML\SAML2\XML\md\Company|null     $company
     * @param \SimpleSAML\SAML2\XML\md\GivenName|null   $givenName
     * @param \SimpleSAML\SAML2\XML\md\SurName|null     $surName
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null  $extensions
     * @param string[]                                  $email
     * @param string[]                                  $telephone
     * @param \DOMAttr[]                                $namespacedAttributes
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
     * Retrieve the value of a child \DOMElements as an array of strings.
     *
     * @param \DOMElement $parent The parent element.
     * @param string      $name The name of the child elements.
     *
     * @return string[]   The value of the child elements.
     */
    private static function getStringElements(DOMElement $parent, string $name): array
    {
        $e = XMLUtils::xpQuery($parent, './saml_metadata:' . $name);

        $ret = [];
        foreach ($e as $i) {
            $ret[] = $i->textContent;
        }

        return $ret;
    }


    /**
     * Retrieve the value of a child \DOMElement as a string.
     *
     * @param \DOMElement $parent The parent element.
     * @param string      $name The name of the child element.
     *
     * @return string|null The value of the child element.
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    private static function getStringElement(DOMElement $parent, string $name): ?string
    {
        $e = self::getStringElements($parent, $name);
        if (empty($e)) {
            return null;
        }

        Assert::maxCount($e, 1, 'More than one ' . $name . ' in ' . $parent->tagName);
        return $e[0];
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
     * @return string[]
     */
    public function getEmailAddresses(): array
    {
        return $this->EmailAddresses;
    }

    /**
     * Remove a "mailto:" prefix on an email address, if present.
     * Check the address for syntactical validity. If not, throw an exception.
     *
     * @param string $emailAddress
     * @return string
     * @throws \InvalidArgumentException if supplied email address is not valid
     */
    private function validateEmailAddress(string $emailAddress): string
    {
        $address = preg_replace('/^mailto:/i', '', $emailAddress);
        if (filter_var($address, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("Invalid email address for ContactPerson: " . var_export($address, true));
        }
        return $address;
    }

    /**
     * Set the value of the EmailAddress-property
     *
     * @param string[] $emailAddresses
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setEmailAddresses(array $emailAddresses): void
    {
        $addresses = array_map([$this, 'validateEmailAddress'], $emailAddresses);
        Assert::allEmail($addresses, 'Invalid email addresses found.');
        $this->EmailAddresses = $addresses;
    }


    /**
     * Collect the value of the TelephoneNumber property
     *
     * @return string[]
     */
    public function getTelephoneNumbers(): array
    {
        return $this->TelephoneNumbers;
    }


    /**
     * Set the value of the TelephoneNumber property
     *
     * @param string[] $telephoneNumbers
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setTelephoneNumbers(array $telephoneNumbers): void
    {
        Assert::allString($telephoneNumbers, 'Incorrect type for telephone number.');
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
    public static function fromXML(DOMElement $xml): object
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

        $email = self::getStringElements($xml, 'EmailAddress');
        $telephone = self::getStringElements($xml, 'TelephoneNumber');
        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        return new self(
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

        $e->setAttribute('contactType', $this->contactType);

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

        if ($this->Extensions !== null) {
            $this->Extensions->toXML($e);
        }

        if ($this->Company !== null) {
            $this->Company->toXML($e);
        }

        if ($this->GivenName !== null) {
            $this->GivenName->toXML($e);
        }
        if ($this->SurName !== null) {
            $this->SurName->toXML($e);
        }

        $addresses = preg_filter('/^/', 'mailto:', $this->EmailAddresses);
        XMLUtils::addStrings($e, Constants::NS_MD, 'md:EmailAddress', false, $addresses);

        XMLUtils::addStrings($e, Constants::NS_MD, 'md:TelephoneNumber', false, $this->TelephoneNumbers);

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): object
    {
        Assert::keyExists($data, 'ContactType');

        $ContactType = $data['ContactType'];
        $Company = isset($data['Company']) ? new Company($data['Company']) : null;
        $GivenName = isset($data['GivenName']) ? new GivenName($data['GivenName']) : null;
        $SurName = isset($data['SurName']) ? new SurName($data['SurName']) : null;
        $Extensions = $data['Extensions'] ?? null;
        $EmailAddresses = $data['EmailAddresses'] ?? [];
        $TelephoneNumbers = $data['TelephoneNumbers'] ?? [];

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

        return new self(
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
            'Company' => $this->Company->getContent(),
            'GivenName' => $this->GivenName->getContent(),
            'SurName' => $this->SurName->getContent(),
            'EmailAddresses' => $this->EmailAddresses,
            'TelephoneNumbers' => $this->TelephoneNumbers,
            'Extensions' => $this->Extensions,
        ];

        foreach ($this->getAttributesNS() as $a) {
            $data[$a['namespaceURI']] = [$a['qualifiedName'] => $a['value']];
        }

        return $data;
    }
}
