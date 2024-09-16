<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;
use SimpleSAML\XML\StringElementTrait;

use function filter_var;
use function preg_replace;
use function rtrim;
use function sprintf;

/**
 * Class implementing DomainHint.
 *
 * @package simplesamlphp/saml2
 */
final class DomainHint extends AbstractMduiElement
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
     * Sanitize the content of the element.
     *
     * @param string $content  The unsanitized textContent
     * @throws \Exception on failure
     * @return string
     */
    protected function sanitizeContent(string $content): string
    {
        // Remove prefixed schema and/or trailing whitespace + forward slashes
        return rtrim(preg_replace('#^http[s]?://#i', '', $content), " \n\r\t\v\x00/");
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
        $sanitizedContent = $this->sanitizeContent($content);
        Assert::notEmpty($sanitizedContent, 'DomainHint cannot be empty');

        if (!filter_var($sanitizedContent, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new InvalidArgumentException(sprintf('DomainHint is not a valid hostname;  %s', $sanitizedContent));
        }
    }
}
