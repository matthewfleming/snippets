<?php

namespace matthewfleming\xml_to_html;

use matthewfleming\xml_to_html\Line;
use matthewfleming\xml_to_html\Section;

class Page
{
    const QUANTUM_NEW_SECTION = 31;
    const QUANTUM_NEW_LINE = 2;

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
            $element = $nodes[$i];
            $attributes = $element->attributes();
            $top = (int) $attributes['top'];

            $line = new Line();
            $line->addElement($element);

            $i++;
            while ($i < $end) {
                $current = $nodes[$i];
                $currentAttributes = $current->attributes();
                $currentTop = (int) $currentAttributes['top'];
                if (self::equals($top, $currentTop, self::QUANTUM_NEW_LINE)) {
                    $line->addElement($current);
                    $i++;
                } else {
                    break;
                }
            }
            $line->sort();
            //$line->dump();
            $lines[] = $line;
        }
        return $lines;
    }

    public function createSections($lines)
    {
        $sections = array();

        $i = 0;
        $end = count($lines);

        while ($i < $end) {
            $element = $lines[$i];
            $top = $element->getTop();

            $section = new Section();
            $section->addLine($element);

            $i++;
            while ($i < $end) {
                $current = $lines[$i];
                $currentTop = $current->getTop();
                if (self::equals($top, $currentTop, self::QUANTUM_NEW_SECTION)) {
                    $top = $currentTop;
                    $section->addLine($current);
                    $i++;
                } else {
                    $i++;
                    break;
                }
            }
            $sections[] = $section;
        }
        $this->sections = $sections;
    }

}