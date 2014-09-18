<?php

class SAML2_Certificate_FingerprintLoader
{
    /**
     * Static method mainly for BC, should be replaced with DI.
     *
     * @param SAML2_Configuration_Certifiable $configuration
     *
     * @return SAML2_Certificate_FingerprintCollection
     */
    public static function loadFingerprintsFromConfiguration(SAML2_Configuration_Certifiable $configuration)
    {
        $loader = new self();

        return $loader->loadFromConfigurationValue($configuration->get('certFingerprint'));
    }

    /**
     * Loads the fingerprints from a configurationValue
     *
     * @param $certFingerprint
     *
     * @return SAML2_Certificate_FingerprintCollection
     */
    public function loadFromConfigurationValue($certFingerprint)
    {
        // should be moved to config parsing and an array expected here (or DTO)
        $certFingerprint = $this->normalizeToArray($certFingerprint);

        $collection = new SAML2_Certificate_FingerprintCollection();
        foreach ($certFingerprint as $fingerprint) {
            $collection->add(new SAML2_Certificate_Fingerprint($fingerprint));
        }

        return $collection;
    }

    /**
     * Normalizes a given value to an array containing strings
     *
     * @param string|array $stringOrArray
     *
     * @return array
     */
    private function normalizeToArray($stringOrArray)
    {
        if (!is_string($stringOrArray) && !is_array($stringOrArray)) {
            throw SAML2_Exception_InvalidArgumentException::invalidType('string or array', $stringOrArray);
        }

        $array = $stringOrArray;
        if (is_string($stringOrArray)) {
            $array = array($stringOrArray);
        } else {
            $invalid = array_filter($stringOrArray, function ($value) {
                return !is_string($value);
            });

            if (!empty($invalid)) {
                throw new SAML2_Exception_InvalidArgumentException(
                    'Could not load fingerprints, encountered non-string fingerprints'
                );
            }
        }

        return $array;
    }
}
