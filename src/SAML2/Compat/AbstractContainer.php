<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Compat;

use Psr\Log\LoggerInterface;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\SAML2\XML\saml\Condition;
use SimpleSAML\SAML2\XML\saml\CustomIdentifierInterface;

use function array_key_exists;
use function is_subclass_of;
use function join;
use function urlencode;

abstract class AbstractContainer
{
    /** @var array */
    protected array $registry;

    /** @var array|null */
    protected ?array $blacklistedEncryptionAlgorithms;


    /**
     * Set the list of algorithms that are blacklisted for any encryption operation.
     *
     * @param string[]|null $algos An array with all algorithm identifiers that are blacklisted,
     * or null if we want to use the defaults.
     */
    abstract public function setBlacklistedAlgorithms(?array $algos): void;


    /**
     * Get the list of algorithms that are blacklisted for any encryption operation.
     *
     * @return string[]|null An array with all algorithm identifiers that are blacklisted, or null if we want to use the
     * defaults.
     */
    public function getBlacklistedEncryptionAlgorithms(): ?array
    {
        return $this->blacklistedEncryptionAlgorithms;
    }


    /**
     * Get a PSR-3 compatible logger.
     * @return \Psr\Log\LoggerInterface
     */
    abstract public function getLogger(): LoggerInterface;


    /**
     * Generate a random identifier for identifying SAML2 documents.
     * @return string
     */
    abstract public function generateId(): string;


    /**
     * Log an incoming message to the debug log.
     *
     * Type can be either:
     * - **in** XML received from third party
     * - **out** XML that will be sent to third party
     * - **encrypt** XML that is about to be encrypted
     * - **decrypt** XML that was just decrypted
     *
     * @param \DOMElement|string $message
     * @param string $type
     */
    abstract public function debugMessage($message, string $type): void;


    /**
     * Trigger the user to perform a GET to the given URL with the given data.
     *
     * @param string $url
     * @param array $data
     */
    abstract public function redirect(string $url, array $data = []): void;


    /**
     * Trigger the user to perform a POST to the given URL with the given data.
     *
     * @param string $url
     * @param array $data
     */
    abstract public function postRedirect(string $url, array $data = []): void;


    /**
     * This function retrieves the path to a directory where temporary files can be saved.
     *
     * @throws \Exception If the temporary directory cannot be created or it exists and does not belong
     * to the current user.
     * @return string Path to a temporary directory, without a trailing directory separator.
     */
    abstract public function getTempDir(): string;


    /**
     * Atomically write a file.
     *
     * This is a helper function for writing data atomically to a file. It does this by writing the file data to a
     * temporary file, then renaming it to the required file name.
     *
     * @param string $filename The path to the file we want to write to.
     * @param string $data The data we should write to the file.
     * @param int $mode The permissions to apply to the file. Defaults to 0600.
     */
    abstract public function writeFile(string $filename, string $data, int $mode = null): void;


    /**
     * Register a class that can handle given extension points of the standard.
     *
     * @param string $class The class name of a class extending AbstractXMLElement or BaseID.
     * @psalm-param class-string $class
     */
    public function registerExtensionHandler(string $class): void
    {
        Assert::subclassOf($class, AbstractXMLElement::class);
        if (is_subclass_of($class, CustomIdentifierInterface::class, true)) {
            $key = $class::getXsiType() . ':BaseID';
        } else {
            $key = join(':', [urlencode($class::NS), AbstractXMLElement::getClassName($class)]);
        }
        $this->registry[$key] = $class;
    }


    /**
     * Search for a class that implements an $element in the given $namespace.
     *
     * Such classes must have been registered previously by calling registerExtensionHandler(), and they must
     * extend \SimpleSAML\XML\AbstractXMLElement.
     *
     * @param string $namespace The namespace URI for the given element.
     * @param string $element The local name of the element.
     *
     * @return string|null The fully-qualified name of a class extending \SimpleSAML\XML\AbstractXMLElement and
     * implementing support for the given element, or null if no such class has been registered before.
     * @psalm-return class-string|null
     */
    public function getElementHandler(string $namespace, string $element): ?string
    {
        Assert::notEmpty($namespace, 'Cannot search for handlers without an associated namespace URI.');
        Assert::notEmpty($element, 'Cannot search for handlers without an associated element name.');
        return $this->registry[join(':', [urlencode($namespace), $element])];
    }


    /**
     * Search for a class that implements a custom identifier type.
     *
     * Such classes must have been registered previously by calling registerExtensionHandler(), and they must
     * implement \SimpleSAML\SAML2\XML\saml\CustomIdentifierInterface.
     *
     * @param string $type The type of the identifier (xsi:type of the BaseID element).
     *
     * @return string|null The fully-qualified name of a class implementing \SimpleSAML\SAML2\XML\saml\CustomIdentifierInterface
     * or null if no such class has been registered before.
     * @psalm-return class-string|null
     */
    public function getIdentifierHandler(string $type): ?string
    {
        Assert::notEmpty($type, 'Cannot search for identifier handlers with an empty type.');
        $handler = $type . ':BaseID';
        return array_key_exists($handler, $this->registry) ? $this->registry[$handler] : null;
    }
}
