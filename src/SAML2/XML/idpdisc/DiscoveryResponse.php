<?php

declare(strict_types=1);

namespace SAML2\XML\idpdisc;

use DOMElement;

use SAML2\Constants;
use SAML2\XML\md\IndexedEndpointType;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 idpdisc:DiscoveryResponse.
 *
 * @package SimpleSAMLphp
 */
class DiscoveryResponse extends IndexedEndpointType
{
    /**
     * Initialize an IndexedEndpointType.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(?DOMElement $xml = null)
    {
        parent::__construct($xml);
    }


    /**
     * Set the value of the Binding property.
     *
     * @param string $binding
     * @return void
     */
    public function setBinding(string $binding) : void
    {
        Assert::same($binding, Constants::NS_IDPDISC);

        parent::setBinding($binding);
    }


    /**
     * Add this endpoint to an XML element.
     *
     * @param \DOMElement $parent The element we should append this endpoint to.
     * @param string $name The name of the element we should create.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent, string $name) : DOMElement
    {
        return $this->toXMLInternal($parent, Constants::NS_IDPDISC, 'idpdisc:DiscoveryResponse');
    }
}
