<?php

declare(strict_types=1);

namespace SAML2\Compat;

use SimpleSAML\Assert\Assert;

class ContainerSingleton
{
    /**
     * @var \SAML2\Compat\AbstractContainer
     */
    protected static AbstractContainer $container;


    /**
     * @return \SAML2\Compat\AbstractContainer
     */
    public static function getInstance(): AbstractContainer
    {
        Assert::notNull(self::$container, 'No container set.');
        return self::$container;
    }


    /**
     * Set a container to use.
     *
     * @param \SAML2\Compat\AbstractContainer $container
     * @return void
     */
    public static function setContainer(AbstractContainer $container): void
    {
        self::$container = $container;
    }
}
