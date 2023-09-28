<?php

declare(strict_types=1);

/**
 * RPCErrorException module.
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

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Revolt\EventLoop;
use Throwable;

use const PHP_EOL;

use const PHP_SAPI;

/**
 * Indicates an error returned by Telegram's API.
 */
class RPCErrorException extends \Exception
{
    use TL\PrettyException;
    private bool $fetched = false;
    /** @internal */
    public static array $descriptions = ['RPC_MCGET_FAIL' => 'Telegram is having internal issues, please try again later.', 'RPC_CALL_FAIL' => 'Telegram is having internal issues, please try again later.', 'USER_PRIVACY_RESTRICTED' => "The user's privacy settings do not allow you to do this", 'CHANNEL_PRIVATE' => "You haven't joined this channel/supergroup", 'USER_IS_BOT' => "Bots can't send messages to other bots", 'BOT_METHOD_INVALID' => 'This method cannot be run by a bot', 'PHONE_CODE_EXPIRED' => 'The phone code you provided has expired, this may happen if it was sent to any chat on telegram (if the code is sent through a telegram chat (not the official account) to avoid it append or prepend to the code some chars)', 'USERNAME_INVALID' => 'The provided username is not valid', 'ACCESS_TOKEN_INVALID' => 'The provided token is not valid', 'ACTIVE_USER_REQUIRED' => 'The method is only available to already activated users', 'FIRSTNAME_INVALID' => 'The first name is invalid', 'LASTNAME_INVALID' => 'The last name is invalid', 'PHONE_NUMBER_INVALID' => 'The phone number is invalid', 'PHONE_CODE_HASH_EMPTY' => 'phone_code_hash is missing', 'PHONE_CODE_EMPTY' => 'phone_code is missing', 'API_ID_INVALID' => 'The api_id/api_hash combination is invalid', 'PHONE_NUMBER_OCCUPIED' => 'The phone number is already in use', 'PHONE_NUMBER_UNOCCUPIED' => 'The phone number is not yet being used', 'USERS_TOO_FEW' => 'Not enough users (to create a chat, for example)', 'USERS_TOO_MUCH' => 'The maximum number of users has been exceeded (to create a chat, for example)', 'TYPE_CONSTRUCTOR_INVALID' => 'The type constructor is invalid', 'FILE_PART_INVALID' => 'The file part number is invalid', 'FILE_PARTS_INVALID' => 'The number of file parts is invalid', 'MD5_CHECKSUM_INVALID' => 'The MD5 checksums do not match', 'PHOTO_INVALID_DIMENSIONS' => 'The photo dimensions are invalid', 'FIELD_NAME_INVALID' => 'The field with the name FIELD_NAME is invalid', 'FIELD_NAME_EMPTY' => 'The field with the name FIELD_NAME is missing', 'MSG_WAIT_FAILED' => 'A waiting call returned an error', 'USERNAME_NOT_OCCUPIED' => 'The provided username is not occupied', 'PHONE_NUMBER_BANNED' => 'The provided phone number is banned from telegram', 'AUTH_KEY_UNREGISTERED' => 'The authorization key has expired', 'INVITE_HASH_EXPIRED' => 'The invite link has expired', 'USER_DEACTIVATED' => 'The user was deactivated', 'USER_ALREADY_PARTICIPANT' => 'The user is already in the group', 'MESSAGE_ID_INVALID' => 'The provided message id is invalid', 'PEER_ID_INVALID' => 'The provided peer id is invalid', 'CHAT_ID_INVALID' => 'The provided chat id is invalid', 'MESSAGE_DELETE_FORBIDDEN' => "You can't delete one of the messages you tried to delete, most likely because it is a service message.", 'CHAT_ADMIN_REQUIRED' => 'You must be an admin in this chat to do this', -429 => 'Too many requests', 'PEER_FLOOD' => "You are spamreported, you can't do this"];
    /** @internal */
    public static array $errorMethodMap = [];
    private static array $fetchedError = [];
    private string $caller = '';
    private ?string $localized = null;

