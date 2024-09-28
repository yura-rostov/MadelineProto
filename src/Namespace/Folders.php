<?php declare(strict_types=1);
/**
 * This file is automatic generated by build_docs.php file
 * and is used only for autocomplete in multiple IDE
 * don't modify manually.
 */

namespace danog\MadelineProto\Namespace;

interface Folders
{
    /**
     * Edit peers in [peer folder](https://core.telegram.org/api/folders#peer-folders).
     *
     * @param list<array{_: 'inputFolderPeer', peer?: array|int|string, folder_id?: int}>|array<never, never> $folder_peers Array of New peer list @see https://docs.madelineproto.xyz/API_docs/types/InputFolderPeer.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array @see https://docs.madelineproto.xyz/API_docs/types/Updates.html
     */
    public function editPeerFolders(array $folder_peers = [], ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;
}