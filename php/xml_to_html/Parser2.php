<?php

namespace matthewfleming\xml_to_html;

use matthewfleming\xml_to_html\Line;

class Parser2
{
    const INPUT_FILE_NAME = 'input.xml';
    const OUTPUT_FILE_NAME = 'output.wiki';


    /**
     *
     * @var \SimpleXMLElement
     */
    public $xml;


    /**
     *
     * @var Page
     */
    public $pages;

    public function parseFonts()
    {
        $fontspecs = $this->xml->xpath('//fontspec');

        foreach ($fontspecs as $fontspec) {
            Font::addFont($fontspec);
        }
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
                echo "---- Page " . $pageNumber . ' Section ' . $index . ' Type ' . $section->getTypeString() . " ----\n";
                foreach ($section->lines as $line) {
                    echo $line->toString();
                }
            }
        }

    }

}