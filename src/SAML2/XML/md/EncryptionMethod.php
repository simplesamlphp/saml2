<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\XMLSecurity\XML\xenc\AbstractEncryptionMethod;

/**
 * A class implementing the md:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
class EncryptionMethod extends AbstractEncryptionMethod
{
    /** @var string */
    public const NS = Constants::NS_MD;

    /** @var string */
    public const NS_PREFIX = 'md';


    /*
     * EncryptionMethod constructor.
     *
     * @param string $algorithm
     * @param int|null $keySize
     * @param string|null $oaepParams
     * @param \SimpleSAML\XML\Chunk[] $children
     */
    public function __construct(
        string $algorithm,
        ?int $keySize = null,
        ?string $oaepParams = null,
        array $children = []
    ) {
        parent::__construct($algorithm, $keySize, $oaepParams, $children);
    }
}
