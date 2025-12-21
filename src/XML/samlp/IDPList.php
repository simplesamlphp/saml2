<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;

use function array_change_key_case;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_pop;

/**
 * Class for handling SAML2 IDPList.
 *
 * @package simplesamlphp/saml2
 */
final class IDPList extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize an IDPList element.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\IDPEntry[] $IDPEntry
     * @param \SimpleSAML\SAML2\XML\samlp\GetComplete|null $getComplete
     */
    public function __construct(
        protected array $IDPEntry,
        protected ?GetComplete $getComplete = null,
    ) {
        Assert::maxCount($IDPEntry, C::UNBOUNDED_LIMIT);
        Assert::minCount($IDPEntry, 1, 'At least one samlp:IDPEntry must be specified.');
        Assert::allIsInstanceOf($IDPEntry, IDPEntry::class);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\IDPEntry[]
     */
    public function getIdpEntry(): array
    {
        return $this->IDPEntry;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\GetComplete|null
     */
    public function getGetComplete(): ?GetComplete
    {
        return $this->getComplete;
    }


    /**
     * Convert XML into a IDPList-element
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'IDPList', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, IDPList::NS, InvalidDOMElementException::class);

        $idpEntry = IDPEntry::getChildrenOfClass($xml);
        Assert::minCount(
            $idpEntry,
            1,
            'At least one <samlp:IDPEntry> must be specified.',
            MissingElementException::class,
        );

        $getComplete = GetComplete::getChildrenOfClass($xml);
        Assert::maxCount(
            $getComplete,
            1,
            'Only one <samlp:GetComplete> element is allowed.',
            TooManyElementsException::class,
        );

        return new static(
            $idpEntry,
            empty($getComplete) ? null : array_pop($getComplete),
        );
    }


    /**
     * Convert this IDPList to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getIDPEntry() as $idpEntry) {
            $idpEntry->toXML($e);
        }

        $this->getGetComplete()?->toXML($e);

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array{
     *   'IDPEntry': string,
     *   'GetComplete'?: string,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            $data['IDPEntry'],
            $data['GetComplete'] ?? null,
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'IDPEntry': string,
     *   'GetComplete'?: string,
     * } $data
     * @return array{
     *   'IDPEntry': string,
     *   'GetComplete'?: string,
     * }
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'idpentry',
                'getcomplete',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'idpentry', ArrayValidationException::class);
        Assert::isArray($data['idpentry'], ArrayValidationException::class);

        $retval = ['IDPEntry' => [], 'GetComplete' => null];

        foreach ($data['idpentry'] as $entry) {
            $retval['IDPEntry'][] = IDPEntry::fromArray($entry);
        }

        if (array_key_exists('getcomplete', $data)) {
            $retval['GetComplete'] = GetComplete::fromArray($data['getcomplete']);
        }

        return $retval;
    }


    /**
     * Create an array from this class
     *
     * @return array{
     *   'IDPEntry': string,
     *   'GetComplete'?: string,
     * }
     */
    public function toArray(): array
    {
        $data = [
            'IDPEntry' => [],
            'GetComplete' => $this->getGetComplete()?->toArray(),
        ];

        foreach ($this->getIDPEntry() as $entry) {
            $data['IDPEntry'][] = $entry->toArray();
        }

        return array_filter($data);
    }
}
