<?php

class ObjectToArray
{

    public static function objectToArray(&$object)
    {
        if (is_object($object)) {
            // prevent infinite recursion for self or circulat references
            $hash = spl_object_hash($object);
            if (in_array($hash, $this->objects)) {
                return '{...}';
            }
            $this->objects[] = $hash;
            $clone = (array) $object;
        } else if (is_array($object)) {
            $clone = &$object;
        } else {
            // scalar
            return $object;
        }
        $rtn = [];

        foreach ($clone as $key => &$value) {
            $dirtyKey = explode("\0", $key);
            $cleanKey = $dirtyKey[count($dirtyKey) - 1];
            $rtn[$cleanKey] = (is_array($value) || is_object($value)) ? $this->objectToarray($value) : $value;
        }

        return $rtn;
    }

}
