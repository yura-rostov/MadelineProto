<?php

declare(strict_types=1);

/**
 * Legacy BigInteger module.
 *
 * This file is part of MadelineProto.
 * MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU General Public License along with MadelineProto.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2025 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPLv3
 * @link https://docs.madelineproto.xyz MadelineProto documentation
 */

namespace danog\MadelineProto;

use phpseclib4\Math\BigInteger;

/**
 * Unserializes phpseclib3 BigIntegers from old sessions.
 *
 * Old phpseclib3 versions serialized BigInteger via __sleep(), so the data contains
 * mangled private property names ("\0phpseclib3\Math\BigInteger\0hex") instead of the
 * plain "hex" key expected by phpseclib4's __unserialize().
 *
 * @internal
 */
final class LegacyBigInteger extends BigInteger
{
    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            if (!\is_string($key) || !str_starts_with($key, "\0")) {
                continue;
            }
            $name = substr($key, strrpos($key, "\0") + 1);
            if ($name === 'hex' || $name === 'precision') {
                $data[$name] ??= $value;
            }
        }
        parent::__unserialize($data);
    }
}
