<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use Webmozart\Assert\Assert;

/**
 * Class for SAML 2 Response messages.
 *
 * @package SimpleSAMLphp
 */
class Response extends AbstractStatusResponse
{
    /**
     * The assertions in this response.
     *
     * @var (\SAML2\Assertion|\SAML2\EncryptedAssertion)[]
     */
    private $assertions = [];


    /**
     * Constructor for SAML 2 response messages.
     *
     * @param \DOMElement|null $xml The input message.
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('Response', $xml);

        if ($xml === null) {
            return;
        }

        foreach ($xml->childNodes as $node) {
            if ($node->namespaceURI !== Constants::NS_SAML) {
                continue;
            } elseif (!($node instanceof DOMElement)) {
                continue;
            }

            if ($node->localName === 'Assertion') {
                $this->assertions[] = new Assertion($node);
            } elseif ($node->localName === 'EncryptedAssertion') {
                $this->assertions[] = new EncryptedAssertion($node);
            }
        }
    }


    /**
     * Retrieve the assertions in this response.
     *
     * @return \SAML2\Assertion[]|\SAML2\EncryptedAssertion[]
     */
    public function getAssertions(): array
    {
        return $this->assertions;
    }


    /**
     * Set the assertions that should be included in this response.
     *
     * @param \SAML2\Assertion[]|\SAML2\EncryptedAssertion[] $assertions The assertions.
     * @return void
     */
    public function setAssertions(array $assertions): void
    {
        $this->assertions = $assertions;
    }


    /**
     * Convert XML into a Response
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\Response
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
    }


    /**
     * Convert the response message to an XML element.
     *
     * @return \DOMElement This response.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        Assert::null($parent);

        $parent = parent::toXML();

        foreach ($this->assertions as $assertion) {
            $assertion->toXML($parent);
        }

        return $parent;
    }
}
