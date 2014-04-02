<?php

namespace matthewfleming\xml_to_html;

use matthewfleming\xml_to_html\Line;
use matthewfleming\xml_to_html\Section;

class Page
{
    const QUANTUM_NEW_SECTION = 27;
    const QUANTUM_NEW_LINE = 2;

    public static $IGNORE_LIST_MATCH = array(
        "Preliminary Copy"
    );
    public static $IGNORE_LIST_REGEX = array(
        "/^\s*REV ..\/..\/../"
    );

    /**
     *
     * @var Section[]
     */
    public $sections;

    public function __construct($elements)
    {
        $lines = $this->createLines($elements);
        $this->createSections($lines);
    }

    public function addSection($section)
    {
        $this->sections[] = $section;
    }

    public static function equals($val1, $val2, $quantum)
    {
        return (abs($val2 - $val1) <= $quantum);
    }

    private function skip($element)
    {
        if (in_array($element->innerText(), self::$IGNORE_LIST_MATCH)) {
            return true;
        }
        foreach (self::$IGNORE_LIST_REGEX as $regex) {
            if (preg_match($regex, $element->innerText())) {
                return true;
            }
        }
        return false;
    }

    public function createLines($nodes)
    {
        //Sort nodes from top to bottom, left to right
        usort($nodes, function($a, $b) {
            $attributesA = $a->attributes();
            $attributesB = $b->attributes();
            if (self::equals($attributesA['top'], $attributesB['top'], self::QUANTUM_NEW_LINE)) {
                //On the same line
                return $attributesA['left'] - $attributesB['left'];
            }
            return $attributesA['top'] - $attributesB['top'];
        });

        $i = 0;
        $end = count($nodes) - 1;

        while ($i <= $end) {
            $element = new Element($nodes[$i]);
            if($this->skip($element)) {
                $i++;
                continue;
            }
            $top = $element->top;

            $line = new Line();
            $line->addElement($element);

            $i++;
            while ($i < $end) {
                $current = new Element($nodes[$i]);
                if($this->skip($current)) {
                    $i++;
                    continue;
                }
                $currentTop = $current->top;
                if (self::equals($top, $currentTop, self::QUANTUM_NEW_LINE)) {
                    $line->addElement($current);
                    $i++;
                } else {
                    break;
                }
            }
            $line->sort();
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     *
     * @param Lines[] $lines
     */
    public function createSections($lines)
    {
        $sections = array();

        $i = 0;
        $end = count($lines);

        while ($i < $end) {
            $line = $lines[$i];
            $i++;
            $top = $line->getTop();

            $section = new Section();
            $section->page = $this;
            $section->addLine($line);

            if ($line->isHeading()) {
                $section->setType(Section::TYPE_HEADING);
                $sections[] = $section;
                continue;
            }

            while ($i < $end) {
                $current = $lines[$i];
                $currentTop = $current->getTop();
                if (self::equals($top, $currentTop, self::QUANTUM_NEW_SECTION)) {
                    $top = $currentTop;
                    $section->addLine($current);
                    $i++;
                } else {
                    break;
                }
            }
            $sections[] = $section;
        }
        $this->sections = $sections;
    }

}