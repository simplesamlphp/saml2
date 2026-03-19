<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;

/**
 * Abstract class representing SAML 2 KeyInfoConfirmationData element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractKeyInfoConfirmationData extends AbstractSubjectConfirmationData
{
    /**
     * Initialize (and parse) a KeyInfoConfirmationData element.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo[] $keyInfo
     */
    public function __construct(
        array $keyInfo = [],
    ) {
        Assert::allIsInstanceOf($keyInfo, KeyInfo::class, SchemaViolationException::class);
        Assert::minCount($keyInfo, 1, MissingElementException::class);

        parent::__construct(null, null, null, null, null, $keyInfo, []);
    }
}
