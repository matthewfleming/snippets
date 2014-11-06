<?php

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data)
{
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

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
 * Generate a 32bit integer between $min and $max
 * @param int $min
 * @param int $max
 */
function psuedo_random_number($min, $max) {
    $bytes = openssl_random_pseudo_bytes(4);
    $int = unpack('l', $bytes);
    return $min + abs($int[1] % ($max-$min+1));
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
    $bytes = openssl_random_pseudo_bytes($adjustedLength);
    $urlSafe = base64url_encode($bytes);
    return substr($urlSafe, 0, $length);
}

function generate_keypair($digestAlgorithm = "sha512", $bits = 4096, $keyType = OPENSSL_KEYTYPE_RSA)
{
    $config = array(
        "digest_alg" => $digestAlgorithm,
        "private_key_bits" => $bits,
        "private_key_type" => $keyType
    );

    // Create the key-pair resource
    $keyPairResource = openssl_pkey_new($config);

    $pemKeys = array();
    // Extract the private key
    openssl_pkey_export($keyPairResource, $pemKeys['private']);

    // Extract the public key
    $details = openssl_pkey_get_details($keyPairResource);
    $pemKeys['public'] = $details['key'];

    return $pemKeys;
}

function make_certificate()
{
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    // Create the private and public key
    $keyPairResource = openssl_pkey_new($config);

    // Extract the private key from $privateKeyResource
    openssl_pkey_export($keyPairResource, $privateKey);

    // Create a self-signed certificate
    $dn = array(
        "countryName" => "AU",
        "stateOrProvinceName" => "WA",
        "localityName" => "Perth",
        "organizationName" => "The West Australian",
        "organizationalUnitName" => "Circulation Department",
        "commonName" => "circdemo.wanews.com.au",
        "emailAddress" => "matthew.fleming@wanews.com.au"
    );

    $csr = openssl_csr_new($dn, $keyPairResource);

    $cert = openssl_csr_sign($csr, null, $privateKey, 3652);

    openssl_x509_export($cert, $certout);

    // Extract the public key from $res to $pubKey
    $pubKeyDetails = openssl_pkey_get_details($keyPairResource);
    $pubKey = $pubKeyDetails["key"];

    $data = psuedo_random_hex_bytes(4096/8-200);
    // Encrypt the data to $encrypted using the public key
    openssl_public_encrypt($data, $encrypted, $pubKey);

    $encrypted = (hex2bin(bin2hex($encrypted)));

    // Decrypt the data using the private key and store the results in $decrypted
    openssl_private_decrypt($encrypted, $decrypted, $privateKey);

    echo "---data--\n" . $data;
    echo "\n---encrypted--\n" . bin2hex($encrypted);
    echo "\n---decrypted--\n" . $decrypted;
}

/**
 *
 * @param type $key
 * @param type $string
 * @return string Base
 */
function encrypt($key, $string) {
    return base64url_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
}

function decrypt($key, $string) {
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64url_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
}

function encryption_info() {
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

    echo $iv_size,"\n",$iv,"\n",md5($iv);
}

function demo_encode() {
    $key = 'f69I5cJKrhXXhgUTOBog';
    $string = 'string to be encrypted';

    $encrypted = encrypt($key,$string);
    $decrypted = decrypt($key,$encrypted);

    echo 'Unencrypted: ', $string, "\n";
    echo 'Encrypted:   ', $encrypted, "\n";
    echo 'Decrypted:   ', $decrypted, "\n";
}


//echo psuedo_random_urlsafe(70);
//var_dump(generate_keypair());
//exit;
//generate_keypair();

//make_certificate();

//demo_encode();
//encryption_info();

//echo psuedo_random_hex_bytes(32);

for($i=0;$i<100; $i++) {
    echo psuedo_random_number(100, 999) . "\n";

}