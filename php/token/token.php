<?php

class OpensslAES
{

    const METHOD = 'aes-256-cbc';

    public static function encrypt($message, $key)
    {
        if (mb_strlen($key, '8bit') !== 32) {
            throw new Exception("Needs a 256-bit key!");
        }
        $ivsize = openssl_cipher_iv_length(self::METHOD);
        $iv = openssl_random_pseudo_bytes($ivsize);

        $ciphertext = openssl_encrypt(
            $message, self::METHOD, $key, OPENSSL_RAW_DATA, $iv
        );

        return $iv . $ciphertext;
    }

    public static function decrypt($message, $key)
    {
        if (mb_strlen($key, '8bit') !== 32) {
            throw new Exception("Needs a 256-bit key!");
        }
        $ivsize = openssl_cipher_iv_length(self::METHOD);
        $iv = mb_substr($message, 0, $ivsize, '8bit');
        $ciphertext = mb_substr($message, $ivsize, null, '8bit');

        return openssl_decrypt(
            $ciphertext, self::METHOD, $key, OPENSSL_RAW_DATA, $iv
        );
    }

}

class Tokenizer
{

    protected $key;
    protected $debug;

    public function __construct($key, $debug = false)
    {
        $this->key = $key;
        $this->debug = $debug;
    }

    protected function base64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64urlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    protected function log($title, $text)
    {
        if ($this->debug) {
            $padLength = 60;
            $padLeft = str_repeat('-', ($padLength - 2 - strlen($title)) / 2);
            $padRight = ($padLength - strlen($title)) % 2 ? $padLeft . '-' : $padLeft;
            echo "$padLeft $title $padRight\n";
            echo $text;
            echo "\n", str_repeat('-', $padLength), "\n";
        }
    }

    public function validateToken($token)
    {
        $fields = [
            'timestamp',
            'userId',
            'email',
            'firstName',
            'lastName',
            'isSubscriber',
            'subscriptions',
        ];
        foreach ($fields as $field) {
            if (!isset($token[$field])) {
                return false;
            }
        }
        return true;
    }

    public function createToken($data)
    {
        $jsonData = json_encode($data);
        $this->log('Unencrypted token', $jsonData);
        $token = $this->base64urlEncode(OpensslAES::encrypt($jsonData, $this->key));
        $this->log('Encrypted token', $token);
        return $token;
    }

    public function readToken($token)
    {
        $decrypted = OpensslAES::decrypt($this->base64urlDecode($token), $this->key);
        $this->log('Decrypted token', $decrypted);
        $decoded = json_decode($decrypted, true);
        if (!$this->validateToken($decoded)) {
            return null;
        }
        return $decoded;
    }

}

$cookieName = 'BENEFITSTOKEN';
//$domain = 'thewest.com.au'; //live
$domain = 'wanews.com.au';
$key = '3o1caNMOK9sSWUApbB8Lkie7kwyXMWsH';
$tokenData = [
    'timestamp' => date('c'),
    'userId' => 123,
    'email' => 'bob@email.com',
    'firstName' => 'Bob',
    'lastName' => 'Jones',
    'isSubscriber' => true,
    'subscriptions' => [
        [
            'subscriptionId' => 2354,
            'expiry' => '2016-10-21'
        ],
        [
            'subscriptionId' => 33356,
            'expiry' => '2017-10-21'
        ]
    ]
];

$tokenizer = new Tokenizer($key, true);

$shell = !isset($_SERVER['HTTP_HOST']);
if ($shell) {
    /* smedia */
    $token = $tokenizer->createToken($tokenData);
    /* subscriber services */
    $data = $tokenizer->readToken($token);
    echo 'token is ';
    echo $data ? 'valid' : 'invalid';
} else {
    // web server token.php?op=write to create cookie, token.php?op=read to read cookie
    echo '<html><body><pre>';
    if (!isset($_GET['op']) || $_GET['op'] === 'write') {
        /* smedia */
        $token = $tokenizer->createToken($tokenData);
        echo setrawcookie($cookieName, $token, 0, '', $domain, true, true) ? 'cookie saved' : 'error saving cookie';
    } else {
        /* subscriber services */
        $token = $_COOKIE[$cookieName];
        $data = $tokenizer->readToken($token);
        echo 'token is ';
        echo $data ? 'valid' : 'invalid';
    }
    echo '</pre>';
    echo '<p><a href="?op=read">read cookie</a><br/><a href="?op=write">write cookie</a></p>';
    echo '</body></html>';
}