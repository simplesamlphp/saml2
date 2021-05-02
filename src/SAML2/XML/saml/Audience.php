<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class representing a saml:Audience element.
 *
 * @package simplesaml/saml2
 */
final class Audience extends AbstractConditionType
{
    /**
     * Initialize an Audience element.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->setContent($value);
    }
}

