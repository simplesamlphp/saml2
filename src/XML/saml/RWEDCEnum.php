<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

enum RWEDCEnum: string
{
    case Read = 'Read';
    case Write = 'Write';
    case Execute = 'Execute';
    case Delete = 'Delete';
    case Control = 'Control';
}
