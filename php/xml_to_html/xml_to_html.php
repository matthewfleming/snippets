<?php

namespace matthewfleming\xml_to_html;

class Context {
    const STRUCTURE_NONE = 0;
    const STRUCTURE_TABLE = 1;
    const STRUCTURE_FORMATTED = 2;

    public $structure;
}

class Parser
{
    const INPUT_FILE_NAME = 'input.xml';
    const OUTPUT_FILE_NAME = 'output.wiki';
    const H2 = 50;
    const H3 = 40;
    const H4 = 30;
    const H5 = 20;
    const EQUALITY_QUANTUM = 1;
    const IGNORE_POSITION_LEFT = 600;
    const IGNORE_POSITION_TOP = 100;
    static $IGNORE_LIST_MATH = array(
        "Preliminary Copy"
    );
    static $IGNORE_LIST_REGEX = array(
        "/^\s*REV ..\/..\/../"
    );


    /**
     *
     * @var \SimpleXMLElement
     */
    public $xml;

    /**
     *
     * @var Context
     */
    public $context;

    /**
     *
     * @var string[int]
     */
    public $fonts;

    public function __construct()
    {
        $this->context = new Context();
        $this->context->structure = Context::STRUCTURE_NONE;
    }

    public function parseFonts()
    {
        $fontspecs = $this->xml->xpath('//fontspec');

        $fonts = array();

        foreach ($fontspecs as $fontspec) {
            $attributes = $fontspec->attributes();
            $size = $attributes['size'];

            if ($size >= self::H2) {
                $level = 2;
            } else if ($size >= self::H3) {
                $level = 3;
            } else if ($size >= self::H4) {
                $level = 4;
            } else if ($size >= self::H5) {
                $level = 5;
            } else {
                $level = 6;
            }
            $fonts[(int) $attributes['id']] = $level;
        }
    }

    /**
     *
     * @param \SimpleXMLElement $node
     */
    public function startTableRow($node) {
        $this->context->structure = Context::STRUCTURE_TABLE;
        return $this->continueTableRow($node);
    }

    /**
     *
     * @param \SimpleXMLElement $node
     */
    public function continueTableRow($node) {
        if(isset($node->b)) {
            $sep = "^ ";
        } else {
            $sep = "| ";
        }
        return $sep . $this->outputText($node, false, true) . " ";
    }

    /**
     *
     * @param \SimpleXMLElement $node
     */
    public function endTableRow($node) {
        $this->context->structure = Context::STRUCTURE_NONE;
        return $this->continueTableRow($node) . "|\n";
    }

    /**
     *
     * @param \SimpleXMLElement $node
     */
    public function outputText($node, $outputBold = true, $isChild = false) {
        $out = "";
        if($node->count()) {
            foreach($node->children() as $child) {
                $out .= $this->outputText($child, $outputBold, true) . " ";
            }
        } else {
            $out = (string)$node;
        }
        if(in_array($out, self::$IGNORE_LIST_MATH)) {
            return "";
        }
        foreach(self::$IGNORE_LIST_REGEX as $regex) {
            if(preg_match($regex, $out)) {
                return "";
            }
        }
        if(!$isChild) {
            $out .= "\n";
        }
        return $out;
    }


    /**
     *
     * @param \SimpleXMLElement $page
     */
    public function parsePage($page)
    {
        $out = "";

        //Build a proper array of text nodes
        $nodes = array();
        foreach($page->text as $page) {
            $nodes[] = $page;
        }
        
        //Sort nodes from top to bottom, left to right
        usort($nodes, function($a,$b) {
            $attributesA = $a->attributes();
            $attributesB = $b->attributes();
            if(self::equals($attributesA['top'], $attributesB['top'])) {
                //On the same line
                return $attributesA['left'] - $attributesB['left'];
            }
            return $attributesA['top'] - $attributesB['top'];
        });

        $i = 0;
        $end = count($nodes) - 1;
        $current = $nodes[0];
        $attributes = $current->attributes();
        $top = (int) $attributes['top'];
        $left = (int) $attributes['left'];

        while ($i <= $end) {
            if($i == $end) {
                $next = null;
                $attributesNext = null;
                $nextTop = 0;
            } else {
                $next = $nodes[$i + 1];
                $attributesNext = $next->attributes();
                $nextTop = (int) $attributesNext['top'];
            }
            //skip header and footer stuff
            if($this->context->structure === Context::STRUCTURE_NONE && (
                $left >= self::IGNORE_POSITION_LEFT
                || $top <= self::IGNORE_POSITION_TOP
            )) {

            } else {
                if (self::equals($top, $nextTop)) {
                    if ($this->context->structure !== Context::STRUCTURE_TABLE) {
                        $out .= $this->startTableRow($current);
                    } else {
                        $out .= $this->continueTableRow($current);
                    }
                } else if ($this->context->structure === Context::STRUCTURE_TABLE) {
                    $out .= $this->endTableRow($current);
                } else {
                    //a normal line
                    $out .= $this->outputText($current);
                }
            }
            if($next) {
                //move to the next node
                $current = $next;
                $attributes = $attributesNext;
                $top = (int) $attributes['top'];
                $left = (int) $attributes['left'];
            }
            $i++;
        }
        return $out;
    }

    public function export()
    {
        $out = "";

        foreach ($this->xml->page as $page) {
            echo $this->parsePage($page);
        }
    }

    public static function run()
    {
        $parser = new Parser();
        $parser->xml = simplexml_load_file(self::INPUT_FILE_NAME);

        $parser->parseFonts();
        $parser->export();
    }

    public static function equals($val1, $val2)
    {
        return (abs($val2 - $val1) < self::EQUALITY_QUANTUM);
    }

}
Parser::run();
