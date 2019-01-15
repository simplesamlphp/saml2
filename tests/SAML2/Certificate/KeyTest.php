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
        $key = new Key([Key::USAGE_SIGNING => true]);

        $key->canBeUsedFor('foo');
    }


    /**
     * @group certificate
     * @dataProvider functionProvider
     * @test
     * @expectedException \SAML2\Exception\InvalidArgumentException
     */
    public function invalid_offset_type_should_throw_an_exception($function, $params)
    {
        $key = new Key([Key::USAGE_SIGNING => true]);
        $this->setExpectedException('\SAML2\Exception\InvalidArgumentException');
        call_user_func_array([$key, $function], $params);
    }


    /**
     * @group certificate
     *
     * @test
     */
    public function assert_that_key_usage_check_works_correctly()
    {
        $key = new Key([Key::USAGE_SIGNING => true]);

        $this->assertTrue($key->canBeUsedFor(Key::USAGE_SIGNING));
        $this->assertFalse($key->canBeUsedFor(Key::USAGE_ENCRYPTION));

        $key[Key::USAGE_ENCRYPTION] = false;
        $this->assertFalse($key->canBeUsedFor(Key::USAGE_ENCRYPTION));
    }


    /**
     * @group certificate
     *
     * @test
     */
    public function assert_that_offsetget_works_correctly()
    {
        $key = new Key([Key::USAGE_SIGNING => true]);
        $this->assertTrue($key->offsetGet(Key::USAGE_SIGNING));
    }


    /**
     * @group certificate
     *
     * @test
     */
    public function assert_that_offsetunset_unsets_offset()
    {
        $key = new Key([Key::USAGE_SIGNING => true, Key::USAGE_ENCRYPTION => true]);
        $this->assertTrue($key->offsetExists(Key::USAGE_SIGNING));
        $this->assertTrue($key->offsetExists(Key::USAGE_ENCRYPTION));
        $key->offsetUnset(Key::USAGE_SIGNING);
        $this->assertFalse($key->offsetExists(Key::USAGE_SIGNING));
        $this->assertTrue($key->offsetExists(Key::USAGE_ENCRYPTION));
        $key->offsetUnset(Key::USAGE_ENCRYPTION);
        $this->assertFalse($key->offsetExists(Key::USAGE_SIGNING));
        $this->assertFalse($key->offsetExists(Key::USAGE_ENCRYPTION));
    }


    public function functionProvider()
    {
        return [
            'offsetGet' => ['offsetGet', [0]],
            'offsetExists' => ['offsetExists', [0]],
            'offsetSet' => ['offsetSet', [0, 2]],
            'offsetUnset' => ['offsetUnset', [0]]
        ];
    }
}
