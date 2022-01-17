<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Compat;

use SimpleSAML\Assert\Assert;

class ContainerSingleton
{
    /** @var \SimpleSAML\SAML2\Compat\AbstractContainer */
    protected static AbstractContainer $container;


    /**
     * @return \SimpleSAML\SAML2\Compat\AbstractContainer
     */
    public static function getInstance(): AbstractContainer
    {
        Assert::notNull(self::$container, 'No container set.');
        return self::$container;
    }


    /**
     * Set a container to use.
     *
     * @param \SimpleSAML\SAML2\Compat\AbstractContainer $container
     */
    public static function setContainer(AbstractContainer $container): void
    {
        self::$container = $container;
    }
}
