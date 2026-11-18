<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\emd;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class implementing RepublishTarget.
 *
 * @package simplesamlphp/saml2
 */
final class RepublishTarget extends AbstractEmdElement
{
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = SAMLAnyURIValue::class;


    /**
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $content
     */
    public function __construct(
        SAMLAnyURIValue $content,
    ) {
        $this->setContent($content);
        Assert::same($content->getValue(), 'http://edugain.org/');
    }
}
