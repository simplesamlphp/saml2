<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Psr\Log\AbstractLogger;
use SimpleSAML\Assert\Assert;

use function array_filter;
use function count;

/**
 * A very simple in-memory logger that allows querying the log for existence of messages
 *
 * @package simplesamlphp\saml2
 */
final class SimpleTestLogger extends AbstractLogger
{
    /**
     * @var array
     */
    private array $messages = [];


    /**
     */
    public function log($level, $message, array $context = []): void
    {
        Assert::string($level);
        Assert::string($message);

        $this->messages[] = [
            'level'   => $level,
            'message' => $message,
            'context' => $context,
        ];
    }


    /**
     * Get all the messages logged at the specified level
     * @param $level
     *
     * @return array
     */
    public function getMessagesForLevel($level): array
    {
        Assert::string($level);
        return array_filter($this->messages, function (string $message) use ($level) {
            return $message['level'] === $level;
        });
    }


    /**
     * Check if the given message exists within the log
     * @param string $messageToFind
     *
     * @return bool
     */
    public function hasMessage(string $messageToFind): bool
    {
        $count = array_filter($this->messages, function ($message) use ($messageToFind) {
            return $message['message'] === $messageToFind;
        });

        return !!count($count);
    }
}
