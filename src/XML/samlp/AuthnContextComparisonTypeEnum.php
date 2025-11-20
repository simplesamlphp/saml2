<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

enum AuthnContextComparisonTypeEnum: string
{
    /**
     * Request Authentication Context Comparison indicating that  the resulting authentication context in the
     * authentication statement MUST be stronger (as deemed by the responder) than any one of the authentication
     * contexts specified
     */
    case Better = 'better';

    /**
     * Request Authentication Context Comparison indicating that the resulting authentication context in the
     * authentication statement MUST be the exact match of at least one of the authentication contexts specified
     */
    case Exact = 'exact';

    /**
     * Request Authentication Context Comparison indicating that the resulting authentication context in the
     * authentication statement MUST be as strong as possible (as deemed by the responder) without exceeding the
     * strength of at least one of the authentication contexts specified.
     */
    case Maxmimum = 'maximum';

    /**
     * Request Authentication Context Comparison indicating that he resulting authentication context in the
     * authentication statement MUST be at least as strong (as deemed by the responder) as one of the authentication
     * contexts specified.
     */
    case Minimum = 'minimum';
}
