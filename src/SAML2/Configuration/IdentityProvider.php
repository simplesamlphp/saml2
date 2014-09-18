<?php

/**
 * Basic configuration wrapper
 */
class SAML2_Configuration_IdentityProvider extends SAML2_Configuration_ArrayAdapter implements
    SAML2_Configuration_Certifiable
{
    public static function load($config)
    {
        //validate
        return new self($config);
    }
}
