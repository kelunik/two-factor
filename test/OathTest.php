<?php

namespace Kelunik\TwoFactor;

class OathTest extends \PHPUnit_Framework_TestCase
{
    const KEY = "12345678901234567890";

    /** @var Oath */
    private $oath;

    public function setUp()
    {
        $this->oath = new Oath(8, 30);
    }

    /**
     * @dataProvider provideRfcTestDataForGeneration
     */
    public function testGeneration($time, $totp)
    {
        $this->assertSame($totp, $this->oath->generateTotp(self::KEY, $time));
    }

    public function provideRfcTestDataForGeneration()
    {
        return [
            [59, "94287082"],
            [1111111109, "07081804"],
            [1111111111, "14050471"],
            [1234567890, "89005924"],
            [2000000000, "69279037"],
            [20000000000, "65353130"],
        ];
    }

    /**
     * @dataProvider provideRfcTestDataForValidation
     */
    public function testValidation($time, $totp, $result)
    {
        $this->assertSame($result, $this->oath->verifyTotp(self::KEY, $totp, 2, $time));
    }

    public function provideRfcTestDataForValidation()
    {
        return [
            [0, "94287082", false],
            [30, "94287082", true],
            [59, "94287082", true],
            [60, "94287082", true],
            [89, "94287082", true],
            [119, "94287082", true],
            [120, "94287082", false],
        ];
    }

    /**
     * @dataProvider provideKeyLengths
     */
    public function testGenerateKeyHasCorrectLength($length)
    {
        $this->assertSame($length, \strlen($this->oath->generateKey($length)));
    }

    public function provideKeyLengths()
    {
        return [
            [16],
            [17],
            [20],
            [100],
        ];
    }

    /**
     * @dataProvider provideInvalidKeyLengths
     * @expectedException \InvalidArgumentException
     */
    public function testRejectsTooShortKeyLength($length)
    {
        $this->oath->generateKey($length);
    }

    public function provideInvalidKeyLengths()
    {
        return [
            [-1],
            [0],
            [10],
            [15],
            ["16"],
            ["16ab"],
            [null],
        ];
    }
}
