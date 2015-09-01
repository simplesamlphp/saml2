<?php

final class SAML2_DOMDocumentFactory
{
    private function __construct()
    {
    }

    /**
     * @param string $xml
     * @return DOMDocument
     */
    public static function fromString($xml)
    {
        if (!is_string($xml)) {
            throw new SAML2_Exception_InvalidArgumentException(sprintf(
                'SAML2_DomDocumentFactory::fromString expects a string as argument, got "%s"',
                (is_object($xml) ? 'instance of ' . get_class($xml) : gettype($xml) )
            ));
        }

        if ('' === trim($xml)) {
            throw new SAML2_Exception_InvalidArgumentException(
                'SAML2_DomDocumentFactory::fromString error: Empty string supplied as input'
            );
        }

        $entityLoader = libxml_disable_entity_loader(true);
        // some parts of the library rely on error-suppression to be able to throw an exception. We do the same here
        // to ensure backwards compatibility
        $internalErrors = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $domDocument = new DOMDocument();
        $loaded = $domDocument->loadXML($xml, LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_NONET | (defined(LIBXML_COMPACT) ? LIBXML_COMPACT : 0));
        if (!$loaded) {
            libxml_disable_entity_loader($entityLoader);

            throw new SAML2_Exception_RuntimeException(implode("\n", static::parseXmlErrors($internalErrors)));
        }

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($entityLoader);

        foreach ($domDocument->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new SAML2_Exception_RuntimeException(
                    'SAML2_DomDocumentFactory::fromString error: Document type is not allowed'
                );
            }
        }

        return $domDocument;
    }

    /**
     * @param $file
     * @return DOMDocument
     */
    public static function fromFile($file)
    {
        if (!is_string($file)) {
            throw new SAML2_Exception_InvalidArgumentException(sprintf(
                'SAML2_DomDocumentFactory::fromFile expects a string as argument, got "%s"',
                (is_object($file) ? 'instance of ' . get_class($file) : gettype($file))
            ));
        }

        if (!is_file($file)) {
            throw new SAML2_Exception_InvalidArgumentException(sprintf(
                'the argument given to SAML2_DomDocumentFactory::fromFile is not a file, got "%s"',
                $file
            ));
        }

        // libxml_disable_entity_loader(true) disables DOMDocument::load() method, so we need to read the content
        // and use DOMDocument::loadXML()
        $xml = @file_get_contents($file);
        if ('' === trim($xml)) {
            throw new SAML2_Exception_InvalidArgumentException(sprintf(
                'SAML2_DomDocumentFactory::fromFile error: Empty file supplied as input: "%s"',
                $file
            ));
        }

        return static::fromString($xml);
    }

    /**
     * @return DOMDocument
     */
    public static function create()
    {
        return new DOMDocument();
    }

    protected static function parseXmlErrors($internalErrors)
    {
        $errors = array();
        foreach(libxml_get_errors() as $error) {
            $errors[] = sprintf(
                'SAML2_DomDocumentFactory::parseXmlErrors error: [%s %s] "%s" in "%s"[%s]',
                $error->level === LIBXML_ERR_WARNING ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ?: '(string)',
                $error->line
            );
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        return $errors;
    }

}
