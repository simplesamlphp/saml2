<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\XMLStringElementTrait;

/**
 * Class implementing AffiliateMember.
 *
 * @package simplesamlphp/saml2
 */
final class AffiliateMember extends AbstractMdElement
{
    use XMLStringElementTrait;


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
        Assert::notEmpty($content, 'AffiliateMember cannot be empty');
        Assert::maxLength($content, 1024, 'The entityID attribute cannot be longer than 1024 characters.');
    }
}
