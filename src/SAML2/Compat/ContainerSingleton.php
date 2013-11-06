<?php

class SAML2_Compat_ContainerSingleton
{
    /**
     * @var Pimple
     */
    protected static $container;

    /**
     * @return Pimple
     */
    public static function getInstance()
    {
        if (!self::$container) {
            self::setContainer(self::initSspContainer());
        }
        return self::$container;
    }

    /**
     * @param Pimple $container
     * @return Pimple
     */
    public function setContainer(Pimple $container)
    {
        self::$container = $container;
        return $container;
    }

    /**
     * @return Pimple
     */
    protected static function initSspContainer()
    {
        $container = new Pimple();

        $container['logger'] = $container->share(function() {
            return new SAML2_Compat_Ssp_Logger();
        });
        $container['id_generator_fn'] = $container->share(function () {
            return function () {
                return SimpleSAML_Utilities::generateID();
            };
        });
        $container['debug_message_fn'] = $container->share(function() {
            return function ($message, $type) {
                SimpleSAML_Utilities::debugMessage($message, $type);
            };
        });
        $container['redirect_fn'] = $container->share(function() {
            return function ($url, $data = array()) {
                SimpleSAML_Utilities:: redirect($url, $data);
            };
        });
        $container['redirect_post_fn'] = $container->share(function () {
            return function ($url, $data = array()) {
                SimpleSAML_Utilities:: postRedirect($url, $data);
            };
        });
        return $container;
    }
}
