<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use InvalidArgumentException;

/**
 * Abstract class implementing LocalizedURIType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedURI extends AbstractLocalizedName
{
    /**
     * Set the localized uri.
     *
     * @param string $value
     */
    protected function setValue(string $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(static::getQualifiedName() . ' is not a valid URL.');
        }

        parent::setValue($value);
    }
}
