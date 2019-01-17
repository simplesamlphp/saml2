<?php

declare(strict_types=1);

namespace SAML2\Compat\Ssp;

use \Psr\Log\LoggerInterface;

use SAML2\Compat\AbstractContainer;

class Container extends AbstractContainer
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
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->logger;
    }


    /**
     * {@inheritdoc}
     * @return string
     */
    public function generateId() : string
    {
        return \SimpleSAML\Utils\Random::generateID();
    }


    /**
     * {@inheritdoc}
     * @return void
     */
    public function debugMessage($message, string $type)
    {
        \SimpleSAML\Utils\XML::debugSAMLMessage($message, $type);
    }


    /**
     * {@inheritdoc}
     * @param string $url
     * @param array $data
     * @return void
     */
    public function redirect(string $url, array $data = [])
    {
        \SimpleSAML\Utils\HTTP::redirectTrustedURL($url, $data);
    }


    /**
     * {@inheritdoc}
     * @param string $url
     * @param array $data
     * @return void
     */
    public function postRedirect(string $url, array $data = [])
    {
        \SimpleSAML\Utils\HTTP::submitPOSTData($url, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function getTempDir()
    {
        return \SimpleSAML\Utils\System::getTempDir();
    }


    /**
     * {@inheritdoc}
     */
    public function writeFile(string $filename, string $data, int $mode = null)
    {
        if ($mode === null) {
            $mode = 0600;
        }
        \SimpleSAML\Utils\System::writeFile($filename, $data, $mode);
    }
}
