<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use Webmozart\Assert\Assert;
use RobRichards\XMLSecLibs\XMLSecurityDSig;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;

/**
 * Class representing SAML 2 SubjectConfirmationData element.
 *
 * @package SimpleSAMLphp
 */
class SubjectConfirmationData
{
    /**
     * The time before this element is valid, as an unix timestamp.
     *
     * @var int|null
     */
    private $NotBefore = null;

    /**
     * The time after which this element is invalid, as an unix timestamp.
     *
     * @var int|null
     */
    private $NotOnOrAfter = null;

    /**
     * The Recipient this Subject is valid for. Either an entity or a location.
     *
     * @var string|null
     */
    private $Recipient = null;

    /**
     * The ID of the AuthnRequest this is a response to.
     *
     * @var string|null
     */
    private $InResponseTo = null

    /**
     * The IP(v6) address of the user.
     *
     * @var string|null
     */
    private $Address = null;

    /**
     * The various key information elements.
     *
     * Array with various elements describing this key.
     * Unknown elements will be represented by \SAML2\XML\Chunk.
     *
     * @var (\SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk)[]
     */
    private $info = [];


    /**
     * Collect the value of the NotBefore-property
     * @return int|null
     */
    public function getNotBefore()
    {
        return $this->NotBefore;
    }


    /**
     * Set the value of the NotBefore-property
     * @param int|null $notBefore
     * @return void
     */
    public function setNotBefore(int $notBefore = null)
    {
        $this->NotBefore = $notBefore;
    }


    /**
     * Collect the value of the NotOnOrAfter-property
     * @return int|null
     */
    public function getNotOnOrAfter()
    {
        return $this->NotOnOrAfter;
    }


    /**
     * Set the value of the NotOnOrAfter-property
     * @param int|null $notOnOrAfter
     * @return void
     */
    public function setNotOnOrAfter(int $notOnOrAfter = null)
    {
        $this->NotOnOrAfter = $notOnOrAfter;
    }


    /**
     * Collect the value of the Recipient-property
     * @return string|null
     */
    public function getRecipient()
    {
        return $this->Recipient;
    }


    /**
     * Set the value of the Recipient-property
     * @param string|null $recipient
     * @return void
     */
    public function setRecipient(string $recipient = null)
    {
        $this->Recipient = $recipient;
    }


    /**
     * Collect the value of the InResponseTo-property
     * @return string|null
     */
    public function getInResponseTo()
    {
        return $this->InResponseTo;
    }


    /**
     * Set the value of the InResponseTo-property
     * @param string|null $inResponseTo
     * @return void
     */
    public function setInResponseTo(string $inResponseTo = null)
    {
        $this->InResponseTo = $inResponseTo;
    }


    /**
     * Collect the value of the Address-property
     * @return string|null
     */
    public function getAddress()
    {
        return $this->Address;
    }


    /**
     * Set the value of the Address-property
     * @param string|null $address
     * @return void
     */
    public function setAddress(string $address = null)
    {
        if (!is_null($address) && !filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            throw new \InvalidArgumentException('Provided argument is not a valid IP address.');
        }
        $this->Address = $address;
    }


    /**
     * Collect the value of the info-property
     * @return (\SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk)[]
     */
    public function getInfo() : array
    {
        return $this->info;
    }


    /**
     * Set the value of the info-property
     * @param (\SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk)[] $info
     * @return void
     */
    public function setInfo(array $info)
    {
        $this->info = $info;
    }


    /**
     * Add the value to the info-property
     * @param \SAML2\XML\Chunk|\SAML2\XML\ds\KeyInfo $info
     * @return void
     */
    public function addInfo($info)
    {
        Assert::isInstanceOfAny($info, [Chunk::class, KeyInfo::class]);
        $this->info[] = $info;
    }


    /**
     * Initialize (and parse) a SubjectConfirmationData element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('NotBefore')) {
            $this->setNotBefore(Utils::xsDateTimeToTimestamp($xml->getAttribute('NotBefore')));
        }
        if ($xml->hasAttribute('NotOnOrAfter')) {
            $this->setNotOnOrAfter(Utils::xsDateTimeToTimestamp($xml->getAttribute('NotOnOrAfter')));
        }
        if ($xml->hasAttribute('Recipient')) {
            $this->setRecipient($xml->getAttribute('Recipient'));
        }
        if ($xml->hasAttribute('InResponseTo')) {
            $this->setInResponseTo($xml->getAttribute('InResponseTo'));
        }
        if ($xml->hasAttribute('Address')) {
            $this->setAddress($xml->getAttribute('Address'));
        }
        for ($n = $xml->firstChild; $n !== null; $n = $n->nextSibling) {
            if (!($n instanceof \DOMElement)) {
                continue;
            }
            if ($n->namespaceURI !== XMLSecurityDSig::XMLDSIGNS) {
                $this->addInfo(new Chunk($n));
                continue;
            }
            switch ($n->localName) {
                case 'KeyInfo':
                    $this->addInfo(new KeyInfo($n));
                    break;
                default:
                    $this->addInfo(new Chunk($n));
                    break;
            }
        }
    }


    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(\DOMElement $parent)
    {
        $e = $parent->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:SubjectConfirmationData');
        $parent->appendChild($e);

        if ($this->getNotBefore() !== null) {
            $e->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', $this->getNotBefore()));
        }
        if ($this->getNotOnOrAfter() !== null) {
            $e->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->getNotOnOrAfter()));
        }
        if ($this->getRecipient() !== null) {
            $e->setAttribute('Recipient', $this->getRecipient());
        }
        if ($this->getInResponseTo() !== null) {
            $e->setAttribute('InResponseTo', $this->getInResponseTo());
        }
        if ($this->getAddress() !== null) {
            $e->setAttribute('Address', $this->getAddress());
        }
        /** @var \SAML2\XML\ds\KeyInfo|\SAML2\XML\Chunk $n */
        foreach ($this->getInfo() as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
