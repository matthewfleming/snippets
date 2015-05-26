<?php

class Password
{
    public static $passwordConfig = [
        'algorithm' => PASSWORD_DEFAULT,
        'options' => [
            'cost' => 11
        ]
    ];

    public static function validateAndRehash($user, $password)
    {
        // retrieve stored hash of password ($user->password)
        $oldHash = '$2y$07$BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq';

        // verify stored hash against plain-text password
        if (true === password_verify($password, $oldHash)) {
            // verify legacy password to new password_hash options
            if (true === password_needs_rehash($oldHash, self::$passwordConfig['algorithm'], self::$passwordConfig['options'])) {
                // rehash/store plain-text password using new hash
                $newHash = password_hash($password, $passwordConfig['algorithm'], $passwordConfig['options']);
                // write new hash
                echo $newHash;
            }
        }
    }

    public static function generateHash($password)
    {
        return password_hash($password, self::$passwordConfig['algorithm'], self::$passwordConfig['options']);
    }

}
if ($argc == 2) {
    $password = $argv[1];
    $hash = Password::generateHash($password);
    echo "Password: $password\n";
    echo "Hash:     $hash\n";
    echo "Verify:   " . password_verify($password,$hash);
} else {
    echo 'Usage: php ', basename(__FILE__), ' password';
}