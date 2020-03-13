<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Utilities\Temporal;
use SAML2\Utils;
use SAML2\XML\saml\Issuer;
use Webmozart\Assert\Assert;

/**
 * Base class for all SAML 2 request messages.
 *
 * Implements samlp:RequestAbstractType. All of the elements in that type is
 * stored in the \SAML2\XML\AbstractMessage class, and this class is therefore empty. It
 * is included mainly to make it easy to separate requests from responses.
 *
 * @package SimpleSAMLphp
 */
abstract class AbstractRequest extends AbstractMessage
{
    /**
     * Initialize a message.
     *
     * This constructor takes an optional parameter with a \DOMElement. If this
     * parameter is given, the message will be initialized with data from that
     * XML element.
     *
     * If no XML element is given, the message is initialized with suitable
     * default values.
     *
     * @param string $tagName The tag name of the root element
     * @param \DOMElement|null $xml The input message
     *
     * @throws \Exception
     */
    protected function __construct(string $tagName, DOMElement $xml = null)
    {
        $this->tagName = $tagName;

        $this->id = Utils::getContainer()->generateId();
        $this->issueInstant = Temporal::getTime();

        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('ID')) {
            throw new \Exception('Missing ID attribute on SAML message.');
        }
        $this->id = $xml->getAttribute('ID');

        if ($xml->getAttribute('Version') !== '2.0') {
            /* Currently a very strict check. */
            throw new \Exception('Unsupported version: ' . $xml->getAttribute('Version'));
        }

        $this->issueInstant = Utils::xsDateTimeToTimestamp($xml->getAttribute('IssueInstant'));

        if ($xml->hasAttribute('Destination')) {
            $this->destination = $xml->getAttribute('Destination');
        }

        if ($xml->hasAttribute('Consent')) {
            $this->consent = $xml->getAttribute('Consent');
        }

        /** @var \DOMElement[] $issuer */
        $issuer = Utils::xpQuery($xml, './saml_assertion:Issuer');
        if (!empty($issuer)) {
            $this->issuer = Issuer::fromXML($issuer[0]);
        }

        $this->validateSignature($xml);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one saml:Extensions element is allowed.');
        if (!empty($extensions)) {
            $this->Extensions = $extensions[0];
        }
    }


    /**
     * Create a class from XML
     *
     * @param \DOMElement $xml
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        return new self(/** Dummy method **/);
    }
}
