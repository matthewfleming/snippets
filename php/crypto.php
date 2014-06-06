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

function generate_keypair($digestAlgorithm = "sha512", $bits = 4096, $keyType = OPENSSL_KEYTYPE_RSA) {
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
        "countryName"            => "AU",
        "stateOrProvinceName"    => "WA",
        "localityName" => "Perth",
        "organizationName" => "The West Australian",
        "organizationalUnitName" => "Circulation Department",
        "commonName" => "circdemo.wanews.com.au",
        "emailAddress" => "matthew.fleming@wanews.com.au"
    );

    $csr  = openssl_csr_new ($dn, $keyPairResource);
    
    $cert = openssl_csr_sign($csr, null, $privateKey, 3652);
    
    openssl_x509_export($cert, $certout);
    echo $certout;
    exit;

    // Extract the public key from $res to $pubKey
    $pubKeyDetails = openssl_pkey_get_details($res);
    $pubKey = $pubKeyDetails["key"];

    $data = 'plaintext data goes here';

    // Encrypt the data to $encrypted using the public key
    openssl_public_encrypt($data, $encrypted, $pubKey);

    // Decrypt the data using the private key and store the results in $decrypted
    openssl_private_decrypt($encrypted, $decrypted, $privateKey);

    echo $decrypted;
}

//echo psuedo_random_urlsafe(70);

var_dump(generate_keypair());
exit;
generate_keypair();
