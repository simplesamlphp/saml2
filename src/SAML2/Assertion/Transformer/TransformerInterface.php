<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use SimpleSAML\SAML2\XML\saml\Assertion;

interface TransformerInterface
{
    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     *
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    public function transform(Assertion $assertion): Assertion;
}
