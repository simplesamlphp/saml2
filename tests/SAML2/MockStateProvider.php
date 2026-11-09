<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use SimpleSAML\SAML2\StateProviderInterface;

final class MockStateProvider implements StateProviderInterface
{
    /** @var array|null $state */
    protected static ?array $state;


    /**
     * Save the state.
     *
     * This function saves the state, and returns an id which can be used to
     * retrieve it later.
     *
     * @param array  &$state The login request state.
     * @param string $stage The current stage in the login process.
     * @param bool   $rawId Return a raw ID, without a restart URL.
     *
     * @return string  Identifier which can be used to retrieve the state later.
     */
    public static function saveState(array &$state, string $stage, bool $rawId = false): string
    {
        self::$state['PHPUnit'][$stage] = $state;
        return 'PHPUnit';
    }


    /**
     * Retrieve saved state.
     *
     * This function retrieves saved state information. If the state information has been lost,
     * it will attempt to restart the request by calling the restart URL which is embedded in the
     * state information. If there is no restart information available, an exception will be thrown.
     *
     * @param string $id            State identifier (with embedded restart information).
     * @param string $stage         The stage the state should have been saved in.
     * @param bool   $allowMissing  Whether to allow the state to be missing.
     *
     * @return array|null           State information, or NULL if the state is missing and $allowMissing is true.
     * @psalm-return ($allowMissing is true ? array|null : array)
     */
    public static function loadState(string $id, string $stage, bool $allowMissing = false): ?array
    {
        return self::$state[$id][$stage];
    }
}
