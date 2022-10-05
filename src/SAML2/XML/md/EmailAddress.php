<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\StringElementTrait;

/**
 * Class implementing EmailAddress.
 *
 * @package simplesamlphp/saml2
 */
final class EmailAddress extends AbstractMdElement
{
    use StringElementTrait;


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
        Assert::notEmpty($content, 'EmailAddress cannot be empty');
        Assert::email($content);
    }


    /**
     * Sanitize the content of the element.
     *
     * @param string $content  The unsanitized textContent
     * @throws \Exception on failure
     * @return string
     */
    protected function sanitizeContent(string $content): string
    {
        return rtrim(trim(preg_replace('/^mailto:/i', '', $content)), '`');
    }


    /**
     * Set the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     */
    protected function setContent(string $content): void
    {
        $sanitized = $this->sanitizeContent($content);
        $this->validateContent($sanitized);

        // Store the email address without mailto: URI
        $this->content = $sanitized;
    }


    /**
     * Get the content of the element.
     *
     * @return string
     */
    public function getContent(): string
    {
        return preg_filter('/^/', 'mailto:', $this->content);
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::notEmpty($data);
        Assert::count($data, 1);

        $index = array_key_first($data);
        return new static($data[$index]);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->content];
    }
}
