<?php declare(strict_types=1);
/**
 * Copyright (c) 2021 Lee Keitel, William Eggers
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace williameggers\phptotp;

class Hotp
{
    /**
     * Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..).
     *
     * @var string
     */
    protected $algo;

    /**
     * Constructor.
     *
     * @param string $algo Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..)
     *                     See hash_hmac_algos() for a list of supported algorithms.
     */
    public function __construct(string $algo = 'sha1')
    {
        $this->algo = $algo;
    }

    /**
     * Generate Token.
     *
     * @param string   $key    Secret key as bytes, base32 decoded
     * @param null|int $count  HOTP counter
     * @param int      $length Length of token
     */
    public function generateToken(string $key, ?int $count = 0, int $length = 6): string
    {
        $count = $this->packCounter($count);
        $hash = hash_hmac($this->algo, $count, $key);
        $code = $this->genHTOPValue($hash, $length);

        $code = str_pad((string) $code, $length, '0', STR_PAD_LEFT);

        return substr($code, (-1 * $length));
    }

    /**
     * Generate Secret.
     *
     * @param int $length Length of string in bytes
     */
    public static function generateSecret(int $length = 16): string
    {
        if (0 != $length % 8) {
            throw new \Exception('Length must be a multiple of 8');
        }

        $secret = openssl_random_pseudo_bytes($length, $strong);
        if (! $strong) {
            throw new \Exception('Random string generation was not strong');
        }

        return $secret;
    }

    private function packCounter(int $counter): string
    {
        // The counter value can be more than one byte long,
        // so we need to pack it down properly.
        $curCounter = [0, 0, 0, 0, 0, 0, 0, 0];
        for ($i = 7; $i >= 0; --$i) {
            $curCounter[$i] = pack('C*', $counter);
            $counter = $counter >> 8;
        }

        $binCounter = implode($curCounter);

        // Pad to 8 chars
        if (strlen($binCounter) < 8) {
            $binCounter = str_repeat(chr(0), 8 - strlen($binCounter)) . $binCounter;
        }

        return $binCounter;
    }

    private function genHTOPValue(string $hash, int $length): int
    {
        // Store calculate decimal
        $hmacResult = [];

        // Convert to decimal
        foreach (str_split($hash, 2) as $hex) {
            $hmacResult[] = hexdec($hex);
        }

        $offset = (int) $hmacResult[count($hmacResult) - 1] & 0xF;

        $code = (int) ($hmacResult[$offset] & 0x7F) << 24
            | ($hmacResult[$offset + 1] & 0xFF) << 16
            | ($hmacResult[$offset + 2] & 0xFF) << 8
            | ($hmacResult[$offset + 3] & 0xFF);

        return $code % pow(10, $length);
    }
}
