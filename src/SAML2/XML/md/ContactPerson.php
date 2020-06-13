<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Exception;
use InvalidArgumentException;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\ExtendableAttributesTrait;
use SAML2\XML\ExtendableElementTrait;
use SimpleSAML\Assert\Assert;

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
     * The contact type.
     *
     * @var string
     */
    protected $contactType;

    /**
     * The Company of this contact.
     *
     * @var string|null
     */
    protected $Company = null;

    /**
     * The GivenName of this contact.
     *
     * @var string|null
     */
    protected $GivenName = null;

    /**
     * The SurName of this contact.
     *
     * @var string|null
     */
    protected $SurName = null;

    /**
     * The EmailAddresses of this contact.
     *
     * @var array
     */
    protected $EmailAddresses = [];

    /**
     * The TelephoneNumbers of this contact.
     *
     * @var array
     */
    protected $TelephoneNumbers = [];


    /**
     * ContactPerson constructor.
     *
     * @param string                         $contactType
     * @param string|null                    $company
     * @param string|null                    $givenName
     * @param string|null                    $surName
     * @param \SAML2\XML\md\Extensions|null  $extensions
     * @param string[]                       $email
     * @param string[]                       $telephone
     * @param \DOMAttr[]                     $namespacedAttributes
     */
    public function __construct(
        string $contactType,
        ?string $company = null,
        ?string $givenName = null,
        ?string $surName = null,
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
     * Initialize a ContactPerson element.
     *
     * @param \DOMElement $xml The XML element we should load.
     *
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     * @throws \InvalidArgumentException if the mandatory contactType attribute is missing
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'ContactPerson');
        Assert::same($xml->namespaceURI, ContactPerson::NS);
        Assert::true($xml->hasAttribute('contactType'), 'Missing contactType on ContactPerson.');

        $contactType = $xml->getAttribute('contactType');
        $company = self::getStringElement($xml, 'Company');
        $givenName = self::getStringElement($xml, 'GivenName');
        $surName = self::getStringElement($xml, 'SurName');
        $email = self::getStringElements($xml, 'EmailAddress');
        $telephone = self::getStringElements($xml, 'TelephoneNumber');
        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        return new self(
            $contactType,
            $company,
            $givenName,
            $surName,
            (count($extensions) === 1) ? $extensions[0] : null,
            $email,
            $telephone,
            self::getAttributesNSFromXML($xml)
        );
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
        $e = Utils::xpQuery($parent, './saml_metadata:' . $name);

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
     * @throws \InvalidArgumentException
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
     * @return void
     * @throws \InvalidArgumentException if $contactType is not one of the predefined values
     */
    protected function setContactType(string $contactType): void
    {
        Assert::oneOf($contactType, ['technical', 'support', 'administrative', 'billing', 'other']);
        $this->contactType = $contactType;
    }


    /**
     * Collect the value of the Company-property
     *
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->Company;
    }


    /**
     * Set the value of the Company-property
     *
     * @param string|null $company
     * @return void
     */
    protected function setCompany(?string $company): void
    {
        $this->Company = $company;
    }


    /**
     * Collect the value of the GivenName-property
     *
     * @return string|null
     */
    public function getGivenName(): ?string
    {
        return $this->GivenName;
    }


    /**
     * Set the value of the GivenName-property
     *
     * @param string|null $givenName
     * @return void
     */
    protected function setGivenName(?string $givenName): void
    {
        $this->GivenName = $givenName;
    }


    /**
     * Collect the value of the SurName-property
     *
     * @return string|null
     */
    public function getSurName(): ?string
    {
        return $this->SurName;
    }


    /**
     * Set the value of the SurName-property
     *
     * @param string|null $surName
     * @return void
     */
    protected function setSurName(?string $surName): void
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
     * @return void
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException
     */
    protected function setTelephoneNumbers(array $telephoneNumbers): void
    {
        Assert::allString($telephoneNumbers, 'Incorrect type for telephone number.');
        $this->TelephoneNumbers = $telephoneNumbers;
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
            Utils::addString($e, Constants::NS_MD, 'md:Company', $this->Company);
        }
        if ($this->GivenName !== null) {
            Utils::addString($e, Constants::NS_MD, 'md:GivenName', $this->GivenName);
        }
        if ($this->SurName !== null) {
            Utils::addString($e, Constants::NS_MD, 'md:SurName', $this->SurName);
        }

        /** @var string[] $addresses */
        $addresses = preg_filter('/^/', 'mailto:', $this->EmailAddresses);
        Utils::addStrings($e, Constants::NS_MD, 'md:EmailAddress', false, $addresses);

        Utils::addStrings($e, Constants::NS_MD, 'md:TelephoneNumber', false, $this->TelephoneNumbers);

        return $e;
    }
}
