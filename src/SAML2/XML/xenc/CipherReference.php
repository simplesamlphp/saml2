<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\xenc;

/**
 * Class representing the <xenc:CipherReference> element.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class CipherReference extends AbstractReference
{
    /**
     * CipherReference constructor.
     *
     * @param string $uri
     * @param \SAML2\XML\Chunk[] $references
     */
    public function __construct(string $uri, array $references = [])
    {
        parent::__construct($uri, $references);
    }
}
