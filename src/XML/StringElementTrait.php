<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\StringElementTrait as BaseStringElementTrait;

/**
 * Trait extending the default StringElementTrait to comply with the restrictions added by the SAML 2.0 specifications.
 *
 * @package simplesamlphp/saml2
 */
trait StringElementTrait
{
    use BaseStringElementTrait;


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(/** @scrutinizer ignore-unused */ string $content): void
    {
        /**
         * 1.3.1 String Values
         *
         * All SAML string values have the type xs:string, which is built in to the W3C XML Schema Datatypes
         * specification [Schema2]. Unless otherwise noted in this specification or particular profiles, all strings in
         * SAML messages MUST consist of at least one non-whitespace character (whitespace is defined in the
         * XML Recommendation [XML] Section 2.3).
         */
        Assert::notWhitespaceOnly($content, ProtocolViolationException::class);
    }
}
