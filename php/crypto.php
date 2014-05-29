<?php

/**
 * Return hex encoded random bytes. The dictionary is /[0-9a-f]/.
 * Encoded data is 100% larger than bytes encoded. Entropy = 4*$length bits.
 * @param int $length Length of psuedo-random string
 * @return string
 */
function psuedo_random_hex_bytes($length)
{
    return bin2hex(openssl_random_pseudo_bytes($length / 2));
}

/**
 * Return URL safe random bytes using standard 'base64url' encoding with URL and Filename Safe Alphabet (RFC 4648).
 * The dictionary is /[0-9A-Za-z_\-]/.
 * Encoded data is 33% larger than bytes encoded. Entropy = 6*$length bits.
 * @param int $length Length of psuedo-random string
 * @return string
 */
function psuedo_random_urlsafe($length)
{
    $adjustedLength = ceil($length / 1.33);
    $base64 = base64_encode(openssl_random_pseudo_bytes($adjustedLength));
    $urlSafe = str_replace(array('+', '/'), array('-', '_'), $base64);
    return substr($urlSafe, 0, $length);
}

echo psuedo_random_urlsafe(70);
