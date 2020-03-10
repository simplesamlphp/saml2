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
     * @param string $class
     * @psalm-param classstring $class
     * @return void
     */
    public static function registerClass(string $class): void
    {
        Assert::subclassOf($class, AbstractXMLElement::class);

        $key = join(':', [urlencode($class::NS), $class::getClassName()]);
        self::$registry[$key] = $class;
    }


    /**
     * @param string $namespace
     * @param string $element
     * @return string|false
     * @psalm-return class-string|false
     */
    public static function getRegisteredClass(string $namespace, string $element)
    {
        $key = join(':', [urlencode($namespace), $element]);
        Assert::keyExists(
            self::$registry,
            $key,
            'No registered class `' . $element . '` found within given namespace `' . $namespace . '`'
        );

        return self::$registry[$key];
    }
}
