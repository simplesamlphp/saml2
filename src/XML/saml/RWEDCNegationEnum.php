<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

enum RWEDCNegationEnum: string
{
    case Read = 'Read';
    case Write = 'Write';
    case Execute = 'Execute';
    case Delete = 'Delete';
    case Control = 'Control';
    case NotRead = '~Read';
    case NotWrite = '~Write';
    case NotExecute = '~Execute';
    case NotDelete = '~Delete';
    case NotControl = '~Control';
}
