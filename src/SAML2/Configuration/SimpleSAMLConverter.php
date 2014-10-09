<?php

/**
 * Backwards compatibility helper for SimpleSAMLphp
 */
class SAML2_Configuration_SimpleSAMLConverter
{
    /**
     * @param SimpleSAML_Configuration $configuration
     * @param string                   $prefix
     *
     * @return SAML2_Configuration_IdentityProvider
     */
    public static function convertToServiceProvider(SimpleSAML_Configuration $configuration, $prefix = '')
    {
        return new SAML2_Configuration_IdentityProvider(static::pluckConfiguration($configuration, $prefix));
    }

    /**
     * @param SimpleSAML_Configuration $configuration
     * @param string                   $prefix
     *
     * @return SAML2_Configuration_ServiceProvider
     */
    public static function convertToIdentityProvider(SimpleSAML_Configuration $configuration, $prefix = '')
    {
        $pluckedConfiguration = static::pluckConfiguration($configuration, $prefix);
        static::enrichForServiceProvider($configuration, $pluckedConfiguration);

        return new SAML2_Configuration_ServiceProvider($pluckedConfiguration);
    }

    /**
     * @param SimpleSAML_Configuration $configuration
     * @param string                   $prefix
     *
     * @return array
     */
    private static function pluckConfiguration(SimpleSAML_Configuration $configuration, $prefix = '')
    {
        $extracted = array();

        // ported from simplesamlphp/lib/SimpleSAML/Configuration.php lines 1092-1094
        if ($configuration->hasValue($prefix . 'keys')) {
            $extracted['keys'] = $configuration->getArray($prefix . 'keys');
        }

        // ported from simplesamlphp/lib/SimpleSAML/Configuration.php lines 1108-1109
        if ($configuration->hasValue($prefix . 'certData')) {
            $extracted['certificateData'] = $configuration->getString($prefix . 'certData');
        }

        // ported from simplesamlphp/lib/SimpleSAML/Configuration.php lines 1119-1120
        if ($configuration->hasValue($prefix . 'certificate')) {
            $extracted['certificateData'] = $configuration->getString($prefix . 'certificate');
        }

        // ported from simplesamlphp/modules/lib/Message.php lines 154-155
        if ($configuration->hasValue($prefix . 'certFingerprint')) {
            $extracted['certificateFingerprint'] = $configuration->getArrayizeString('certFingerprint');
        }

        $extracted['assertionEncryptionEnabled'] = $configuration->getBoolean('assertion.encryption', FALSE);

        return $extracted;
    }

    private static function enrichForServiceProvider(SimpleSAML_Configuration $configuration, &$baseConfiguration)
    {
        $baseConfiguration['entityId'] = $configuration->getString('entityid');
    }
}
