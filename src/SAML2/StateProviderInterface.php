<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

interface StateProviderInterface
{
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
    public static function loadState(string $id, string $stage, bool $allowMissing = false): ?array;
}
