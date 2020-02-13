<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 SubjectConfirmationData element.
 *
 * @package SimpleSAMLphp
 */
final class SubjectConfirmationData extends AbstractSamlElement
{
    /**
     * The time before this element is valid, as an unix timestamp.
     *
     * @var int|null
     */
    protected $NotBefore = null;

    /**
     * The time after which this element is invalid, as an unix timestamp.
     *
     * @var int|null
     */
    protected $NotOnOrAfter = null;

    /**
     * The Recipient this Subject is valid for. Either an entity or a location.
     *
     * @var string|null
     */
    protected $Recipient = null;

    /**
     * The ID of the AuthnRequest this is a response to.
     *
     * @var string|null
     */
    protected $InResponseTo = null;

    /**
     * The IP(v6) address of the user.
     *
     * @var string|null
     */
    protected $Address = null;

    /**
     * The various key information elements.
     *
     * Array with various elements describing this key.
     * Unknown elements will be represented by \SAML2\XML\Chunk.
     *
     * @var (\SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk)[]
     */
    protected $info = [];


    /**
     * Initialize (and parse) a SubjectConfirmationData element.
     *
     * @param int|null $notBefore
     * @param int|null $notOnOrAfter
     * @param string|null $recipient
     * @param string|null $inResponseTo
     * @param string|null $address
     * @param (\SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk)[] $info
     */
    public function __construct(
        ?int $notBefore = null,
        ?int $notOnOrAfter = null,
        ?string $recipient = null,
        ?string $inResponseTo = null,
        ?string $address = null,
        array $info = []
    ) {
        $this->setNotBefore($notBefore);
        $this->setNotOnOrAfter($notOnOrAfter);
        $this->setRecipient($recipient);
        $this->setInResponseTo($inResponseTo);
        $this->setAddress($address);
        $this->setInfo($info);
    }


    /**
     * Collect the value of the NotBefore-property
     *
     * @return int|null
     */
    public function getNotBefore(): ?int
    {
        return $this->NotBefore;
    }


    /**
     * Set the value of the NotBefore-property
     *
     * @param int|null $notBefore
     * @return void
     */
    private function setNotBefore(?int $notBefore): void
    {
        $this->NotBefore = $notBefore;
    }


    /**
     * Collect the value of the NotOnOrAfter-property
     *
     * @return int|null
     */
    public function getNotOnOrAfter(): ?int
    {
        return $this->NotOnOrAfter;
    }


    /**
     * Set the value of the NotOnOrAfter-property
     *
     * @param int|null $notOnOrAfter
     * @return void
     */
    private function setNotOnOrAfter(?int $notOnOrAfter): void
    {
        $this->NotOnOrAfter = $notOnOrAfter;
    }


    /**
     * Collect the value of the Recipient-property
     *
     * @return string|null
     */
    public function getRecipient(): ?string
    {
        return $this->Recipient;
    }


    /**
     * Set the value of the Recipient-property
     *
     * @param string|null $recipient
     * @return void
     */
    private function setRecipient(?string $recipient): void
    {
        $this->Recipient = $recipient;
    }


    /**
     * Collect the value of the InResponseTo-property
     *
     * @return string|null
     */
    public function getInResponseTo(): ?string
    {
        return $this->InResponseTo;
    }


    /**
     * Set the value of the InResponseTo-property
     *
     * @param string|null $inResponseTo
     * @return void
     */
    private function setInResponseTo(?string $inResponseTo): void
    {
        $this->InResponseTo = $inResponseTo;
    }


    /**
     * Collect the value of the Address-property
     *
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->Address;
    }


    /**
     * Set the value of the Address-property
     *
     * @param string|null $address
     * @return void
     */
    private function setAddress(?string $address): void
    {
        if (!is_null($address) && !filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            Utils::getContainer()->getLogger()->warning(
                sprintf('Provided argument (%s) is not a valid IP address.', $address)
            );
        }
        $this->Address = $address;
    }


    /**
     * Collect the value of the info-property
     *
     * @return (\SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk)[]
     */
    public function getInfo(): array
    {
        return $this->info;
    }


    /**
     * Set the value of the info-property
     *
     * @param (\SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk)[] $info
     * @return void
     */
    private function setInfo(array $info): void
    {
        Assert::allIsInstanceOfAny($info, [Chunk::class, KeyInfo::class]);
        $this->info = $info;
    }


    /**
     * Add the value to the info-property
     *
     * @param \SAML2\XML\Chunk|\SAML2\XML\ds\KeyInfo $info
     * @return void
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function addInfo(object $info): void
    {
        Assert::isInstanceOfAny($info, [Chunk::class, KeyInfo::class]);
        $this->info[] = $info;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return (
            empty($this->NotBefore)
            && empty($this->NotOnOrAfter)
            && empty($this->Recipient)
            && empty($this->InResponseTo)
            && empty($this->Address)
            && empty($this->info)
        );
    }


    /**
     * Convert XML into a SubjectConfirmationData
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'SubjectConfirmationData');
        Assert::same($xml->namespaceURI, SubjectConfirmationData::NS);

        $NotBefore = $xml->hasAttribute('NotBefore')
            ? Utils::xsDateTimeToTimestamp($xml->getAttribute('NotBefore'))
            : null;

        $NotOnOrAfter = $xml->hasAttribute('NotOnOrAfter')
            ? Utils::xsDateTimeToTimestamp($xml->getAttribute('NotOnOrAfter'))
            : null;

        $Recipient = self::getAttribute($xml, 'Recipient', null);
        $InResponseTo = self::getAttribute($xml, 'InResponseTo', null);
        $Address = self::getAttribute($xml, 'Address', null);

        $info = [];
        foreach ($xml->childNodes as $n) {
            if (!($n instanceof DOMElement)) {
                continue;
            } elseif ($n->namespaceURI !== XMLSecurityDSig::XMLDSIGNS) {
                $info[] = new Chunk($n);
                continue;
            }

            switch ($n->localName) {
                case 'KeyInfo':
                    $info[] = KeyInfo::fromXML($n);
                    break;
                default:
                    $info[] = new Chunk($n);
                    break;
            }
        }

        return new self(
            $NotBefore,
            $NotOnOrAfter,
            $Recipient,
            $InResponseTo,
            $Address,
            $info
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement|null $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->NotBefore !== null) {
            $e->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', $this->NotBefore));
        }
        if ($this->NotOnOrAfter !== null) {
            $e->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->NotOnOrAfter));
        }
        if ($this->Recipient !== null) {
            $e->setAttribute('Recipient', $this->Recipient);
        }
        if ($this->InResponseTo !== null) {
            $e->setAttribute('InResponseTo', $this->InResponseTo);
        }
        if ($this->Address !== null) {
            $e->setAttribute('Address', $this->Address);
        }

        foreach ($this->info as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
