#!/usr/bin/env php
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

/*
 * Usage: generate.php [key]
 *
 * If no key is provided as an arg, the script will ask for it.
 *
 */

include __DIR__ . '/vendor/autoload.php';

use williameggers\phptotp\Base32;
use williameggers\phptotp\Totp;

$key = '';

if (2 == $argc) {
    $key = $argv[1];
} else {
    echo 'Enter secret key: ';
    $key = trim(fgets(STDIN));

    if ('' == $key) {
        echo "No key provided\n";

        exit(1);
    }
}

$key = Base32::decode($key);

echo 'Token: ' . (new Totp())->generateToken($key) . "\n";
