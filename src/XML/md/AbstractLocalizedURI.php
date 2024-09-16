<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;

/**
 * Abstract class implementing LocalizedURIType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedURI extends AbstractLocalizedName
{
    /**
     * Set the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     */
    protected function setContent(string $content): void
    {
        $this->validateContent($content);
        $this->content = $content;
    }


    /**
     * Get the content of the element.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->sanitizeContent($this->getRawContent());
    }


    /**
     * Get the raw and unsanitized content of the element.
     *
     * @return string
     */
    public function getRawContent(): string
    {
        return $this->content;
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
        // We've seen metadata in the wild that had stray whitespace around URIs, causing assertions to fail
        return trim($content);
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
        SAMLAssert::validURI($this->sanitizeContent($content));
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::count($data, 1);

        $lang = array_key_first($data);
        Assert::stringNotEmpty($lang);

        $value = $data[$lang];
        Assert::stringNotEmpty($value);
        SAMLAssert::validURI($value);

        return new static($lang, $value);
    }
}
