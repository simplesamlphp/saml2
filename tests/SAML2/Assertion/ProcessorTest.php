<?php

declare(strict_types=1);

namespace SAML2\Assertion;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @runTestsInSeparateProcesses
 */
class ProcessorTest extends MockeryTestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var m\MockInterface&Decrypter
     */
    private $decrypter;

    protected function setUp(): void
    {
        $this->decrypter = m::mock(Decrypter::class);
        $validator = m::mock(\SAML2\Signature\Validator::class);
        $assertionValidator = m::mock(\SAML2\Assertion\Validation\AssertionValidator::class);
        $subjectConfirmationValidator = m::mock(\SAML2\Assertion\Validation\SubjectConfirmationValidator::class);
        $transformer = m::mock(\SAML2\Assertion\Transformer\Transformer::class);
        $identityProvider = new \SAML2\Configuration\IdentityProvider([]);
        $logger = m::mock(\Psr\Log\LoggerInterface::class);

        $this->processor = new Processor(
            $this->decrypter,
            $validator,
            $assertionValidator,
            $subjectConfirmationValidator,
            $transformer,
            $identityProvider,
            $logger
        );
    }

    /**
     * @test
     */
    public function processor_correctly_encrypts_assertions(): void
    {
        $testData = [
            [new \SAML2\Assertion()],
            [new \SAML2\EncryptedAssertion()],
            [new \SAML2\Assertion(), new \SAML2\EncryptedAssertion(), new \SAML2\Assertion()],
            [new \SAML2\EncryptedAssertion(), new \SAML2\EncryptedAssertion(), new \SAML2\EncryptedAssertion()],
        ];

        foreach ($testData as $assertions) {
            $this->decrypter
                ->shouldReceive('decrypt')
                ->andReturn(new \SAML2\Assertion());

            $collection = new \SAML2\Utilities\ArrayCollection($assertions);
            $result = $this->processor->decryptAssertions($collection);
            self::assertInstanceOf(\SAML2\Utilities\ArrayCollection::class, $result);
            foreach ($result as $assertion) {
                self::assertInstanceOf(\SAML2\Assertion::class, $assertion);
            }
        }
    }

    /**
     * @test
     */
    public function unsuported_assertions_are_rejected(): void
    {
        $this->expectException('\SAML2\Assertion\Exception\InvalidAssertionException');
        $this->expectExceptionMessage('The assertion must be of type: EncryptedAssertion or Assertion');
        $this->processor->decryptAssertions(new \SAML2\Utilities\ArrayCollection([new \stdClass()]));
    }
}
