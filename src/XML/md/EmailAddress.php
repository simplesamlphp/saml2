<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\EmailAddressValue;
use SimpleSAML\XML\{
    ArrayizableElementInterface,
    SchemaValidatableElementInterface,
    SchemaValidatableElementTrait,
    TypedTextContentTrait,
};

use function array_key_first;
use function preg_filter;

/**
 * Class implementing EmailAddress.
 *
 * @package simplesamlphp/saml2
 */
final class EmailAddress extends AbstractMdElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;

    /** @var string */
    public const TEXTCONTENT_TYPE = EmailAddressValue::class;


    /**
     * Get the content of the element.
     *
     * @return string
     */
    public function getContent(): string
    {
        return preg_filter('/^/', 'mailto:', $this->content->getValue());
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
        return new static(
            EmailAddressValue::fromString($data[$index]),
        );
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
