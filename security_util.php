<?php
function encryptField($plaintext) {
    if ($plaintext === null || $plaintext === '') {
        return [null, null];
    }

    $key = IMPACT_ENC_KEY;
    $iv  = random_bytes(16); // 16 bytes for AES-256-CBC

    $ciphertext = openssl_encrypt(
        $plaintext,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );

    return [
        base64_encode($ciphertext),
        base64_encode($iv)
    ];
}

function decryptField($cipher_b64, $iv_b64) {
    if (!$cipher_b64 || !$iv_b64) {
        return null;
    }

    $key = IMPACT_ENC_KEY;
    $cipherRaw = base64_decode($cipher_b64);
    $iv        = base64_decode($iv_b64);

    return openssl_decrypt(
        $cipherRaw,
        'AES-256-CBC',
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
}
