<?php

/**
 * @todo Finish me
 */
class Base31SafetyMap {

    public static $BASE_31_SAFETY_MAP = array(
        'a' => 'B', 'b' => 'C', 'c' => 'D', 'd' => 'F', 'e' => 'G', 'f' => 'H', 'g' => 'J', 'h' => 'K', 'i' => 'L', 'j' => 'M',
        'k' => 'N', 'l' => 'P', 'm' => 'Q', 'n' => 'R', 'o' => 'S', 'p' => 'T', 'q' => 'V', 'r' => 'W', 's' => 'X', 't' => 'Y',
        'u' => 'Z'
    );

    /**
     * Generates a base32 number with vowels removed to prevent offensive words
     * @throws \Exception
     */
    public function generateMerchantReference()
    {
        if ($this->id === null) {
            throw new \Exception("An id is required to generate a receipt number");
        }

        $base31 = base_convert($this->id, 10, 31);
        $length = strlen($base31);
        $safe31 = '';
        for ($i = 0; $i < $length; $i++) {
            $digit = $base31[$i];
            $safe31 .= is_numeric($digit) ? $digit : self::$BASE_31_SAFETY_MAP[$digit];
        }
        $this->merchantReference = self::RECEIPT_PREFIX . substr(
                sprintf('%0' . (self::RECEIPT_LENGTH - 1) . 's', $safe31), - self::RECEIPT_LENGTH
        );
    }

}