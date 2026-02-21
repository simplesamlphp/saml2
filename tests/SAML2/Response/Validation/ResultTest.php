<?php

declare(strict_types=1);

namespace SAML2\Response\Validation;

use PHPUnit\Framework\Attributes\Test;
use SAML2\Response\Validation\Result;

class ResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group response-validation
     * @return void
     */
    #[Test]
    public function added_errors_can_be_retrieved() : void
    {
        $error = 'This would be an error message';
        $result = new Result();

        $result->addError($error);
        $errors = $result->getErrors();

        $this->assertCount(1, $errors);
        $this->assertEquals($error, $errors[0]);
    }


    /**
     * @group response-validation
     * @return void
     */
    #[Test]
    public function the_result_correctly_reports_whether_or_not_it_is_valid() : void
    {
        $result = new Result();

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getErrors());

        $result->addError('Oh noooos!');

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }
}
