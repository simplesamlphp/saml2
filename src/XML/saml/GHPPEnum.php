<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

enum GHPPEnum: string
{
    case GET = 'GET';
    case HEAD = 'HEAD';
    case PUT = 'PUT';
    case POST = 'POST';
}
