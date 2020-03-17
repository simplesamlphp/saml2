<?php

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use Webmozart\Assert\Assert;

final class OneTimeUse extends AbstractConditionType
{
    protected const XSI_TYPE = 'OneTimeUse';


    /**
     * OneTimeUse constructor.
     */
    public function __construct()
    {
        parent::__construct('');
    }


    /**
     * @param \DOMElement $xml
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'OneTimeUse');
        Assert::same($xml->namespaceURI, OneTimeUse::NS);

        return new self();
    }
}
