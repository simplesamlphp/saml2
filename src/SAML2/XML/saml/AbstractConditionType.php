<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\XMLStringElementTrait;

/**
 * Abstract class to be implemented by all the conditions in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractConditionType extends AbstractSamlElement
{
    use XMLStringElementTrait;


    /**
     * Initialize a saml:Condition from scratch
     *
     * @param string $value
     */
    protected function __construct(string $value)
    {
        $this->setContent($value);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(/** @scrutinizer ignore-unused */ string $content): void
    {
        Assert::notWhitespaceOnly($content);
    }
}
