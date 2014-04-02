<?php

namespace matthewfleming\xml_to_html;

use matthewfleming\xml_to_html\Line;

class Section
{
    const TYPE_UNDEFINED = 0;
    const TYPE_PARAGRAPH = 1;
    const TYPE_HEADING = 2;
    const TYPE_FORMATTED = 3;
    const TYPE_LIST = 4;
    const TYPE_TABLE = 5;

    public static $TYPES = array (
        self::TYPE_UNDEFINED => 'TYPE_UNDEFINED',
        self::TYPE_PARAGRAPH => 'TYPE_PARAGRAPH',
        self::TYPE_HEADING => 'TYPE_HEADING',
        self::TYPE_FORMATTED => 'TYPE_FORMATTED',
        self::TYPE_LIST => 'TYPE_LIST',
        self::TYPE_TABLE => 'TYPE_TABLE'
    );

    /**
     *
     * @var Line[]
     */
    public $lines = array();

    /**
     *
     * @var int
     */
    private $type;

    /**
     *
     * @var Page
     */
    public $page;

    public function __construct()
    {
        $this->type = self::TYPE_UNDEFINED;
    }

    public function addLine($line)
    {
        $this->lines[] = $line;
    }

    public function determineList()
    {
        $line = $this->lines[0];
        $text = $line->elements[0]->innerText();

        if (strpos($text, '·') !== false) {
            $this->type = self::TYPE_LIST;
            return true;
        }
        return false;
    }

    public function determineTable()
    {
        $nElements = 0;
        $columns = array();
        $maxColumnsPerLine = 1;
        foreach ($this->lines as $line) {
            foreach ($line->elements as $element) {
                if (isset($columns[$element->left])) {
                    $columns[$element->left] ++;
                } else {
                    $columns[$element->left] = 1;
                }
                $nElements++;
            }
            if (count($line->elements) > $maxColumnsPerLine) {
                $maxColumnsPerLine = count($line->elements);
            }
        }
        $nColumns = count($columns);

        if ($nColumns < $nElements && $nColumns > 1) {
            if ($maxColumnsPerLine > 1) {
                $this->type = self::TYPE_TABLE;
            } else {
                $this->type = self::TYPE_FORMATTED;
            }
            return true;
        }

        return false;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        if ($this->type === self::TYPE_UNDEFINED) {

            if (!$this->determineList() && !$this->determineTable()) {
                $this->type = self::TYPE_PARAGRAPH;
            }
        }
        return $this->type;
    }

    public function getTypeString()
    {
        return self::$TYPES[$this->getType()];
    }

    public function dump()
    {
        echo "--- Section start ---\n";
        foreach ($this->lines as $line) {
            $line->dump();
            echo "\n";
        }
        echo "--- Section end ---\n";
    }

}