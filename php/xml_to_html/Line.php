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
        $out = "";
        foreach ($this->elements as $element) {
            $out .= $this->outputText($element, false, true);
        }
        return $out . "\n";
    }

    public function toText()
    {
        $out = '';
        foreach ($this->elements as $element) {
            $out .= $this->innerText($element);
        }
        return $out . "\n";
    }

    public function innerXML($node) {
        if ($node->count()) {
            $xpath = $node->xpath('.');
            $xml = $xpath[0]->asXML();

            $innerXml = preg_replace('/<\/?text.*?>/', '', $xml);

            return html_entity_decode($innerXml);
        }
        return (string) $node;
    }

    public function innerText($node) {
        $innerXml = $this->innerXML($node);
        return preg_replace('/<\/?.*?>/', '', $innerXml);
    }

    public function outputText($node, $outputBold = true, $isChild = false)
    {
        $out = $this->innerXML($node);
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