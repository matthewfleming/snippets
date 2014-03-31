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
    public $type;

    public function __construct()
    {
        $this->type = self::TYPE_UNDEFINED;
    }

    public function addLine($line)
    {
        $this->lines[] = $line;
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