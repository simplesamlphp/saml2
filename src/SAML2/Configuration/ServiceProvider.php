<?php

/**
 * Basic Configuration Wrapper
 */
class SAML2_Configuration_ServiceProvider extends SAML2_Configuration_ArrayAdapter implements
    SAML2_Configuration_Certifiable
{
    public static function load(array $config)
    {
        // validate
        return new self($config);
    }
}
