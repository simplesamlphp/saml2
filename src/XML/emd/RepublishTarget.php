<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\emd;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\XML\StringElementTrait;

/**
 * Class implementing RepublishTarget.
 *
 * @package simplesamlphp/saml2
 */
final class RepublishTarget extends AbstractEmdElement
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
        SAMLAssert::validURI($content);
        Assert::same($content, 'http://edugain.org/');
    }
}
