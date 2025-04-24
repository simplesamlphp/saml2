<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\XML\URIElementTrait;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

use function array_key_first;

/**
 * Class representing a samlp:RequesterID element.
 *
 * @package simplesaml/saml2
 */
final class RequesterID extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use URIElementTrait;


    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->setContent($content);
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::allString($data, ArrayValidationException::class);

        $index = array_key_first($data);
        return new static($data[$index]);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->getContent()];
    }
}
