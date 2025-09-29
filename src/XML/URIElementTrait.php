<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\URIElementTrait as BaseURIElementTrait;

/**
 * Trait extending the default URIElementTrait to comply with the restrictions added by the SAML 2.0 specifications.
 *
 * @package simplesamlphp/saml2
 */
trait URIElementTrait
{
    use BaseURIElementTrait;


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        /**
         * 1.3.2 URI Values
         *
         * Unless otherwise indicated in this specification, all URI reference values used within SAML-defined
         * elements or attributes MUST consist of at least one non-whitespace character, and are REQUIRED to be
         * absolute [RFC 2396].
         */
        Assert::notWhitespaceOnly($content, ProtocolViolationException::class);
        SAMLAssert::validURI($content, SchemaViolationException::class);
    }
}
