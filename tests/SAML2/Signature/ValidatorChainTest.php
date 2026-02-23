<?php

declare(strict_types=1);

namespace SAML2\Signature;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Configuration\IdentityProvider;
use SAML2\Signature\ValidatorChain;
use SAML2\Response;
use SAML2\Signature\MissingConfigurationException;

class ValidatorChainTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \SAML2\Signature\ValidatorChain
     */
    private $chain;


    /**
     */
    public function setUp(): void
    {
        $this->chain = new ValidatorChain(new \Psr\Log\NullLogger(), []);
    }


    /**
     * @group signature
     */
    #[Test]
    public function ifNoValidatorsCanValidateAnExceptionIsThrown(): void
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(false, true));

        $this->expectException(MissingConfigurationException::class);
        $this->chain->hasValidSignature(new Response(), new IdentityProvider([]));
    }


    /**
     * @group signature
     */
    #[Test]
    public function allRegisteredValidatorsShouldBeTried(): void
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(true, false));

        $validationResult = $this->chain->hasValidSignature(
            new Response(),
            new IdentityProvider([])
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }


    /**
     * @group signature
     */
    #[Test]
    public function itUsesTheResultOfTheFirstValidatorThatCanValidate(): void
    {
        $this->chain->appendValidator(new MockChainedValidator(false, true));
        $this->chain->appendValidator(new MockChainedValidator(true, false));
        $this->chain->appendValidator(new MockChainedValidator(false, true));

        $validationResult = $this->chain->hasValidSignature(
            new Response(),
            new IdentityProvider([])
        );
        $this->assertFalse($validationResult, 'The validation result is not what is expected');
    }
}
