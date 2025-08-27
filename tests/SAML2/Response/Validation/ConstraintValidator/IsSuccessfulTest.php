<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\{CoversClass, Group};
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Response\Validation\ConstraintValidator\IsSuccessful;
use SimpleSAML\SAML2\Response\Validation\Result;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\samlp\{Response, Status, StatusCode, StatusMessage};

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(IsSuccessful::class)]
final class IsSuccessfulTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface */
    private MockInterface $response;


    /**
     */
    public function setUp(): void
    {
        $this->response = Mockery::mock(Response::class);
    }


    /**
     */
    #[Group('response-validation')]
    public function testValidatingASuccessfulResponseGivesAValidValidationResult(): void
    {
        $this->response->shouldReceive('isSuccess')->once()->andReturn(true);

        $validator = new IsSuccessful();
        $result    = new Result();

        $validator->validate($this->response, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('response-validation')]
    public function testAnUnsuccessfulResponseIsNotValidAndGeneratesAProperErrorMessage(): void
    {
        $responseStatus = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
                [
                    new StatusCode(
                        SAMLAnyURIVAlue::fromString(C::STATUS_PREFIX . 'bar'),
                    ),
                ],
            ),
            new StatusMessage(
                SAMLStringValue::fromString('this is a test message'),
            ),
        );

        $this->response->shouldReceive('isSuccess')->once()->andReturn(false);
        $this->response->shouldReceive('getStatus')->once()->andReturn($responseStatus);

        $validator = new IsSuccessful();
        $result    = new Result();

        $validator->validate($this->response, $result);
        $errors = $result->getErrors();

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $errors);
        $this->assertEquals('Success/bar this is a test message', $errors[0]);
    }
}
