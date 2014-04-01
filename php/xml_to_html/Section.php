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
    public $type;

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
        
        $text = $line->elements[0];
        
        if(preg_match('/^\s*·\s*$/', $text)) {
            return true;
        }
        return false;  
    }

    public function getType() {
        if ($this->type === null) {
            if($this->determineList()) {
                $this->type = self::TYPE_LIST;
            }
            $this->type = self::TYPE_PARAGRAPH;
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