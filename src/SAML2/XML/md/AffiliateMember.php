<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\StringElementTrait;

/**
 * Class implementing AffiliateMember.
 *
 * @package simplesamlphp/saml2
 */
final class AffiliateMember extends AbstractMdElement
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
        Assert::maxLength(
            $content,
            C::ENTITYID_MAX_LENGTH,
            sprintf('The AffiliateMember cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH),
            ProtocolViolationException::class,
        );
    }
}
