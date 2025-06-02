<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\{CoversClass, Group};
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, EntityIDValue};
use SimpleSAML\SAML2\XML\saml\{SubjectConfirmation, SubjectConfirmationData};
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\XML\Type\NCNameValue;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(SubjectConfirmationResponseToMatches::class)]
final class SubjectConfirmationResponseToMatchesTest extends MockeryTestCase
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
    #[Group('assertion-validation')]
    public function testWhenTheResponseResponsetoIsNullTheSubjectConfirmationIsValid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturnNull();
        $subjectConfirmationData = new SubjectConfirmationData(
            inResponseTo: NCNameValue::fromString('someIDValue'),
        );
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationResponseToMatches($this->response);
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testWhenTheSubjectconfirmationResponsetoIsNullTheSubjectconfirmationIsValid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn(NCNameValue::fromString('someIDValue'));
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationResponseToMatches($this->response);
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testWhenTheSubjectconfirmationAndResponseResponsetoAreNullTheSubjectconfirmationIsValid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturnNull();
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationResponseToMatches($this->response);
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testWhenTheSubjectConfirmationAndResponseResponsetoAreEqualTheSubjectConfirmationIsValid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn(NCNameValue::fromString('someIDValue'));
        $subjectConfirmationData = new SubjectConfirmationData(
            inResponseTo: NCNameValue::fromString('someIDValue'),
        );
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );

        $validator = new SubjectConfirmationResponseToMatches($this->response);
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     */
    #[Group('assertion-validation')]
    public function testWhenTheSubjectconfirmationAndResponseResponsetoDifferTheSubjectconfirmationIsInvalid(): void
    {
        $this->response->shouldReceive('getInResponseTo')->andReturn(NCNameValue::fromString('someIDValue'));
        $subjectConfirmationData = new SubjectConfirmationData(
            inResponseTo: NCNameValue::fromString('someOtherIDValue'),
        );
        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_HOK),
            null,
            $subjectConfirmationData,
        );
        $validator = new SubjectConfirmationResponseToMatches($this->response);
        $result = new Result();

        $validator->validate($subjectConfirmation, $result);
        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