    private static function isBad(string $error, int $code): bool
    {
        return \in_array($error, ['PEER_FLOOD', 'INPUT_CONSTRUCTOR_INVALID_X', 'USER_DEACTIVATED_BAN', 'INPUT_METHOD_INVALID', 'INPUT_FETCH_ERROR', 'AUTH_KEY_UNREGISTERED', 'SESSION_REVOKED', 'USER_DEACTIVATED', 'RPC_SEND_FAIL', 'RPC_CALL_FAIL', 'RPC_MCGET_FAIL', 'INTERDC_5_CALL_ERROR', 'INTERDC_4_CALL_ERROR', 'INTERDC_3_CALL_ERROR', 'INTERDC_2_CALL_ERROR', 'INTERDC_1_CALL_ERROR', 'INTERDC_5_CALL_RICH_ERROR', 'INTERDC_4_CALL_RICH_ERROR', 'INTERDC_3_CALL_RICH_ERROR', 'INTERDC_2_CALL_RICH_ERROR', 'INTERDC_1_CALL_RICH_ERROR', 'AUTH_KEY_DUPLICATED', 'CONNECTION_NOT_INITED', 'LOCATION_NOT_AVAILABLE', 'AUTH_KEY_INVALID', 'LANG_CODE_EMPTY', 'memory limit exit', 'memory limit(?)', 'INPUT_REQUEST_TOO_LONG', 'SESSION_PASSWORD_NEEDED', 'INPUT_FETCH_FAIL',
            'CONNECTION_SYSTEM_EMPTY',
            'BOT_POLLS_DISABLED', 'TEMPNAM_FAILED', 'MSG_WAIT_TIMEOUT',
            'CHAT_FROM_CALL_CHANGED', 'MTPROTO_CLUSTER_INVALID',
            'CONNECTION_DEVICE_MODEL_EMPTY', 'AUTH_KEY_PERM_EMPTY', 'UNKNOWN_METHOD', 'ENCRYPTION_OCCUPY_FAILED', 'ENCRYPTION_OCCUPY_ADMIN_FAILED', 'CHAT_OCCUPY_USERNAME_FAILED', 'REG_ID_GENERATE_FAILED',
            'CONNECTION_LANG_PACK_INVALID', 'MSGID_DECREASE_RETRY', 'API_CALL_ERROR', 'STORAGE_CHECK_FAILED', 'INPUT_LAYER_INVALID', 'NEED_MEMBER_INVALID', 'NEED_CHAT_INVALID', 'HISTORY_GET_FAILED', 'CHP_CALL_FAIL', 'IMAGE_ENGINE_DOWN', 'MSG_RANGE_UNSYNC', 'PTS_CHANGE_EMPTY',
            'CONNECTION_SYSTEM_LANG_CODE_EMPTY', 'WORKER_BUSY_TOO_LONG_RETRY', 'WP_ID_GENERATE_FAILED', 'ARR_CAS_FAILED', 'CHANNEL_ADD_INVALID', 'CHANNEL_ADMINS_INVALID', 'CHAT_OCCUPY_LOC_FAILED', 'GROUPED_ID_OCCUPY_FAILED', 'GROUPED_ID_OCCUPY_FAULED', 'LOG_WRAP_FAIL', 'MEMBER_FETCH_FAILED', 'MEMBER_OCCUPY_PRIMARY_LOC_FAILED', 'MEMBER_FETCH_FAILED', 'MEMBER_NO_LOCATION', 'MEMBER_OCCUPY_USERNAME_FAILED', 'MT_SEND_QUEUE_TOO_LONG', 'POSTPONED_TIMEOUT', 'RPC_CONNECT_FAILED', 'SHORTNAME_OCCUPY_FAILED', 'STORE_INVALID_OBJECT_TYPE', 'STORE_INVALID_SCALAR_TYPE', 'TMSG_ADD_FAILED', 'UNKNOWN_ERROR', 'UPLOAD_NO_VOLUME', 'USER_NOT_AVAILABLE', 'VOLUME_LOC_NOT_FOUND',
        ], true)
                || \str_contains($error, 'Received bad_msg_notification')
                || \str_contains($error, 'FLOOD_WAIT_')
                || \str_contains($error, '_MIGRATE_')
                || \str_contains($error, 'INPUT_METHOD_INVALID')
                || \str_contains($error, 'INPUT_CONSTRUCTOR_INVALID')
                || \str_contains($error, 'https://telegram.org/dl')
                || \str_starts_with($error, 'Received bad_msg_notification')
                || \str_starts_with($error, 'No workers running')
                || \str_starts_with($error, 'All workers are busy. Active_queries ')
                || \preg_match('/FILE_PART_\d*_MISSING/', $error);
    }

