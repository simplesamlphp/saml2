<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion;

use Mockery as m;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\Transformer\TransformerInterface;
use SimpleSAML\SAML2\Assertion\Validation\AssertionValidator;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use stdClass;

/**
 * @covers \SimpleSAML\SAML2\Assertion\Processor
 * @package simplesamlphp/saml2
 * @runTestsInSeparateProcesses
 */
final class ProcessorTest extends MockeryTestCase
{
    /**
     * @var \SimpleSAML\SAML2\Assertion\Processor
     */
    private Processor $processor;

    /**
     * @var m\MockInterface&Decrypter
     */
    private MockInterface $decrypter;


    protected function setUp(): void
    {
        $this->decrypter = m::mock(Decrypter::class);
        $validator = m::mock(Validator::class);
        $assertionValidator = m::mock(AssertionValidator::class);
        $subjectConfirmationValidator = m::mock(SubjectConfirmationValidator::class);
        $transformer = m::mock(TransformerInterface::class);
        $identityProvider = new IdentityProvider([]);
        $logger = m::mock(LoggerInterface::class);

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
    public function processorCorrectlyEncryptsAssertions(): void
    {
        $encryptedAssertion = m::mock(EncryptedAssertion::class);
        $assertion = m::mock(Assertion::class);

        $testData = [
            [$assertion],
            [$encryptedAssertion],
            [$assertion, $encryptedAssertion, $assertion],
            [$encryptedAssertion, $encryptedAssertion, $encryptedAssertion],
        ];

        foreach ($testData as $assertions) {
            $this->decrypter
                ->shouldReceive('decrypt')
                ->andReturn($assertion);

            $collection = new ArrayCollection($assertions);
            $result = $this->processor->decryptAssertions($collection);
            self::assertInstanceOf(ArrayCollection::class, $result);
            foreach ($result as $assertion) {
                self::assertInstanceOf(Assertion::class, $assertion);
            }
        }
    }

    /**
     * @test
     */
    public function unsuportedAssertionsAreRejected(): void
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('The assertion must be of type: EncryptedAssertion or Assertion');
        $this->processor->decryptAssertions(new ArrayCollection([new stdClass()]));
    }
}
