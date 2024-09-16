<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\XML\StringElementTrait;

/**
 * Class implementing GeolocationHint.
 *
 * @package simplesamlphp/saml2
 */
final class GeolocationHint extends AbstractMduiElement
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
     * Set the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     */
    protected function setContent(string $content): void
    {
        $sanitized = $this->sanitizeContent($content);
        $this->validateContent($sanitized);

        // Store the email address with any whitespace removed
        $this->content = $sanitized;
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
        return preg_replace('/\s+/', '', $content);
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
        Assert::notEmpty($content, 'GeolocationHint cannot be empty');
        // Assert::regex(
        //     $content,
        //     '/^geo:([-+]?\d+(?:\.\d+)?),([-+]?\d+(?:\.\d+)?)(?:\?z=(\d{1,2}))?$/',
        //     'Content is not a valid geolocation:  %s'
        // );
        // The regex above is incomplete, so for now we only test for a valid URI
        SAMLAssert::validURI($content);
    }
}
