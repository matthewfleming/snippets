<?php

namespace matthewfleming\xml_to_html;

class Context
{
    const STRUCTURE_NONE = 0;
    const STRUCTURE_TABLE = 1;
    const STRUCTURE_FORMATTED = 2;
    const STRUCTURE_HEADING = 3;

    public $structure;

    public function isUnstructured()
    {
        return ($this->structure === self::STRUCTURE_NONE);
    }

    public function isFormatted()
    {
        return ($this->structure === self::STRUCTURE_FORMATTED);
    }

}

class Parser
{
    const INPUT_FILE_NAME = 'input.xml';
    const OUTPUT_FILE_NAME = 'output.wiki';
    const HEADING_MAX_LEVEL = 5;
    const H2 = 40;
    const H3 = 20;
    const H4 = 15;
    const H5 = 15;
    const EQUALITY_QUANTUM = 1;
    const IGNORE_POSITION_LEFT = 600;
    const IGNORE_POSITION_TOP = 100;
    const FORMATTED_TEXT_THRESHHOLD = 157;
    const HARD_LEFT = 135;
    const FORMATTED_TEXT_SPACE_SIZE = 7;
    const QUANTUM_NEW_SECTION = 31;
    const QUANTUM_NEW_LINE = 2;

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
     * @var int[int]
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
            $size = (int) $attributes['size'];

            if ($size >= self::H2) {
                $level = 2;
            } else if ($size >= self::H3) {
                $level = 3;
            } else if ($size >= self::H4) {
                $level = 4;
            } else if ($size >= self::H5) {
                $level = 5;
            } else {
                $level = 0;
            }
            $fonts[(int) $attributes['id']] = $level;
        }

        $this->fonts = $fonts;
    }

    /**
     *
     * @param \SimpleXMLElement $node
     */
    public function startTableRow($node)
    {
        $this->context->structure = Context::STRUCTURE_TABLE;
        return $this->continueTableRow($node);
    }

    /**
     *
     * @param \SimpleXMLElement $node
     */
    public function continueTableRow($node)
    {
        if (isset($node->b)) {
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
    public function endTableRow($node)
    {
        $out = $this->continueTableRow($node);
        $this->context->structure = Context::STRUCTURE_NONE;
        return $out . "|\n";
    }

    public function startHeader($level)
    {
        $out = "=";
        for ($i = $level; $i <= self::HEADING_MAX_LEVEL; $i++) {
            $out .= '=';
        }
        return $out;
    }

    public function endHeader($level)
    {
        return $this->startHeader($level);
    }

    public function startFormatted()
    {
        $this->context->structure = Context::STRUCTURE_FORMATTED;
        return "<code>\n";
    }

    public function endFormatted()
    {
        $this->context->structure = Context::STRUCTURE_NONE;
        return "</code>\n";
    }

    /**
     *
     * @param \SimpleXMLElement $node
     */
    public function outputText($node, $outputBold = true, $isChild = false)
    {
        $out = "";
        if ($node->count()) {
            $xpath = $node->xpath('.');
            $xml = $xpath[0]->asXML();


            $innerXml = preg_replace('/<\/?text.*?>/', '', $xml);


            $out .= html_entity_decode($innerXml);
        } else {
            $out = (string) $node;
        }
        if (in_array($out, self::$IGNORE_LIST_MATH)) {
            return "";
        }
        foreach (self::$IGNORE_LIST_REGEX as $regex) {
            if (preg_match($regex, $out)) {
                return "";
            }
        }
        // Wiki escaping
        $out = preg_replace('/(\*+)/', '%%$1%%', $out);
        $bReplace = $outputBold ? '**' : '';
        $out = preg_replace('/<\/?b.*?>/', $bReplace, $out);

        if (!$isChild) {
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
        foreach ($page->text as $page) {
            $nodes[] = $page;
        }

        //Sort nodes from top to bottom, left to right
        usort($nodes, function($a, $b) {
            $attributesA = $a->attributes();
            $attributesB = $b->attributes();
            if (self::equals($attributesA['top'], $attributesB['top'])) {
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
            if ($i == $end) {
                $next = null;
                $attributesNext = null;
                $nextTop = 0;
            } else {
                $next = $nodes[$i + 1];
                $attributesNext = $next->attributes();
                $nextTop = (int) $attributesNext['top'];
            }
            //skip header and footer stuff
            if ($this->context->isUnstructured() && (
                $left >= self::IGNORE_POSITION_LEFT || $top <= self::IGNORE_POSITION_TOP
                )) {

            } else {
                // Tables
                if (self::equals($top, $nextTop)) {
                    if ($this->context->structure !== Context::STRUCTURE_TABLE) {
                        $out .= $this->startTableRow($current);
                    } else {
                        $out .= $this->continueTableRow($current);
                    }
                } else if ($this->context->structure === Context::STRUCTURE_TABLE) {
                    $out .= $this->endTableRow($current);
                } else {
                    // Headings
                    $headingLevel = $this->fonts[(int) $attributes['font']];

                    if ($headingLevel > 0) {
                        $heading = $this->outputText($current, false, true);
                        if ($heading) {
                            $out .= $this->startHeader($headingLevel) . ' ' . $heading . ' '
                                . $this->endHeader($headingLevel) . "\n";
                        }
                    } else {
                        if ($left >= self::FORMATTED_TEXT_THRESHHOLD) {
                            // Formatted Text
                            if (!$this->context->isFormatted()) {
                                $out .= $this->startFormatted();
                            }
                            $spaces = (int) ($left - self::HARD_LEFT ) / self::FORMATTED_TEXT_SPACE_SIZE;
                            $space = '';
                            for ($j = 0; $j < $spaces; $j++) {
                                $space .= ' ';
                            }
                            $out .= $space . $this->outputText($current);
                        } else {
                            //a normal line
                            if ($this->context->isFormatted()) {
                                $out .= $this->endFormatted();
                            }
                            $out .= $this->outputText($current);
                        }
                    }
                }
            }
            if ($next) {
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
