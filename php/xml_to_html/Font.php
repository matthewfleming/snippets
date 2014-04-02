<?php

namespace matthewfleming\xml_to_html;

class Font
{
    const HEADING_MAX_LEVEL = 5;
    const HEADING_SIZE_H2 = 40;
    const HEADING_SIZE_H3 = 20;
    const HEADING_SIZE_H4 = 14;
    const HEADING_SIZE_H5 = 12;

    public $id;
    public $size;
    public $family;
    public $color;
    public $headingLevel;

    private function __construct(\SimpleXMLElement $element)
    {
        $attributes = $element->attributes();
        $this->id = (int) $attributes['id'];
        $this->size = (int) $attributes['size'];
        $this->family = (string) $attributes['family'];
        $this->color = (string) $attributes['color'];

        $this->determineHeadingLevel();
    }

    private function determineHeadingLevel()
    {
        if ($this->size >= self::HEADING_SIZE_H2) {
            $level = 2;
        } else if ($this->size >= self::HEADING_SIZE_H3) {
            $level = 3;
        } else if ($this->size >= self::HEADING_SIZE_H4) {
            $level = 4;
        } else if ($this->size >= self::HEADING_SIZE_H5) {
            $level = 5;
        } else {
            $level = 0;
        }
        $this->headingLevel = $level;
    }

    public function isHeading()
    {
        return $this->headingLevel > 0;
    }

    /**
     *
     * @var Font[]
     */
    public static $fonts;

    /**
     *
     * @param int $id
     * @return Font
     */
    public static function getFont($id)
    {
        return self::$fonts[$id];
    }

    public static function getFonts()
    {
        return self::$fonts;
    }

    public static function addFont(\SimpleXMLElement $element) {
        $font = new Font($element);
        self::$fonts[$font->id] = $font;
    }

}