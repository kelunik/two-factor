<?php

namespace Kelunik\TwoFactor;

use ParagonIE\ConstantTime\Base32;

/** @final */
class Oath
{
    private $length;
    private $windowSize;

    public function __construct(int $length = 6, int $windowSize = 30)
    {
        $this->length = $length;
        $this->windowSize = $windowSize;
    }

    public function generateKey($length = 20)
    {
        if (!\is_int($length)) {
            throw new \InvalidArgumentException("Length must be int");
        }

        if ($length < 16) {
            throw new \InvalidArgumentException("Keys shorter than 16 bytes are not supported!");
        }

        return \random_bytes($length);
    }

    public function encodeKey($key)
    {
        if (!\is_string($key)) {
            throw new \InvalidArgumentException("Key must be string");
        }

        return Base32::encode($key);
    }

    public function generateHotp($key, $counter)
    {
        if (!\is_string($key)) {
            throw new \InvalidArgumentException("Key must be string");
        }

        if (!\is_int($counter)) {
            throw new \InvalidArgumentException("Counter must be int");
        }

        if (\strlen($key) < 16) {
            throw new \InvalidArgumentException("Key too short.");
        }

        $counter = \pack("N*", 0, $counter);
        $rawHmac = \hash_hmac("sha1", $counter, $key, true);
        $oath = $this->oathTruncate($rawHmac);

        return \str_pad($oath, $this->length, "0", STR_PAD_LEFT);
    }

    public function generateTotp($key, $time = null)
    {
        return $this->generateHotp($key, $this->getTimeWindow($time ?: \time()));
    }

    public function verifyHotp($key, $value, $counter)
    {
        if (!\is_string($key)) {
            throw new \InvalidArgumentException("Key must be string");
        }

        if (!\is_string($value)) {
            throw new \InvalidArgumentException("Value must be string");
        }

        if (!\is_int($counter)) {
            throw new \InvalidArgumentException("Counter must be int");
        }

        return \hash_equals($value, $this->generateHotp($key, $counter));
    }

    public function verifyTotp($key, $value, $graceWindows = 2, $currentTime = null)
    {
        if (!\is_string($key)) {
            throw new \InvalidArgumentException("Key must be string");
        }

        if (!\is_string($value)) {
            throw new \InvalidArgumentException("Value must be string");
        }

        if (!\is_int($graceWindows) || $graceWindows < 0 || $graceWindows > 5) {
            throw new \InvalidArgumentException("Grace windows must be int and between 0 and 5");
        }

        $currentTime = $currentTime ?: \time();

        if (!\is_int($currentTime)) {
            throw new \InvalidArgumentException("Current time must be int");
        }

        $valid = false;

        for ($i = 0; $i <= $graceWindows; $i++) {
            $hotp = $this->generateHotp($key, $this->getTimeWindow($currentTime));
            $currentValid = \hash_equals($hotp, $value);

            $valid = $valid || $currentValid;
            $currentTime -= $this->windowSize;
        }

        return $valid;
    }

    /** @see https://github.com/google/google-authenticator/wiki/Key-Uri-Format */
    public function getUri($key, $issuer, $account)
    {
        if (!\is_string($key)) {
            throw new \InvalidArgumentException("Key must be string");
        }

        if (!\is_string($issuer)) {
            throw new \InvalidArgumentException("Issuer must be string");
        }

        if (!\is_string($account)) {
            throw new \InvalidArgumentException("Account must be string");
        }

        return "otpauth://totp/" . \urlencode($issuer) . ":" . \urlencode($account) . "?" . \http_build_query([
            "algorithm" => "SHA1",
            "secret" => $this->encodeKey($key),
            "digits" => $this->length,
            "period" => $this->windowSize,
            "issuer" => $issuer,
        ]);
    }

    private function getTimeWindow(int $time = null): int
    {
        $time = $time ?: \time();

        return (int) \floor($time / $this->windowSize);
    }

    /**
     * @see https://tools.ietf.org/html/rfc4226#section-5.3
     */
    private function oathTruncate($rawHmac): int
    {
        // Take lower 4 bit as offset
        $offset = \ord($rawHmac[19]) & 0x0F;

        // Extract 32 bit string from 160 byte HMAC
        $p = \unpack("N", \substr($rawHmac, $offset, 4));

        // Mask first bit due to signed / unsigned modulo operations
        // And extract HOTP value according to OTP_LENGTH
        return ($p[1] & 0x7FFFFFFF) % (10 ** $this->length);
    }
}
