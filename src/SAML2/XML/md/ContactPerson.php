<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Exception;
use InvalidArgumentException;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\ExtendableAttributes;
use SAML2\XML\ExtendableElement;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 ContactPerson.
 *
 * @package simplesamlphp/saml2
 */
final class ContactPerson extends AbstractMdElement
{
    use ExtendableAttributes;
    use ExtendableElement;

    /**
     * The contact type.
     *
     * @var string
     */
    protected $contactType;

    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    protected $Extensions = [];

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
     * @param string        $contactType
     * @param string|null   $company
     * @param string|null   $givenName
     * @param string|null   $surName
     * @param string[]|null $email
     * @param string[]|null $telephone
     * @param string[]|null $namespacedAttributes
     * @param array|null    $extensions
     */
    public function __construct(
        string $contactType,
        ?string $company = null,
        ?string $givenName = null,
        ?string $surName = null,
        ?array $email = null,
        ?array $telephone = null,
        ?array $namespacedAttributes = null,
        ?array $extensions = null
    ) {
        $this->setContactType($contactType);
        $this->setCompany($company);
        $this->setGivenName($givenName);
        $this->setSurName($surName);
        $this->setEmailAddresses($email);
        $this->setTelephoneNumbers($telephone);
        $this->setAttributesNS($namespacedAttributes);
        $this->setExtensions($extensions);
    }


    /**
     * Initialize a ContactPerson element.
     *
     * @param DOMElement|null $xml The XML element we should load.
     *
     * @return self
     * @throws Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        if (!$xml->hasAttribute('contactType')) {
            throw new Exception('Missing contactType on ContactPerson.');
        }
        $contactType = $xml->getAttribute('contactType');
        $company = self::getStringElement($xml, 'Company');
        $givenName = self::getStringElement($xml, 'GivenName');
        $surName = self::getStringElement($xml, 'SurName');
        $email = self::getStringElements($xml, 'EmailAddress');
        $telephone = self::getStringElements($xml, 'TelephoneNumber');

        return new self(
            $contactType,
            $company,
            $givenName,
            $surName,
            $email,
            $telephone,
            self::getAttributesNSFromXML($xml),
            self::getExtensionsFromXML($xml)
        );
    }


    /**
     * Retrieve the value of a child \DOMElements as an array of strings.
     *
     * @param DOMElement $parent The parent element.
     * @param string      $name The name of the child elements.
     *
     * @return array       The value of the child elements.
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
     * @param DOMElement $parent The parent element.
     * @param string      $name The name of the child element.
     *
     * @return string|null The value of the child element.
     * @throws Exception
     */
    private static function getStringElement(DOMElement $parent, string $name): ?string
    {
        $e = self::getStringElements($parent, $name);
        if (empty($e)) {
            return null;
        }
        if (count($e) > 1) {
            throw new Exception('More than one ' . $name . ' in ' . $parent->tagName);
        }

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
        if (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE) {
            throw new \InvalidArgumentException("Invalid email address for ContactPerson: " . var_export($address, true));
        }
        return $address;
    }

    /**
     * Set the value of the EmailAddress-property
     *
     * @param string[] $emailAddress
     * @return void
     */
    public function setEmailAddress(array $emailAddress): void
    {
        $this->EmailAddress = array_map([$this, 'validateEmailAddress'], $emailAddress);
    }

    /**
     * Add the value to the EmailAddress-property
     *
     * @param string $emailAddress
     * @return void
     */
    public function addEmailAddress(string $emailAddress): void
    {
        $this->EmailAddress[] = $this->validateEmailAddress($emailAddress);
    }

    /**
     * Collect the value of the TelephoneNumber-property
     *
     * @return string[]
     */
    public function getTelephoneNumber(): array
    {
        return $this->TelephoneNumber;
    }


    /**
     * Set the value of the TelephoneNumber-property
     *
     * @param string[] $telephoneNumber
     * @return void
     */
    public function setTelephoneNumber(array $telephoneNumber): void
    {
        $this->TelephoneNumber = $telephoneNumber;
    }


    /**
     * Add the value to the TelephoneNumber-property
     *
     * @param string $telephoneNumber
     * @return void
     */
    public function addTelephoneNumber($telephoneNumber): void
    {
        $this->TelephoneNumber[] = $telephoneNumber;
    }


    /**
     * Collect the value of the Extensions-property
     *
     * @return \SAML2\XML\Chunk[]
     */
    public function getExtensions(): array
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions-property
     *
     * @param array $extensions
     * @return void
     */
    public function setExtensions(array $extensions): void
    {
        $this->Extensions = $extensions;
    }


    /**
     * Add an Extension.
     *
     * @param \SAML2\XML\Chunk $extensions The Extensions
     * @return void
     */
    public function addExtension(Chunk $extension): void
    {
        $this->Extensions[] = $extension;
    }


    /**
     * Collect the value of the ContactPersonAttributes-property
     *
     * @return string[]
     */
    public function getContactPersonAttributes(): array
    {
        return $this->ContactPersonAttributes;
    }


    /**
     * Set the value of the ContactPersonAttributes-property
     *
     * @param string[] $contactPersonAttributes
     * @return void
     */
    public function setContactPersonAttributes(array $contactPersonAttributes): void
    {
        $this->ContactPersonAttributes = $contactPersonAttributes;
    }


    /**
     * Add the key/value of the ContactPersonAttributes-property
     *
     * @param string $attr
     * @param string $value
     * @return void
     */
    public function addContactPersonAttributes(string $attr, string $value): void
    {
        $this->ContactPersonAttributes[$attr] = $value;
    }


    /**
     * Convert this ContactPerson to XML.
     *
     * @param DOMElement $parent The element we should add this contact to.
     *
     * @return DOMElement The new ContactPerson-element.
     *
     * @throws InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        Assert::notEmpty($this->contactType);
        Assert::allEmail($this->EmailAddress);

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_MD, 'md:ContactPerson');
        $parent->appendChild($e);

        $e->setAttribute('contactType', $this->contactType);

        foreach ($this->ContactPersonAttributes as $attr => $val) {
            $e->setAttribute($attr, $val);
        }

        Extensions::addList($e, $this->Extensions);

        if ($this->Company !== null) {
            Utils::addString($e, Constants::NS_MD, 'md:Company', $this->Company);
        }
        if ($this->GivenName !== null) {
            Utils::addString($e, Constants::NS_MD, 'md:GivenName', $this->GivenName);
        }
        if ($this->SurName !== null) {
            Utils::addString($e, Constants::NS_MD, 'md:SurName', $this->SurName);
        }
        if (!empty($this->EmailAddress)) {
            /** @var array $addresses */
            $addresses = preg_filter('/^/', 'mailto:', $this->EmailAddress);
            Utils::addStrings($e, Constants::NS_MD, 'md:EmailAddress', false, $addresses);
        }
        if (!empty($this->TelephoneNumber)) {
            Utils::addStrings($e, Constants::NS_MD, 'md:TelephoneNumber', false, $this->TelephoneNumber);
        }

        return $e;
    }
}
