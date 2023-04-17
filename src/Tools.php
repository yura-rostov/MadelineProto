<?php

declare(strict_types=1);

/**
 * Tools module.
 *
 * This file is part of MadelineProto.
 * MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU General Public License along with MadelineProto.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2023 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPLv3
 * @link https://docs.madelineproto.xyz MadelineProto documentation
 */

namespace danog\MadelineProto;

use ArrayAccess;
use Closure;
use Countable;
use Exception;
use Fiber;
use phpseclib3\Crypt\Random;
use Throwable;
use Traversable;
use Webmozart\Assert\Assert;

use const DIRECTORY_SEPARATOR;

use const PHP_INT_MAX;
use const PHP_SAPI;
use const STR_PAD_RIGHT;
use function unpack;

/**
 * Some tools.
 */
abstract class Tools extends AsyncTools
{
    /**
     * Test fibers.
     *
     * @return array{maxFibers: int, realMemoryMb: int, maps: ?int, maxMaps: ?int}
     */
    public static function testFibers(int $fiberCount = 100000): array
    {
        \ini_set('memory_limit', -1);

        $f = [];
        for ($x = 0; $x < $fiberCount; $x++) {
            try {
                $f []= $cur = new Fiber(function (): void {
                    Fiber::suspend();
                });
                $cur->start();
            } catch (\Throwable $e) {
                break;
            }
        }
        return [
            'maxFibers' => $x,
            'realMemoryMb' => (int) (\memory_get_usage(true)/1024/1024),
            'maps' => self::getMaps(),
            'maxMaps' => self::getMaxMaps(),
        ];
    }
    /**
     * Get current number of memory-mapped regions, UNIX only.
     */
    public static function getMaps(): ?int
    {
        try {
            if (\file_exists('/proc/self/maps')) {
                return \substr_count(@\file_get_contents('/proc/self/maps'), "\n")-1;
            }
            $pid = \getmypid();
            if (\file_exists("/proc/$pid/maps")) {
                return \substr_count(@\file_get_contents("/proc/$pid/maps"), "\n")-1;
            }
            return null;
        } catch (\Throwable) {
            return null;
        }
    }
    /**
     * Get maximum number of memory-mapped regions, UNIX only.
     * Use testFibers to get the maximum number of fibers on any platform.
     */
    public static function getMaxMaps(): ?int
    {
        try {
            return ((int) @\file_get_contents('/proc/sys/vm/max_map_count')) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
    /**
     * Sanify TL obtained from JSON for TL serialization.
     *
     * @param array $input Data to sanitize
     * @internal
     */
    public static function convertJsonTL(array $input): array
    {
        $cb = static function (&$val) use (&$cb): void {
            if (isset($val['@type'])) {
                $val['_'] = $val['@type'];
            } elseif (\is_array($val)) {
                \array_walk($val, $cb);
            }
        };
        \array_walk($input, $cb);
        return $input;
    }
    /**
     * Generate MTProto vector hash.
     *
     * @param array $ints IDs
     * @return string Vector hash
     */
    public static function genVectorHash(array $ints): string
    {
        $hash = 0;
        foreach ($ints as $id) {
            $hash = $hash ^ ($id >> 21);
            $hash = $hash ^ ($id << 35);
            $hash = $hash ^ ($id >> 4);
            $hash = $hash + $id;
        }
        return self::packSignedLong($hash);
    }
    /**
     * Get random integer.
     *
     * @param integer $modulus Modulus
     */
    public static function randomInt(int $modulus = 0): int
    {
        if ($modulus === 0) {
            $modulus = PHP_INT_MAX;
        }
        try {
            return \random_int(0, PHP_INT_MAX) % $modulus;
        } catch (Exception $e) {
            // random_compat will throw an Exception, which in PHP 5 does not implement Throwable
        } catch (Throwable $e) {
            // If a sufficient source of randomness is unavailable, random_bytes() will throw an
            // object that implements the Throwable interface (Exception, TypeError, Error).
            // We don't actually need to do anything here. The string() method should just continue
            // as normal.
        }
        $number = self::unpackSignedLong(self::random(8));
        return ($number & PHP_INT_MAX) % $modulus;
    }
    /**
     * Get random string of specified length.
     *
     * @param integer $length Length
     * @return string Random string
     */
    public static function random(int $length): string
    {
        return $length === 0 ? '' : Random::string($length);
    }
    /**
     * Positive modulo
     * Works just like the % (modulus) operator, only returns always a postive number.
     *
     * @param int $a A
     * @param int $b B
     * @return int Modulo
     */
    public static function posmod(int $a, int $b): int
    {
        $resto = $a % $b;
        return $resto < 0 ? $resto + \abs($b) : $resto;
    }
    /**
     * Unpack base256 signed int.
     *
     * @param string $value base256 int
     */
    public static function unpackSignedInt(string $value): int
    {
        if (\strlen($value) !== 4) {
            throw new TL\Exception(Lang::$current_lang['length_not_4']);
        }
        return \unpack('l', Magic::$BIG_ENDIAN ? \strrev($value) : $value)[1];
    }
    /**
     * Unpack base256 signed long.
     *
     * @param string $value base256 long
     */
    public static function unpackSignedLong(string $value): int
    {
        if (\strlen($value) !== 8) {
            throw new TL\Exception(Lang::$current_lang['length_not_8']);
        }
        return \unpack('q', Magic::$BIG_ENDIAN ? \strrev($value) : $value)[1];
    }
    /**
     * Unpack base256 signed long to string.
     *
     * @param string|int|array $value base256 long
     */
    public static function unpackSignedLongString(string|int|array $value): string
    {
        if (\is_int($value)) {
            return (string) $value;
        }
        if (\is_array($value) && \count($value) === 2) {
            $value = \pack('l2', $value);
        }
        if (\strlen($value) !== 8) {
            throw new TL\Exception(Lang::$current_lang['length_not_8']);
        }
        return (string) self::unpackSignedLong($value);
    }
    /**
     * Convert integer to base256 signed int.
     *
     * @param integer $value Value to convert
     */
    public static function packSignedInt(int $value): string
    {
        if ($value > 2147483647) {
            throw new TL\Exception(\sprintf(Lang::$current_lang['value_bigger_than_2147483647'], $value));
        }
        if ($value < -2147483648) {
            throw new TL\Exception(\sprintf(Lang::$current_lang['value_smaller_than_2147483648'], $value));
        }
        $res = \pack('l', $value);
        return Magic::$BIG_ENDIAN ? \strrev($res) : $res;
    }
    /**
     * Convert integer to base256 long.
     *
     * @param int $value Value to convert
     */
    public static function packSignedLong(int $value): string
    {
        return Magic::$BIG_ENDIAN ? \strrev(\pack('q', $value)) : \pack('q', $value);
    }
    /**
     * Convert value to unsigned base256 int.
     *
     * @param int $value Value
     */
    public static function packUnsignedInt(int $value): string
    {
        if ($value > 4294967295) {
            throw new TL\Exception(\sprintf(Lang::$current_lang['value_bigger_than_4294967296'], $value));
        }
        if ($value < 0) {
            throw new TL\Exception(\sprintf(Lang::$current_lang['value_smaller_than_0'], $value));
        }
        return \pack('V', $value);
    }
    /**
     * Convert double to binary version.
     *
     * @param float $value Value to convert
     */
    public static function packDouble(float $value): string
    {
        $res = \pack('d', $value);
        if (\strlen($res) !== 8) {
            throw new TL\Exception(Lang::$current_lang['encode_double_error']);
        }
        return Magic::$BIG_ENDIAN ? \strrev($res) : $res;
    }
    /**
     * Unpack binary double.
     *
     * @param string $value Value to unpack
     */
    public static function unpackDouble(string $value): float
    {
        if (\strlen($value) !== 8) {
            throw new TL\Exception(Lang::$current_lang['length_not_8']);
        }
        return \unpack('d', Magic::$BIG_ENDIAN ? \strrev($value) : $value)[1];
    }
    /**
     * Check if is array or similar (traversable && countable && arrayAccess).
     *
     * @param mixed $var Value to check
     */
    public static function isArrayOrAlike(mixed $var): bool
    {
        return \is_array($var) || $var instanceof ArrayAccess && $var instanceof Traversable && $var instanceof Countable;
    }
    /**
     * Create array.
     *
     * @param mixed ...$params Params
     */
    public static function arr(mixed ...$params): array
    {
        return $params;
    }
    /**
     * base64URL decode.
     *
     * @param string $data Data to decode
     */
    public static function base64urlDecode(string $data): string
    {
        return \base64_decode(\str_pad(\strtr($data, '-_', '+/'), \strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    /**
     * Base64URL encode.
     *
     * @param string $data Data to encode
     */
    public static function base64urlEncode(string $data): string
    {
        return \rtrim(\strtr(\base64_encode($data), '+/', '-_'), '=');
    }
    /**
     * null-byte RLE decode.
     *
     * @param string $string Data to decode
     */
    public static function rleDecode(string $string): string
    {
        $new = '';
        $last = '';
        $null = \chr(0);
        foreach (\str_split($string) as $cur) {
            if ($last === $null) {
                $new .= \str_repeat($last, \ord($cur));
                $last = '';
            } else {
                $new .= $last;
                $last = $cur;
            }
        }
        $string = $new.$last;
        return $string;
    }
    /**
     * null-byte RLE encode.
     *
     * @param string $string Data to encode
     */
    public static function rleEncode(string $string): string
    {
        $new = '';
        $count = 0;
        $null = \chr(0);
        foreach (\str_split($string) as $cur) {
            if ($cur === $null) {
                $count++;
            } else {
                if ($count > 0) {
                    $new .= $null.\chr($count);
                    $count = 0;
                }
                $new .= $cur;
            }
        }
        return $new;
    }
    private const INFLATE_HEADER = "\xff\xd8\xff\xe0\x00\x10\x4a\x46\x49".
        "\x46\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xff\xdb\x00\x43\x00\x28\x1c".
        "\x1e\x23\x1e\x19\x28\x23\x21\x23\x2d\x2b\x28\x30\x3c\x64\x41\x3c\x37\x37".
        "\x3c\x7b\x58\x5d\x49\x64\x91\x80\x99\x96\x8f\x80\x8c\x8a\xa0\xb4\xe6\xc3".
        "\xa0\xaa\xda\xad\x8a\x8c\xc8\xff\xcb\xda\xee\xf5\xff\xff\xff\x9b\xc1\xff".
        "\xff\xff\xfa\xff\xe6\xfd\xff\xf8\xff\xdb\x00\x43\x01\x2b\x2d\x2d\x3c\x35".
        "\x3c\x76\x41\x41\x76\xf8\xa5\x8c\xa5\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8".
        "\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8".
        "\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8\xf8".
        "\xf8\xf8\xf8\xf8\xf8\xff\xc0\x00\x11\x08\x00\x00\x00\x00\x03\x01\x22\x00".
        "\x02\x11\x01\x03\x11\x01\xff\xc4\x00\x1f\x00\x00\x01\x05\x01\x01\x01\x01".
        "\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08".
        "\x09\x0a\x0b\xff\xc4\x00\xb5\x10\x00\x02\x01\x03\x03\x02\x04\x03\x05\x05".
        "\x04\x04\x00\x00\x01\x7d\x01\x02\x03\x00\x04\x11\x05\x12\x21\x31\x41\x06".
        "\x13\x51\x61\x07\x22\x71\x14\x32\x81\x91\xa1\x08\x23\x42\xb1\xc1\x15\x52".
        "\xd1\xf0\x24\x33\x62\x72\x82\x09\x0a\x16\x17\x18\x19\x1a\x25\x26\x27\x28".
        "\x29\x2a\x34\x35\x36\x37\x38\x39\x3a\x43\x44\x45\x46\x47\x48\x49\x4a\x53".
        "\x54\x55\x56\x57\x58\x59\x5a\x63\x64\x65\x66\x67\x68\x69\x6a\x73\x74\x75".
        "\x76\x77\x78\x79\x7a\x83\x84\x85\x86\x87\x88\x89\x8a\x92\x93\x94\x95\x96".
        "\x97\x98\x99\x9a\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xb2\xb3\xb4\xb5\xb6".
        "\xb7\xb8\xb9\xba\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xd2\xd3\xd4\xd5\xd6".
        "\xd7\xd8\xd9\xda\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xf1\xf2\xf3\xf4".
        "\xf5\xf6\xf7\xf8\xf9\xfa\xff\xc4\x00\x1f\x01\x00\x03\x01\x01\x01\x01\x01".
        "\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08".
        "\x09\x0a\x0b\xff\xc4\x00\xb5\x11\x00\x02\x01\x02\x04\x04\x03\x04\x07\x05".
        "\x04\x04\x00\x01\x02\x77\x00\x01\x02\x03\x11\x04\x05\x21\x31\x06\x12\x41".
        "\x51\x07\x61\x71\x13\x22\x32\x81\x08\x14\x42\x91\xa1\xb1\xc1\x09\x23\x33".
        "\x52\xf0\x15\x62\x72\xd1\x0a\x16\x24\x34\xe1\x25\xf1\x17\x18\x19\x1a\x26".
        "\x27\x28\x29\x2a\x35\x36\x37\x38\x39\x3a\x43\x44\x45\x46\x47\x48\x49\x4a".
        "\x53\x54\x55\x56\x57\x58\x59\x5a\x63\x64\x65\x66\x67\x68\x69\x6a\x73\x74".
        "\x75\x76\x77\x78\x79\x7a\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x92\x93\x94".
        "\x95\x96\x97\x98\x99\x9a\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xb2\xb3\xb4".
        "\xb5\xb6\xb7\xb8\xb9\xba\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xd2\xd3\xd4".
        "\xd5\xd6\xd7\xd8\xd9\xda\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xf2\xf3\xf4".
        "\xf5\xf6\xf7\xf8\xf9\xfa\xff\xda\x00\x0c\x03\x01\x00\x02\x11\x03\x11\x00".
        "\x3f\x00";
    private const INFLATE_FOOTER = "\xff\xd9";
    /**
     * Inflate stripped photosize to full JPG payload.
     *
     * @param string $stripped Stripped photosize
     * @return string JPG payload
     */
    public static function inflateStripped(string $stripped): string
    {
        if (\strlen($stripped) < 3 || \ord($stripped[0]) !== 1) {
            return $stripped;
        }
        $header = self::INFLATE_HEADER;
        $header[164] = $stripped[1];
        $header[166] = $stripped[2];
        return $header.\substr($stripped, 3).self::INFLATE_FOOTER;
    }
    /**
     * Close connection with client, connected via web.
     *
     * @param string $message Message
     */
    public static function closeConnection(string $message): void
    {
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg' || isset($GLOBALS['exited']) || \headers_sent() || isset($_GET['MadelineSelfRestart']) || Magic::$isIpcWorker) {
            return;
        }
        $buffer = @\ob_get_clean() ?: '';
        $buffer .= $message;
        \ignore_user_abort(true);
        \header('Connection: close');
        \header('Content-Type: text/html');
        echo $buffer;
        \flush();
        $GLOBALS['exited'] = true;
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
    /**
     * Get maximum photo size.
     *
     * @internal
     */
    public static function maxSize(array $sizes): array
    {
        $maxPixels = 0;
        $max = null;
        foreach ($sizes as $size) {
            if (isset($size['w'], $size['h'])) {
                $curPixels = $size['w'] * $size['h'];
                if ($curPixels > $maxPixels) {
                    $maxPixels = $curPixels;
                    $max = $size;
                }
            }
        }
        if (!$max) {
            $maxType = 0;
            foreach ($sizes as $size) {
                $curType = \ord($size['type']);
                if ($curType > $maxType) {
                    $maxType = $curType;
                    $max = $size;
                }
            }
        }
        Assert::isArray($max);
        return $max;
    }
    /**
     * Get final element of array.
     *
     * @param array $what Array
     */
    public static function end(array $what)
    {
        return \end($what);
    }
    /**
     * Whether this is altervista.
     */
    public static function isAltervista(): bool
    {
        return Magic::$altervista;
    }
    /**
     * Checks private property exists in an object.
     *
     * @param object $obj Object
     * @param string $var Attribute name
     * @psalm-suppress InvalidScope
     * @access public
     */
    public static function hasVar(object $obj, string $var): bool
    {
        return Closure::bind(
            fn () => isset($this->{$var}),
            $obj,
            $obj::class,
        )->__invoke();
    }
    /**
     * Accesses a private variable from an object.
     *
     * @param object $obj Object
     * @param string $var Attribute name
     * @psalm-suppress InvalidScope
     * @access public
     */
    public static function &getVar(object $obj, string $var)
    {
        return Closure::bind(
            fn &() => $this->{$var},
            $obj,
            $obj::class,
        )->__invoke();
    }
    /**
     * Sets a private variable in an object.
     *
     * @param object $obj Object
     * @param string $var Attribute name
     * @param mixed  $val Attribute value
     * @psalm-suppress InvalidScope
     * @access public
     */
    public static function setVar(object $obj, string $var, mixed &$val): void
    {
        Closure::bind(
            function () use ($var, &$val): void {
                $this->{$var} =& $val;
            },
            $obj,
            $obj::class,
        )->__invoke();
    }
    /**
     * Get absolute path to file, related to session path.
     *
     * @param string $file File
     * @internal
     */
    public static function absolute(string $file): string
    {
        if (($file[0] ?? '') !== '/' && ($file[1] ?? '') !== ':' && !\in_array(\substr($file, 0, 4), ['phar', 'http'])) {
            $file = Magic::getcwd().DIRECTORY_SEPARATOR.$file;
        }
        return $file;
    }
    /**
     * Parse t.me link.
     *
     * @internal
     * @return array{0: bool, 1: string}|null
     */
    public static function parseLink(string $link): array|null
    {
        if (\preg_match('@([a-z0-9_-]*)\\.(?:t|telegram)\.(?:me|dog)@', $link, $matches)) {
            if ($matches[1] !== 'www') {
                return [false, $matches[1]];
            }
        }
        if (\preg_match('@(?:t|telegram)\\.(?:me|dog)/(joinchat/|\+)?([a-z0-9_-]*)@i', $link, $matches)) {
            return [!!$matches[1], $matches[2]];
        }
        return null;
    }
}
