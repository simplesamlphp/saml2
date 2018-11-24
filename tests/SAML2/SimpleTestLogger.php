<?php

declare(strict_types=1);

namespace SAML2\Tests;

/**
 * A very simple in-memory logger that allows querying the log for existence of messages
 */
class SimpleTestLogger extends \Psr\Log\AbstractLogger
{
    /**
     * @var array
     */
    private $messages = [];

    public function log($level, $message, array $context = [])
    {
        $this->messages[] = [
            'level'   => $level,
            'message' => $message,
            'context' => $context
        ];
    }

    /**
     * Get all the messages logged at the specified level
     * @param $level
     *
     * @return array
     */
    public function getMessagesForLevel($level)
    {
        return array_filter($this->messages, function ($message) use ($level) {
            return $message['level'] === $level;
        });
    }

    /**
     * Check if the given message exists within the log
     * @param $messageToFind
     *
     * @return bool
     */
    public function hasMessage($messageToFind)
    {
        $count = array_filter($this->messages, function ($message) use ($messageToFind) {
            return $message['message'] === $messageToFind;
        });

        return !!count($count);
    }
}
