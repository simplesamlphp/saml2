<?php

declare(strict_types=1);

namespace SAML2\Compat;

use SAML2\Compat\Ssp\Container;

class ContainerSingleton
{
    /** @var \SAML2\Compat\ContainerInterface|null */
    protected static $container = null;

    /** @var array */
    protected static $registry = [];


    /**
     * @return \SAML2\Compat\ContainerInterface
     */
    public static function getInstance(): ContainerInterface
    {
        if (!isset(self::$container)) {
            self::$container = self::initSspContainer();
        }
        return self::$container;
    }


    /**
     * Set a container to use.
     *
     * @param \SAML2\Compat\ContainerInterface $container
     * @return void
     */
    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }


    /**
     * @return \SAML2\Compat\Ssp\Container
     */
    public static function initSspContainer(): Container
    {
        return new Container();
    }


    /**
     * @param string $className
     * @param array $qualifiedName
     * @return void
     */
    public static function registerClass(string $className, array $qualifiedName): void
    {
        $this->registry[$qualifiedName] = $className;
    }


    /**
     * @param string $className
     * @return array|false
     */
    public static function getRegisteredClass(string $className)
    {
        return array_search($className, $this->registry);
    }
}
