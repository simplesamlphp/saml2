<?php

declare(strict_types=1);

namespace SAML2;

use DOMDocument;
use SAML2\Exception\InvalidArgumentException;
use SAML2\Exception\RuntimeException;
use SAML2\Exception\UnparseableXmlException;

final class DOMDocumentFactory
{
    /**
     * Constructor for DOMDocumentFactory.
     * This class should never be instantiated
     */
    private function __construct()
    {
    }


    /**
     */
    public static function fromString(string $xml): DOMDocument
    {
        if (trim($xml) === '') {
            throw InvalidArgumentException::invalidType('non-empty string', $xml);
        } elseif (preg_match('/<(\s*)!(\s*)DOCTYPE/', $xml)) {
            throw new RuntimeException(
                'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body'
            );
        } elseif (PHP_VERSION_ID < 80000) {
            $entityLoader = libxml_disable_entity_loader(true);
        } else {
            libxml_set_external_entity_loader(null);
        }

        $internalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $domDocument = self::create();
        $options = LIBXML_NONET | LIBXML_PARSEHUGE;

        /* LIBXML_NO_XXE available from PHP 8.4 */
        if (defined('LIBXML_NO_XXE')) {
            $options |= LIBXML_NO_XXE;
        }

        if (defined('LIBXML_COMPACT')) {
            $options |= LIBXML_COMPACT;
        }

        $loaded = $domDocument->loadXML($xml, $options);
        libxml_use_internal_errors($internalErrors);

        if (!$loaded) {
            $error = libxml_get_last_error();
            libxml_clear_errors();

            throw new UnparseableXmlException($error);
        }

        libxml_clear_errors();

        foreach ($domDocument->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new RuntimeException(
                    'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body'
                );
            }
        }

        return $domDocument;
    }


    /**
     */
    public static function fromFile(string $file): DOMDocument
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf('Path "%s" is not a file', $file));
        }

        if (!is_readable($file)) {
            throw new InvalidArgumentException(sprintf('File "%s" is not readable', $file));
        }

        // libxml_disable_entity_loader(true) disables \DOMDocument::load() method
        // so we need to read the content and use \DOMDocument::loadXML()
        $xml = file_get_contents($file);
        if ($xml === false) {
            throw new RuntimeException(sprintf(
                'Contents of readable file "%s" could not be gotten',
                $file
            ));
        }

        if (trim($xml) === '') {
            throw new RuntimeException(sprintf('File "%s" does not have content', $file));
        }

        return static::fromString($xml);
    }


    /**
     */
    public static function create(): DOMDocument
    {
        return new DOMDocument('1.0', 'UTF-8');
    }
}
