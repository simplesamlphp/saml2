<?php

namespace SAML2\Utilities;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group utilities
     * @test
     *
     * @expectedException \SAML2\Exception\RuntimeException
     */
    public function when_loading_a_non_existant_file_an_exception_is_thrown()
    {
        File::getFileContents('/foo/bar/baz/quux');
    }

   /**
     * @group utilities
     * @test
     *
     * @expectedException \SAML2\Exception\InvalidArgumentException
     */
    public function passing_nonstring_filename_throws_exception()
    {
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
