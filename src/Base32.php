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

// Original source: https://github.com/bbars/utils/tree/master/php-base32-encode-decode
class Base32
{
    public const BITS_5_RIGHT = 31;
    protected static $CHARS = 'abcdefghijklmnopqrstuvwxyz234567';

    /**
     * Data to base32 encode.
     */
    public static function encode(string $data): string
    {
        $dataSize = strlen($data);
        $res = '';
        $remainder = 0;
        $remainderSize = 0;

        for ($i = 0; $i < $dataSize; ++$i) {
            $b = ord($data[$i]);
            $remainder = ($remainder << 8) | $b;
            $remainderSize += 8;
            while ($remainderSize > 4) {
                $remainderSize -= 5;
                $c = $remainder & (self::BITS_5_RIGHT << $remainderSize);
                $c >>= $remainderSize;
                $res .= self::$CHARS[$c];
            }
        }
        if ($remainderSize > 0) {
            // remainderSize < 5:
            $remainder <<= (5 - $remainderSize);
            $c = $remainder & self::BITS_5_RIGHT;
            $res .= self::$CHARS[$c];
        }

        return $res;
    }

    /**
     * Data to base32 decode.
     */
    public static function decode(string $data): string
    {
        $data = strtolower($data);
        $dataSize = strlen($data);
        $buf = 0;
        $bufSize = 0;
        $res = '';

        for ($i = 0; $i < $dataSize; ++$i) {
            $c = $data[$i];
            $b = strpos(self::$CHARS, $c);
            if (false === $b) {
                throw new \Exception('Encoded string is invalid, it contains unknown char #' . ord($c));
            }
            $buf = ($buf << 5) | $b;
            $bufSize += 5;
            if ($bufSize > 7) {
                $bufSize -= 8;
                $b = ($buf & (0xFF << $bufSize)) >> $bufSize;
                $res .= chr($b);
            }
        }

        return $res;
    }
}
