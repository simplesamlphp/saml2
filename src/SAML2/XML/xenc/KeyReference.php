<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\xenc;

/**
 * Class representing the <xenc:KeyReference> element.
 *
 * @package simplesamlphp/saml2
 */
class KeyReference extends AbstractReference
{
    /**
     * KeyReference constructor.
     *
     * @param string $uri
     * @param \SimpleSAML\XML\Chunk[] $references
     */
    public function __construct(string $uri, array $references = [])
    {
        parent::__construct($uri, $references);
    }
}