    public static function localizeMessage($method, int $code, string $error): string
    {
        if (!$method || !$code || !$error) {
            return $error;
        }
        $error = \preg_replace('/\\d+$/', 'X', $error);
        $description = self::$descriptions[$error] ?? '';
        if (
            !isset(self::$fetchedError[$error])
            && (!isset(self::$errorMethodMap[$code][$method][$error]) || !isset(self::$descriptions[$error]))
            && !self::isBad($error, $code)
            && !($error === 'Timeout' && !\in_array(\strtolower($method), ['messages.getbotcallbackanswer', 'messages.getinlinebotresults'], true))
        ) {
            EventLoop::queue(function () use ($method, $code, $error): void {
                try {
                    $res = \json_decode(
                        (
                            HttpClientBuilder::buildDefault()
                            ->request(new Request('https://rpc.pwrtelegram.xyz/?method='.$method.'&code='.$code.'&error='.$error))
                        )->getBody()->buffer(),
                        true,
                    );
                    if (isset($res['ok']) && $res['ok'] && isset($res['result']) && \is_string($res['result'])) {
                        $description = $res['result'];
                        self::$descriptions[$error] = $description;
                        self::$errorMethodMap[$code][$method][$error] = $error;
                    }
                    self::$fetchedError[$error] = true;
                } catch (Throwable) {
                }
            });
        }
        if (!$description) {
            return $error;
        }
        return $description;
    }
    public function __toString(): string
    {
        Magic::start(light: true);
        $this->localized ??= self::localizeMessage($this->caller, $this->code, $this->message);
        $result = \sprintf(Lang::$current_lang['rpc_tg_error'], $this->localized." ({$this->code})", $this->rpc, $this->file, $this->line.PHP_EOL, Magic::$revision.PHP_EOL.PHP_EOL).PHP_EOL.$this->getTLTrace().PHP_EOL;
        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
            $result = \str_replace(PHP_EOL, '<br>'.PHP_EOL, $result);
        }
        return $result;
    }
    /**
     * Get localized error name.
     */
    public function getLocalization(): string
    {
        $this->localized ??= self::localizeMessage($this->caller, $this->code, $this->message);
        return $this->localized;
    }
    /**
     * Set localized error name.
     */
    public function setLocalization(string $localization): void
    {
        $this->localized = $localization;
    }
    public function __construct(
        /** @var string RPC error */
        public readonly string $rpc,
        int $code = 0,
        $caller = '',
        ?Exception $previous = null
    ) {
        parent::__construct($rpc, $code, $previous);
        if (\is_string($caller)) {
            $this->prettifyTL($caller);
            $this->caller = $caller;
            $additional = [];
            foreach ($this->getTrace() as $level) {
                if (isset($level['function']) && $level['function'] === 'methodCall') {
                    $this->line = $level['line'];
                    $this->file = $level['file'];
                }
            }
        }
        $this->getLocalization();
    }
}
