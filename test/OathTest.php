<?php

namespace Kelunik\TwoFactor;

class OathTest extends \PHPUnit_Framework_TestCase {
    const KEY = "12345678901234567890";

    /**
     * @dataProvider provideRfcTestData
     */
    public function test(int $time, string $totp) {
        $this->assertSame($totp, Oath::generateHotp(self::KEY, Oath::getTimeWindow($time)));
    }

    public function provideRfcTestData() {
        return [
            [59, "94287082"],
            [1111111109, "07081804"],
            [1111111111, "14050471"],
            [1234567890, "89005924"],
            [2000000000, "69279037"],
            [20000000000, "65353130"],
        ];
    }
}