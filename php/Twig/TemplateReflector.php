<?php

namespace github\matthewfleming;

class TemplateReflector implements \ArrayAccess
{
    const VARIABLE_START = '{{';
    const VARIABLE_END = '}}';

    private static $GLOBAL_EXCEPTIONS = array(
        'thing.this.that' => 'I do what I want!'
    );
    private $exceptions;
    private $name;

    /**
     *
     * @var TemplateReflector
     */
    private $parent;

    public function __construct($name, $exceptions = array(), $parent = null)
    {
        if (!is_array($exceptions)) {
            throw new \Exception('$exceptions parameter must be an array');
        }
        $this->name = $name;
        $this->exceptions = $exceptions;
        $this->parent = $parent;
    }

    private function checkExceptions($name, &$exceptionResult)
    {
        // Check local exceptions
        if (isset($this->exceptions[$name])) {
            $exceptionResult = $this->exceptions[$name];
            return true;
        }
        // Check global exceptions
        if (isset(self::$GLOBAL_EXCEPTIONS[$name])) {
            $exceptionResult = self::$GLOBAL_EXCEPTIONS[$name];
            return true;
        }
        return false;
    }

    public function __toString()
    {
        $fullName = $this->fullName();
        $exception = $this->checkExceptions($fullName, $exceptionResult);
        if ($exception) {
            return $exceptionResult;
        }
        return self::VARIABLE_START . $fullName . self::VARIABLE_END;
    }

    public function __get($name)
    {
        $exception = $this->checkExceptions($this->fullName() . '.' . $name, $exceptionResult);
        if ($exception) {
            return $exceptionResult;
        }
        return new TemplateReflector($name, $this->exceptions, $this);
    }

    public function __isset($name)
    {
        return true;
    }

    /* ArrayAccess Methods */

    public function offsetExists($offset)
    {
        return true;
    }

    public function offsetGet($offset)
    {

        $exception = $this->checkExceptions($this->fullName() . '.' . $offset, $exceptionResult);

        if ($exception) {
            return $exceptionResult;
        }
        return new TemplateReflector($offset, $this->exceptions, $this);
    }

    public function offsetSet($offset, $value)
    {
        return;
    }

    public function offsetUnset($offset)
    {
        return;
    }

    public function fullName()
    {
        return ($this->parent ? $this->parent->fullName() . '.' : '') . $this->name;
    }

}
$thing = new TemplateReflector('thing');

echo $thing->Me . "\n";
echo $thing->You . "\n";

echo $thing->Me->You . "\n";

echo $thing->this->that . "\n";

echo $thing->this->other['thing'] . "\n";


