<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use SimpleSAML\SAML2\Assertion;

interface Transformer
{
    /**
     * @param \SimpleSAML\SAML2\Assertion $assertion
     *
     * @return \SimpleSAML\SAML2\Assertion
     */
    public function transform(Assertion $assertion): Assertion;
}
