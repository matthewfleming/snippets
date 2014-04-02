<?php

namespace matthewfleming\xml_to_html;

class Element
{
    public $top;
    public $left;
    public $width;
    public $height;

    /**
     *
     * @var Font
     */
    public $font;
    public $innerXml;

    public function __construct(\SimpleXMLElement $element)
    {
        $attributes = $element->attributes();
        $this->top = (int) $attributes['top'];
        $this->left = (int) $attributes['left'];
        $this->width = (int) $attributes['width'];
        $this->height = (int) $attributes['height'];
        $fontId = (int) $attributes['font'];
        $this->font = Font::getFont($fontId);

        $this->createInnerXml($element);
    }

    private function createInnerXml($element)
    {
        if ($element->count()) {
            $xml = $element->asXML();
            $innerXml = preg_replace('/<\/?text.*?>/', '', $xml);
            $this->innerXml = html_entity_decode($innerXml);
        } else {
            $this->innerXml = (string) $element;
        }
    }

    public function innerText()
    {
        return preg_replace('/<\/?.*?>/', '', $this->innerXml);
    }

    public function isHeading() {
        return 
            $this->font->isHeading()
            && ($this->height > $this->font->size)
            && (strpos($this->innerText(), '.') === false);
    }

}