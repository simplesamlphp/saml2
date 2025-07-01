<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\subjectid;

enum SignalEnum: string
{
    /**
     * The value MUST be one of the following, signaling the corresponding requirement:
     */

    /**
     * The relying party requires the standard identifier Attribute defined in Section 3.3.
     *
     * - subject-id
     */
    case SUBJECT_ID = 'subject-id';

    /**
     * The relying party requires the pair-wise identifier Attribute defined in Section 3.4.
     *
     * - pairwise-id
     */
    case PAIRWISE_ID = 'pairwise-id';

    /**
     * The relying party does not require any subject identifier and is designed to operate without a
     * specific user identity (e.g., with authorization based on non-identifying data).
     *
     * - none
     */
    case NONE = 'none';

    /**
     * The relying party will accept any of the identifier Attributes defined in this profile but requires at least one.
     *
     * - any
     */
    case ANY = 'any';
}
