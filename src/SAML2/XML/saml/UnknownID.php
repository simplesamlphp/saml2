<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\XML\Chunk;

/**
 * Class for unknown identifiers.
 *
 * @package simplesamlphp/saml2
 */
final class UnknownID extends AbstractBaseID
{
    /** @var \SimpleSAML\XML\Chunk */
    private Chunk $chunk;

    /**
     * @param \SimpleSAML\XML\Chunk $chunk The whole BaseID element as a chunk object.
     * @param string $type The xsi:type of this identifier.
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    public function __construct(
        Chunk $chunk,
        string $type,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null
    ) {
        parent::__construct($type, $NameQualifier, $SPNameQualifier);
        $this->chunk = $chunk;
    }


    /**
     * Get the raw version of this identifier as a Chunk
     *
     * @return \SimpleSAML\XML\Chunk
     */
    public function getRawIdentifier(): Chunk
    {
        return $this->chunk;
    }
}
