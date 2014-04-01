<?php

namespace matthewfleming\xml_to_html;

use matthewfleming\xml_to_html\Line;

class Element
{
    public $top;
    public $left;
    public $width;
    public $height;
    public $fontNumber;
    public $innerXml;

    public function __construct(\SimpleXMLElement $element)
    {
        $attributes = $element->attributes();
        $this->top = $attributes['top'];
        $this->left = $attributes['left;'];
        $this->width = $attributes['width'];
        $this->height = $attributes['height'];
        $this->fontNumber = $attributes['fontNumber'];

        $this->createInnerXml($element);
    }

    private function createInnerXml($element) {
        if ($element->count()) {
            $xml = $element->asXML();
            $innerXml = preg_replace('/<\/?text.*?>/', '', $xml);
            $this->innerXml = html_entity_decode($innerXml);
        } else {
            $this->innerXml = (string) $element;
        }
    }

}