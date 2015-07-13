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

        $domDocument = new DOMDocument();
        // some parts of the library rely on error-suppression to be able to throw an exception. We do the same here
        // to ensure backwards compatibility
        $loaded = @$domDocument->loadXML($xml, LIBXML_DTDLOAD | LIBXML_DTDATTR);
        if (!$loaded) {
            $error = error_get_last();
            throw new SAML2_Exception_RuntimeException(sprintf(
                'Could not load given string as XML into DOMDocument, error: [%s] "%s" in "%s"[%s]',
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            ));
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

        $domDocument = new DOMDocument();
        // some parts of the library rely on error-suppression to be able to throw an exception. We do the same here
        // to ensure backwards compatibility
        $loaded = @$domDocument->load($file, LIBXML_DTDLOAD | LIBXML_DTDATTR);
        if (!$loaded) {
            $error = error_get_last();
            throw new SAML2_Exception_RuntimeException(sprintf(
                'Could not load given string as XML into DOMDocument, error: [%s] "%s" in "%s"[%s]',
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            ));
        }

        return $domDocument;
    }

    /**
     * @return DOMDocument
     */
    public static function create()
    {
        return new DOMDocument();
    }
}
