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
     * ContactPerson constructor.
     *
     * @param string $contactType
     * @param \SimpleSAML\SAML2\XML\md\Company|null $company
     * @param \SimpleSAML\SAML2\XML\md\GivenName|null $givenName
     * @param \SimpleSAML\SAML2\XML\md\SurName|null $surName
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\XML\md\EmailAddress[] $emailAddress
     * @param \SimpleSAML\SAML2\XML\md\TelephoneNumber[] $telephoneNumber
     * @param \DOMAttr[] $namespacedAttribute
     */
    public function __construct(
        protected string $contactType,
        protected ?Company $company = null,
        protected ?GivenName $givenName = null,
        protected ?SurName $surName = null,
        ?Extensions $extensions = null,
        protected array $emailAddress = [],
        protected array $telephoneNumber = [],
        array $namespacedAttribute = [],
    ) {
        Assert::oneOf($contactType, self::CONTACT_TYPES);
        Assert::allIsInstanceOf($emailAddress, EmailAddress::class);
        Assert::allIsInstanceOf($telephoneNumber, TelephoneNumber::class);

        $this->setExtensions($extensions);
        $this->setAttributesNS($namespacedAttribute);
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
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
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
            self::getAttributesNSFromXML($xml),
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

        $EmailAddress = [];
        foreach ($data['EmailAddress'] as $mail) {
            $EmailAddress[] = new EmailAddress($mail);
        }

        $TelephoneNumber = [];
        foreach ($data['TelephoneNumber'] as $telephone) {
            $TelephoneNumber[] = new TelephoneNumber($telephone);
        }

        // Anything after this should be (namespaced) attributes
        unset(
            $data['ContactType'],
            $data['Company'],
            $data['GivenName'],
            $data['SurName'],
            $data['Extensions'],
            $data['EmailAddress'],
            $data['TelephoneNumber'],
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
            $EmailAddress,
            $TelephoneNumber,
            $attributes,
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
            'ContactType' => $this->getContactType(),
            'Company' => $this->getCompany()?->getContent(),
            'GivenName' => $this->getGivenName()?->getContent(),
            'SurName' => $this->getSurName()?->getContent(),
            'EmailAddress' => [],
            'TelephoneNumber' => [],
            'Extensions' => $this->Extensions,
        ];

        foreach ($this->getEmailAddress() as $mail) {
            $data['EmailAddress'] = array_merge($data['EmailAddress'], $mail->toArray());
        }

        foreach ($this->getTelephoneNumber() as $telephone) {
            $data['TelephoneNumber'] = array_merge($data['TelephoneNumber'], $telephone->toArray());
        }

        /** @psalm-suppress PossiblyNullReference */
        foreach ($this->getAttributesNS() as $a) {
            $data[$a['namespaceURI']] = [$a['qualifiedName'] => $a['value']];
        }

        return $data;
    }
}
