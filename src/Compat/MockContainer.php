<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Compat;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function chmod;
use function file_put_contents;
use function strval;
use function sys_get_temp_dir;

/**
 * Class \SimpleSAML\SAML2\Compat\MockContainer
 */
class MockContainer extends AbstractContainer
{
    /** @var \Psr\Clock\ClockInterface */
    private ClockInterface $clock;

    /** @var array */
    private array $debugMessages = [];


    /**
     * Get a PSR-3 compatible logger.
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return new NullLogger();
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
     * Trigger the user to perform a POST to the given URL with the given data.
     *
     * @param string|null $url
     * @param array $data
     * @return string
     */
    public function getPOSTRedirectURL(
        /** @scrutinizer ignore-unused */string $url = null,
        /** @scrutinizer ignore-unused */array $data = [],
    ): string {
        return strval($url);
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
    public function setBlacklistedAlgorithms(?array $algos): void
    {
        $this->blacklistedEncryptionAlgorithms = [];
    }


    /**
     * Set the system clock
     *
     * @param \Psr\Clock\ClockInterface $clock
     * @return void
     */
    public function setClock(ClockInterface $clock): void
    {
        $this->clock = $clock;
    }


    /**
     * Get the system clock
     *
     * @return \Psr\Clock\ClockInterface
     */
    public function getClock(): ClockInterface
    {
        return $this->clock;
    }
}
