<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

/**
 * Valid values for saml:DecisionType
 */
enum Decision: string
{
    case PERMIT = 'Permit';
    case DENY = 'Deny';
    case INDETERMINATE = 'Indeterminate';
}
