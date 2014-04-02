<?php

namespace matthewfleming\xml_to_html;

class Line
{
    const TYPE_UNDEFINED = 0;
    const TYPE_PARAGRAPH = 1;
    const TYPE_HEADING = 2;

    /**
     *
     * @var Element[]
     */
    public $elements = array();
    private $top = null;

    /**
     *
     * @return boolean
     */
    public function isHeading() {
        foreach($this->elements as $element) {
            if ($element->isHeading()) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param \SimpleXMLElement $element
     */
    public function addElement($element)
    {
        $this->elements[] = new Element($element);
    }

    public function getTop()
    {
        if (!$this->top) {
            $this->top = $this->elements[0]->top;
        }
        return $this->top;
    }

    public function sort()
    {
        usort($this->elements, function($a, $b) {
            return $a->left - $b->left;
        });
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
            $out .= $element->innerText();
        }
        return $out . "\n";
    }

    public function outputText($node, $outputBold = true, $isChild = false)
    {
        $out = $node->innerXml;
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