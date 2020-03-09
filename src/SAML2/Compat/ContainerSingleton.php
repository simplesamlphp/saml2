<?php

declare(strict_types=1);

namespace SAML2\Compat;

use SAML2\Compat\Ssp\Container;
use SAML2\XML\AbstractXMLElement;
use Webmozart\Assert\Assert;

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
     * @param class-string $class
     * @return void
     */
    public static function registerClass(string $class): void
    {
        Assert::subclassOf($class, AbstractXMLElement::class);

        self::$registry[$class] = [$class::NS, $class::getClassName()];
    }


    /**
     * @param string $namespace
     * @param string $element
     * @return class-string|false
     */
    public static function getRegisteredClass(string $namespace, string $element)
    {
        return array_search([$namespace, $element], self::$registry);
    }
}
