<?php

declare(strict_types=1);

namespace SAML2\Assertion\Transformer;

use SAML2\XML\saml\Assertion;

interface TransformerInterface
{
    /**
     * @param \SAML2\XML\saml\Assertion $assertion
     *
     * @return \SAML2\XML\saml\Assertion
     */
    public function transform(Assertion $assertion): Assertion;
}
