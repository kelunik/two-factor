<?php

namespace Kelunik\TwoFactor;

class Oath {
    public static function generateKey($length = 20) {
        if (!is_int($length)) {
            throw new \InvalidArgumentException("Length must be int");
        }

        if ($length < 16) {
            throw new \InvalidArgumentException("Keys shorter than 16 bytes are not supported!");
        }

        return random_bytes($length);
    }

    public static function generateHotp($key, $counter, $length = 8) {
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Key must be string");
        }

        if (!is_int($counter)) {
            throw new \InvalidArgumentException("Counter must be int");
        }

        if (!is_int($length)) {
            throw new \InvalidArgumentException("Length must be int");
        }

        if (strlen($key) < 16) {
            throw new \InvalidArgumentException("Key too short.");
        }

        if ($length < 6) {
            throw new \InvalidArgumentException("OATH tokens must be at least 6 digits long.");
        }

        $counter = pack("N*", 0, $counter);
        $rawHmac = hash_hmac("sha1", $counter, $key, true);
        $oath = self::oathTruncate($rawHmac, $length);

        return str_pad($oath, $length, "0", STR_PAD_LEFT);
    }

    public static function getTimeWindow($time = null, $windowSize = 30) {
        if ($time !== null && !is_int($time)) {
            throw new \InvalidArgumentException("Time must be int");
        }

        if (!is_int($windowSize)) {
            throw new \InvalidArgumentException("Window size must be int");
        }

        $time = $time ?? time();

        return floor($time / $windowSize);
    }

    /**
     * @see https://tools.ietf.org/html/rfc4226#section-5.3
     */
    private static function oathTruncate($rawHmac, $length) {
        // Take lower 4 bit as offset
        $offset = ord($rawHmac[19]) & 0x0F;

        // Extract 32 bit string from 160 byte HMAC
        $p = unpack("N", substr($rawHmac, $offset, 4));

        // Mask first bit due to signed / unsigned modulo operations
        // And extract HOTP value according to OTP_LENGTH
        return ($p[1] & 0x7FFFFFFF) % 10 ** $length;
    }
}