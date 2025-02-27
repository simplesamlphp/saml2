<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Compat;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\XML\ExtensionPointInterface;
use SimpleSAML\XML\{AbstractElement, ElementInterface};
use SimpleSAML\XML\Type\QNameValue;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;

use function array_key_exists;
use function constant;

abstract class AbstractContainer
{
    /** @var array */
    protected array $registry = [];

    /** @var array */
    protected array $extRegistry = [];

    /** @var array|null */
    protected ?array $blacklistedEncryptionAlgorithms = [
        EncryptionAlgorithmFactory::DEFAULT_BLACKLIST,
        KeyTransportAlgorithmFactory::DEFAULT_BLACKLIST,
        SignatureAlgorithmFactory::DEFAULT_BLACKLIST,
    ];


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
     * Register a class that can handle a given element.
     *
     * @param string $class The class name of a class extending AbstractElement
     * @psalm-param class-string $class
     */
    public function registerElementHandler(string $class): void
    {
        Assert::subclassOf($class, AbstractElement::class);
        $key = '{' . constant($class::NS) . '}' . AbstractElement::getClassName($class);
        $this->registry[$key] = $class;
    }


    /**
     * Register a class that can handle given extension points of the standard.
     *
     * @param string $class The class name of a class extending AbstractElement or implementing ExtensionPointInterface.
     * @psalm-param class-string $class
     */
    public function registerExtensionHandler(string $class): void
    {
        Assert::subclassOf($class, ExtensionPointInterface::class);
        $key = '{' . $class::getXsiTypeNamespaceURI() . '}' . $class::getXsiTypeName();
        $this->extRegistry[$key] = $class;
    }


    /**
     * Search for a class that implements an element in the given $namespace.
     *
     * Such classes must have been registered previously by calling registerExtensionHandler(), and they must
     * extend \SimpleSAML\XML\AbstractElement.
     *
     * @param \SimpleSAML\XML\Type\QNameValue|null $qName The qualified name of the element.
     *
     * @return string|null The fully-qualified name of a class extending \SimpleSAML\XML\AbstractElement and
     * implementing support for the given element, or null if no such class has been registered before.
     * @psalm-return class-string|null
     */
    public function getElementHandler(QNameValue $qName): ?string
    {
        $key = '{' . $qName->getNameSpaceURI()->getValue() . '}' . $qName->getLocalName()->getValue();
        if (array_key_exists($key, $this->registry) === true) {
            Assert::implementsInterface($this->registry[$key], ElementInterface::class);
            return $this->registry[$key];
        }

        return null;
    }


    /**
     * Search for a class that implements a custom element type.
     *
     * Such classes must have been registered previously by calling registerExtensionHandler(), and they must
     * implement \SimpleSAML\SAML11\XML\saml\ExtensionPointInterface.
     *
     * @param \SimpleSAML\XML\Type\QNameValue $qName The qualified name of the extension.
     * @return string|null The fully-qualified name of a class implementing
     *  \SimpleSAML\SAML11\XML\saml\ExtensionPointInterface or null if no such class has been registered before.
     * @psalm-return class-string|null
     */
    public function getExtensionHandler(QNameValue $qName): ?string
    {
        $key = '{' . $qName->getNameSpaceURI()->getValue() . '}' . $qName->getLocalName()->getValue();
        if (array_key_exists($key, $this->extRegistry) === true) {
            Assert::implementsInterface($this->extRegistry[$key], ExtensionPointInterface::class);
            return $this->extRegistry[$key];
        }

        return null;
    }


    /**
     * Set the list of algorithms that are blacklisted for any encryption operation.
     *
     * @param string[]|null $algos An array with all algorithm identifiers that are blacklisted,
     * or null if we want to use the defaults.
     */
    abstract public function setBlacklistedAlgorithms(?array $algos): void;


    /**
     * Get a PSR-3 compatible logger.
     * @return \Psr\Log\LoggerInterface
     */
    abstract public function getLogger(): LoggerInterface;


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
     * Trigger the user to perform a POST to the given URL with the given data.
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    abstract public function getPOSTRedirectURL(string $url, array $data = []): string;


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
     * @param int|null $mode The permissions to apply to the file. Defaults to 0600.
     */
    abstract public function writeFile(string $filename, string $data, ?int $mode = null): void;


    /**
     * Get the system clock, using UTC for a timezone
     */
    abstract public function getClock(): ClockInterface;
}
