<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

use function filter_var;

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
    protected function validateContent(string $content): void
    {
        parent::validateContent($content);

        Assert::false(
            !empty($content) && !filter_var($content, FILTER_VALIDATE_URL),
            static::getQualifiedName() . ' is not a valid URL.',
            ProtocolViolationException::class,
        );
    }
}
