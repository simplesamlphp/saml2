<?php

namespace SAML2\Certificate;

class KeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group certificate
     *
     * @test
     * @expectedException \SAML2\Certificate\Exception\InvalidKeyUsageException
     */
    public function invalid_key_usage_should_throw_an_exception()
    {
        $key = new Key(array(Key::USAGE_SIGNING => true));

        $key->canBeUsedFor('foo');
    }

    /**
     * @group certificate
     *
     * @test
     */
    public function assert_that_key_usage_check_works_correctly()
    {
        $key = new Key(array(Key::USAGE_SIGNING => true));

        $this->assertTrue($key->canBeUsedFor(Key::USAGE_SIGNING));
        $this->assertFalse($key->canBeUsedFor(Key::USAGE_ENCRYPTION));

        $key[Key::USAGE_ENCRYPTION] = false;
        $this->assertFalse($key->canBeUsedFor(Key::USAGE_ENCRYPTION));
    }
}
