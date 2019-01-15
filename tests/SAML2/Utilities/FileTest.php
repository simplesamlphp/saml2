<?php

namespace SAML2\Utilities;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group utilities
     * @test
     */
    public function when_loading_a_non_existant_file_an_exception_is_thrown()
    {
        $this->setExpectedException('SAML2\Exception\RuntimeException', 'File "/foo/bar/baz/quux" does not exist or is not readable');
        File::getFileContents('/foo/bar/baz/quux');
    }


    /**
     * @group utilities
     * @test
     */
    public function passing_nonstring_filename_throws_exception()
    {
        $this->setExpectedException('SAML2\Exception\InvalidArgumentException', 'Invalid Argument type: "string" expected, "NULL" given');
        File::getFileContents(null);
    }


    /**
     * @group utilities
     * @test
     */
    public function an_existing_readable_file_can_be_loaded()
    {
        $contents = File::getFileContents(__DIR__ . '/File/can_be_loaded.txt');

        $this->assertEquals("Yes we can!\n", $contents, 'The contents of the loaded file differ from what was expected');
    }
}
