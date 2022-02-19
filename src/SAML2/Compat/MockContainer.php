<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Compat;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function chmod;
use function file_put_contents;
use function sys_get_temp_dir;

/**
 * Class \SimpleSAML\SAML2\Compat\MockContainer
 */
class MockContainer extends AbstractContainer
{
    /**
     * @var string
     */
    private string $id = '123';

    /**
     * @var array
     */
    private array $debugMessages = [];

    /**
     * @var string
     */
    private string $redirectUrl;

    /**
     * @var array
     */
    private array $redirectData = [];

    /**
     * @var string|null
     */
    private ?string $postRedirectUrl = null;

    /**
     * @var array
     */
    private array $postRedirectData;


    /**
     * Get a PSR-3 compatible logger.
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return new NullLogger();
    }


    /**
     * Generate a random identifier for identifying SAML2 documents.
     * @return string
     */
    public function generateId(): string
    {
        return $this->id;
    }


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
    public function debugMessage($message, string $type): void
    {
        $this->debugMessages[$type] = $message;
    }


    /**
     * Trigger the user to perform a GET to the given URL with the given data.
     *
     * @param string $url
     * @param array $data
     */
    public function redirect(string $url, array $data = []): void
    {
        $this->redirectUrl = $url;
        $this->redirectData = $data;
    }


    /**
     * Trigger the user to perform a POST to the given URL with the given data.
     *
     * @param string|null $url
     * @param array $data
     */
    public function postRedirect(string $url = null, array $data = []): void
    {
        $this->postRedirectUrl = $url;
        $this->postRedirectData = $data;
    }


    /**
     * @return string
     */
    public function getTempDir(): string
    {
        return sys_get_temp_dir();
    }


    /**
     * @param string $filename
     * @param string $data
     * @param int|null $mode
     */
    public function writeFile(string $filename, string $data, int $mode = null): void
    {
        if ($mode === null) {
            $mode = 0600;
        }
        file_put_contents($filename, $data);
        chmod($filename, $mode);
    }


    /**
     * @inheritDoc
     */
    public function registerExtensionHandler(string $class): void
    {
    }


    /**
     * @inheritDoc
     */
    public function getElementHandler(string $namespace, string $element): ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getIdentifierHandler(string $type): ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function setBlacklistedAlgorithms(?array $algos): void {
        $this->blacklistedEncryptionAlgorithms = [];
    }
}
