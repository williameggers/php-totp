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

class Totp extends Hotp
{
    /**
     * The beginning of time.
     *
     * @var int
     */
    private $startTime;

    /**
     * Time interval between tokens.
     *
     * @var int
     */
    private $timeInterval;

    /**
     * Constructor.
     *
     * @param string $algo         Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..)
     *                             See hash_hmac_algos() for a list of supported algorithms.
     * @param int    $startTime    The beginning of time
     * @param int    $timeInterval Time interval between tokens
     */
    public function __construct(string $algo = 'sha1', int $startTime = 0, int $timeInterval = 30)
    {
        parent::__construct($algo);
        $this->startTime = $startTime;
        $this->timeInterval = $timeInterval;
    }

    /**
     * Generate Token.
     *
     * @param string   $key    Secret key as bytes, base32 decoded
     * @param null|int $time   Time to use for the token, defaults to now
     * @param int      $length Length of token
     */
    public function generateToken(string $key, ?int $time = null, int $length = 6): string
    {
        // Pad the key if necessary
        if ('sha256' === $this->algo) {
            $key = $key . substr($key, 0, 12);
        } elseif ('sha512' === $this->algo) {
            $key = $key . $key . $key . substr($key, 0, 4);
        }

        // Get the current unix timestamp if one isn't given
        if (is_null($time)) {
            $time = (new \DateTime())->getTimestamp();
        }

        // Calculate the count
        $now = $time - $this->startTime;
        $count = (int) floor($now / $this->timeInterval);

        // Generate a normal HOTP token
        return parent::generateToken($key, $count, $length);
    }
}
