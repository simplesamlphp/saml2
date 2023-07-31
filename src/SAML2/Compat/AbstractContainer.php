<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Compat;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\ExtensionPointInterface;
use SimpleSAML\XML\AbstractElement;

use function array_key_exists;
use function implode;
use function is_subclass_of;

abstract class AbstractContainer
{
    /** @var string */
    protected const XSI_TYPE_PREFIX = '<xsi:type>';

    /** @var array */
    protected array $registry = [];

    /** @var array */
    protected $blacklistedEncryptionAlgorithms = [];


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
     * Register a class that can handle given extension points of the standard.
     *
     * @param string $class The class name of a class extending AbstractElement or implementing ExtensionPointInterface.
     * @psalm-param class-string $class
     */
    public function registerExtensionHandler(string $class): void
    {
        Assert::subclassOf($class, AbstractElement::class);

        if (is_subclass_of($class, ExtensionPointInterface::class, true)) {
            $key = implode(':', [self::XSI_TYPE_PREFIX, $class::getXsiTypeNamespaceURI(), $class::getXsiTypeName()]);
        } else {
            $className = AbstractElement::getClassName($class);
            $key = ($class::NS === null) ? $className : implode(':', [$class::NS, $className]);
        }

        $this->registry[$key] = $class;
    }


    /**
     * Search for a class that implements a custom element type.
     *
     * Such classes must have been registered previously by calling registerExtensionHandler(), and they must
     * implement \SimpleSAML\SAML2\XML\saml\ExtensionPointInterface.
     *
     * @param string $type The type of the identifier (xsi:type of a BaseID element).
     *
     * @return string|null The fully-qualified name of a class implementing
     *  \SimpleSAML\SAML2\XML\saml\ExtensionPointInterface or null if no such class has been registered before.
     * @psalm-return class-string|null
     */
    public function getExtensionHandler(string $type): ?string
    {
        Assert::notEmpty($type, 'Cannot search for identifier handlers with an empty type.');

        $type = implode(':', [self::XSI_TYPE_PREFIX, $type]);
        if (!array_key_exists($type, $this->registry)) {
            return null;
        }

        Assert::implementsInterface($this->registry[$type], ExtensionPointInterface::class);
        return $this->registry[$type];
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
     * @return void
     */
    abstract public function debugMessage($message, string $type): void;


    /**
     * Trigger the user to perform a POST to the given URL with the given data.
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    abstract public function getpostRedirectURL(string $url, array $data = []): string;


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
     * @return void
     */
    abstract public function writeFile(string $filename, string $data, int $mode = null): void;


    /**
     * Set the list of algorithms that are blacklisted for any encryption operation.
     *
     * @param string[]|null $algos An array with all algorithm identifiers that are blacklisted,
     * or null if we want to use the defaults.
     */
    abstract public function setBlacklistedAlgorithms(?array $algos): void;


    /**
     * Get the system clock, using UTC for a timezone
     */
    abstract public function getClock(): ClockInterface;
}
