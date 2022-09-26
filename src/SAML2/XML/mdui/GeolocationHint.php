<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\Assert\Assert;
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
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        Assert::notEmpty($content, 'GeolocationHint cannot be empty');
        Assert::regex($content, '/^geo:([-+]?\d+(?:\.\d+)?),([-+]?\d+(?:\.\d+)?)(?:\?z=(\d{1,2}))?$/', 'Content is not a valid geolocation');
    }
}
