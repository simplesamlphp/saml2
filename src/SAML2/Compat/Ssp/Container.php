<?php

declare(strict_types=1);

namespace SAML2\Compat\Ssp;

use SAML2\Compat\AbstractContainer;

final class Container extends AbstractContainer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Create a new SimpleSAMLphp compatible container.
     */
    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    public function generateId()
    {
        return \SimpleSAML\Utils\Random::generateID();
    }

    /**
     * {@inheritdoc}
     */
    public function debugMessage(\DOMElement $message, string $type)
    {
        \SimpleSAML\Utils\XML::debugMessage($message, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function redirect(string $url, $data = [])
    {
        \SimpleSAML\Utils\HTTP::redirectTrustedURL($url, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function postRedirect(string $url, $data = [])
    {
        \SimpleSAML\Utils\HTTP::submitPOSTData($url, $data);
    }
}
