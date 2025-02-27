<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

enum KeyTypesEnum: string
{
    case SIGNING = 'signing';
    case ENCRYPTION = 'encryption';
}
