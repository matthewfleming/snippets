<?php

require_once 'TrimAll.php';

use PHPUnit\Framework\TestCase;
use function MatthewFleming\PHP\trim_all;

final class TrimAllTest extends TestCase
{

    public function testTrimBoth()
    {
        $tests = [
            "\xC2\x85 TEST ONE \xC2\x85" => "TEST ONE",
            "\xC2\xA0 TEST TWO \xC2\xA0" => "TEST TWO",
            "\xE1\xA0\x8E TEST THREE \xE1\xA0\x8E" => "TEST THREE",
            "\xE2\x80\x80 TEST FOUR \xE2\x80\x80" => "TEST FOUR",
            "\xE2\x80\x85 TEST FIVE \xE2\x80\x85" => "TEST FIVE",
            "\xE2\x80\x8D TEST SIX \xE2\x80\x8D" => "TEST SIX",
            "\xE2\x80\xA8 TEST SEVEN \xE2\x80\xA8" => "TEST SEVEN",
            "\xE2\x80\xA9 TEST EIGHT \xE2\x80\xA9" => "TEST EIGHT",
            "\xE2\x80\xAF TEST NINE \xE2\x80\xAF" => "TEST NINE",
            "\xE2\x81\x9F TEST TEN \xE2\x81\x9F" => "TEST TEN",
            "\xE2\x81\xA0 TEST ELEVEN \xE2\x81\xA0" => "TEST ELEVEN",
            "\xE3\x80\x80 TEST TWELVE \xE3\x80\x80" => "TEST TWELVE",
            "\xEF\xBB\xBF TEST THIRTEEN \xEF\xBB\xBF" => "TEST THIRTEEN"
        ];

        foreach ($tests as $test => $result) {
            $this->assertEquals(trim_all($test), $result);
        }
    }

}
