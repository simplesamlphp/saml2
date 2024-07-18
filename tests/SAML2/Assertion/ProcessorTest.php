<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion\Decrypter;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\Processor;
use SimpleSAML\SAML2\Assertion\Transformer\TransformerInterface;
use SimpleSAML\SAML2\Assertion\Validation\AssertionValidator;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\XML\DOMDocumentFactory;
use stdClass;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(Processor::class)]
final class ProcessorTest extends MockeryTestCase
{
    private static Processor $processor;
    private static Decrypter $decrypter;


    public static function setUpBeforeClass(): void
    {
        self::$decrypter = Mockery::mock(Decrypter::class);
        /** @var \SimpleSAML\SAML2\Signature\Validator */
        $validator = Mockery::mock(Validator::class);
        /** @var \SimpleSAML\SAML2\Assertion\Validation\AssertionValidator */
        $assertionValidator = Mockery::mock(AssertionValidator::class);
        /** @var \SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator */
        $subjectConfirmationValidator = Mockery::mock(SubjectConfirmationValidator::class);
        /** @var \SimpleSAML\SAML2\Assertion\Transformer\TransformerInterface */
        $transformer = Mockery::mock(TransformerInterface::class);
        $identityProvider = new IdentityProvider([]);
        /** @var \Psr\Log\LoggerInterface */
        $logger = Mockery::mock(LoggerInterface::class);

        self::$processor = new Processor(
            self::$decrypter,
            $validator,
            $assertionValidator,
            $subjectConfirmationValidator,
            $transformer,
            $identityProvider,
            $logger,
        );
    }


    /**
     */
    public function testProcessorCorrectlyEncryptsAssertions(): void
    {
        $encryptedAssertion = EncryptedAssertion::fromXML(
            DOMDocumentFactory::fromFile(
                dirname(__FILE__, 3) . '/resources/xml/saml_EncryptedAssertion.xml',
            )->documentElement,
        );
        $assertion = Assertion::fromXML(
            DOMDocumentFactory::fromFile(
                dirname(__FILE__, 3) . '/resources/xml/saml_Assertion.xml',
            )->documentElement,
        );

        $testData = [
            [$assertion],
            [$encryptedAssertion],
            [$assertion, $encryptedAssertion, $assertion],
            [$encryptedAssertion, $encryptedAssertion, $encryptedAssertion],
        ];

        foreach ($testData as $assertions) {
            self::$decrypter
                ->shouldReceive('decrypt')
                ->andReturn($assertion);

            $collection = new ArrayCollection($assertions);
            $result = self::$processor->decryptAssertions($collection);
            self::assertInstanceOf(ArrayCollection::class, $result);
            foreach ($result as $assertion) {
                self::assertInstanceOf(Assertion::class, $assertion);
            }
        }
    }


    /**
     */
    public function testUnsuportedAssertionsAreRejected(): void
    {
        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('The assertion must be of type: EncryptedAssertion or Assertion');
        self::$processor->decryptAssertions(new ArrayCollection([new stdClass()]));
    }
}
