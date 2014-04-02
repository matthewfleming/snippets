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
    
    public function determineList() {
        $line = $this->lines[0];
        $text = $line->elements[0]->innerText();

        if(strpos($text, '·') !== false) {
            return true;
        }
        return false;  
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        if ($this->type === self::TYPE_UNDEFINED) {
            if($this->determineList()) {
                $this->type = self::TYPE_LIST;
            } else {
                $this->type = self::TYPE_PARAGRAPH;
            }
        }
        return $this->type;
    }

    public function dump() {
        echo "--- Section start ---\n";
        foreach($this->lines as $line) {
            $line->dump();
            echo "\n";
        }
        echo "--- Section end ---\n";
    }

}
