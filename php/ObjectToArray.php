<?php

class ObjectToArray
{

    protected $objectHashes = [];

    public static function convert(&$object)
    {
        $converter = new ObjectToArray();
        return $converter->objectToArray($object);
    }

    protected function convertObjectToArray(&$object)
    {
        if (is_object($object)) {
            // prevent infinite recursion for self or circulat references
            $hash = spl_object_hash($object);
            if (in_array($hash, $this->objectHashes)) {
                return '{...}';
            }
            $this->objectHashes[] = $hash;
            $clone = (array) $object;
            // Prevent deeply nested trace
            if ($object instanceof \Exception) {
                $clone['trace'] = $object->getTraceAsString();
            }
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
