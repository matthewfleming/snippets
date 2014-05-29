<?php

class Validator
{
    /**
     *
     * @param mixed $value string or object with __toString method to check
     * @param bool $checkMX Enable check for valid MX record
     * @param bool $checkHost Enable check for valid MX, A or AAAA record
     * @return boolean
     * @throws \Exception
     */
    public function validateEmail($value, $checkMX = false, $checkHost = false)
    {
        if (null === $value || '' === $value) {
            return false;
        }

        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new \Exception($value, 'Expected string');
        }

        $value = (string) $value;
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL);

        if ($valid) {
            $host = substr($value, strpos($value, '@') + 1);

            if (version_compare(PHP_VERSION, '5.3.3', '<') && strpos($host, '.') === false) {
                // Likely not a FQDN, bug in PHP FILTER_VALIDATE_EMAIL prior to PHP 5.3.3
                $valid = false;
            }

            // Check for host DNS resource records
            if ($valid && $checkMX) {
                $valid = $this->checkMX($host);
            } elseif ($valid && $checkHost) {
                $valid = $this->checkHost($host);
            }
        }

        return $valid;
    }

    /**
     * Check DNS Records for MX type.
     *
     * @param string $host Hostname
     *
     * @return Boolean
     */
    private function checkMX($host)
    {
        return checkdnsrr($host, 'MX');
    }

    /**
     * Check if one of MX, A or AAAA DNS RR exists.
     *
     * @param string $host Hostname
     *
     * @return Boolean
     */
    private function checkHost($host)
    {
        return $this->checkMX($host) || (checkdnsrr($host, "A") || checkdnsrr($host, "AAAA"));
    }
}
