<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\XMLStringElementTrait;

/**
 * Class implementing DomainHint.
 *
 * @package simplesamlphp/saml2
 */
final class DomainHint extends AbstractMduiElement
{
    use XMLStringElementTrait;


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        Assert::notEmpty($content, 'DomainHint cannot be empty');
    }
}
