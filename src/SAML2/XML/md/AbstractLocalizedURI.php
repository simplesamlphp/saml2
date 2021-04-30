<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use InvalidArgumentException;
use SimpleSAML\Assert\Assert;

/**
 * Abstract class implementing LocalizedURIType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedURI extends AbstractLocalizedName
{
    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(/** @scrutinizer ignore-unused */ string $content): void
    {
        Assert::notEmpty($content);

        if (!empty($content) && !filter_var($content, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(static::getQualifiedName() . ' is not a valid URL.');
        }
    }
}
