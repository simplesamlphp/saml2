<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\XML\StringElementTrait;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * Class representing a saml:AssertionIDRef element.
 *
 * @package simplesaml/saml2
 */
final class AssertionIDRef extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use StringElementTrait {
        StringElementTrait::validateContent as baseValidateContent;
    }


    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->setContent($content);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        $this->baseValidateContent($content);
        Assert::validNCName($content, SchemaViolationException::class);
    }
}
