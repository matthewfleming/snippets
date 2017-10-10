<?php

namespace MatthewFleming\Php;

class Escaper
{

    /**
     *
     * Escape input for html content context
     *
     * @param string $data
     * @param string $encoding
     * @return string
     */
    public static function escapeHtmlContent($data, $encoding = 'UTF-8')
    {
        return htmlspecialchars($data, ENT_COMPAT, $encoding);
    }

    /**
     *
     * Escape input for html attribute context
     *
     * @param string $data
     * @param string $encoding
     * @return string
     */
    public static function escapeHtmlAttribute($data, $encoding = 'UTF-8')
    {
        return htmlspecialchars($data, ENT_QUOTES, $encoding);
    }

}
