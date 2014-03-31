<?php

namespace matthewfleming\xml_to_html;

class Line
{
    /**
     *
     * @var \SimpleXMLElement[]
     */
    public $elements = array();
    private $top = null;

    public function addElement($element)
    {
        $this->elements[] = $element;
    }

    public function getTop()
    {
        if (!$this->top) {
            $attributes = $this->elements[0]->attributes();
            $this->top = (int) $attributes['top'];
        }
        return $this->top;
    }

    public function sort()
    {
        usort($this->elements, function($a, $b) {
            $attributesA = $a->attributes();
            $attributesB = $b->attributes();
            return $attributesA['left'] - $attributesB['left'];
        });
    }

    public function dump() {
        foreach($this->elements as $element) {
            echo var_dump($element);
        }
    }

    public function toString()
    {
        foreach ($this->elements as $element) {
            echo $this->outputText($element);
        }
    }

    public function filter()
    {
        $out = "";
        if (in_array($out, self::$IGNORE_LIST_MATH)) {
            return "";
        }
        foreach (self::$IGNORE_LIST_REGEX as $regex) {
            if (preg_match($regex, $out)) {
                return "";
            }
        }
    }

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
        // Wiki escaping
        $out = preg_replace('/(\*+)/', '%%$1%%', $out);
        $bReplace = $outputBold ? '**' : '';
        $out = preg_replace('/<\/?b.*?>/', $bReplace, $out);

        if (!$isChild) {
            $out .= "\n";
        }
        return $out;
    }

}