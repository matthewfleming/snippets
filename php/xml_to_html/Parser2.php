<?php

namespace matthewfleming\xml_to_html;

use matthewfleming\xml_to_html\Line;

class Parser2
{
    const INPUT_FILE_NAME = 'input.xml';
    const OUTPUT_FILE_NAME = 'output.wiki';
    const HEADING_MAX_LEVEL = 5;
    const HEADING_SIZE_H2 = 40;
    const HEADING_SIZE_H3 = 20;
    const HEADING_SIZE_H4 = 15;
    const HEADING_SIZE_H5 = 15;

    /**
     *
     * @var \SimpleXMLElement
     */
    public $xml;
    public $fonts;
    public $lines;
    public $sections;

    public function parseFonts()
    {
        $fontspecs = $this->xml->xpath('//fontspec');

        $fonts = array();

        foreach ($fontspecs as $fontspec) {
            $attributes = $fontspec->attributes();
            $size = (int) $attributes['size'];

            if ($size >= self::HEADING_SIZE_H2) {
                $level = 2;
            } else if ($size >= self::HEADING_SIZE_H3) {
                $level = 3;
            } else if ($size >= self::HEADING_SIZE_H4) {
                $level = 4;
            } else if ($size >= self::HEADING_SIZE_H5) {
                $level = 5;
            } else {
                $level = 0;
            }
            $fonts[(int) $attributes['id']] = $level;
        }

        $this->fonts = $fonts;
    }

    public function createPages()
    {
        //Get an array of text nodes
        foreach($this->xml->page as $page) {
            $this->pages[] = new Page($page->xpath('text'));
        }
    }

    public static function run()
    {
        $parser = new Parser2();
        $parser->xml = simplexml_load_file(self::INPUT_FILE_NAME);

        $parser->parseFonts();
        $parser->createPages();

        foreach ($parser->pages as $pageNumber => $page) {
            foreach($page->sections as $index => $section) {
                echo "---- Page " . $pageNumber . ' Section ' . $index . ' Type ' . $section->type . " ----\n";
                foreach ($section->lines as $line) {
                    echo $line->toString();
                }
            }
        }

    }

}