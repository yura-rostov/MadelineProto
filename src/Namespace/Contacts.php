<?php declare(strict_types=1);
/**
 * This file is automatic generated by build_docs.php file
 * and is used only for autocomplete in multiple IDE
 * don't modify manually.
 */

namespace danog\MadelineProto\Namespace;

interface Contacts
{
    /**
     * Get the telegram IDs of all contacts.
     * Returns an array of Telegram user IDs for all contacts (0 if a contact does not have an associated Telegram account or have hidden their account using privacy settings).
     *
     * @param list<int|string>|array<never, never> $hash Array of [Hash for pagination, for more info click here](https://core.telegram.org/api/offsets#hash-generation) @see https://docs.madelineproto.xyz/API_docs/types/int|string.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return list<int>
     */
    public function getContactIDs(array $hash = [], ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Use this method to obtain the online statuses of all contacts with an accessible Telegram account.
     *
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return list<array{_: 'contactStatus', user_id: int, status: array{_: 'userStatusEmpty'}|array{_: 'userStatusOnline', expires: int}|array{_: 'userStatusOffline', was_online: int}|array{_: 'userStatusRecently', by_me: bool}|array{_: 'userStatusLastWeek', by_me: bool}|array{_: 'userStatusLastMonth', by_me: bool}}> Array of  @see https://docs.madelineproto.xyz/API_docs/types/ContactStatus.html
     */
    public function getStatuses(?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Returns the current user's contact list.
     *
     * @param list<int|string>|array<never, never> $hash Array of If there already is a full contact list on the client, a [hash](https://core.telegram.org/api/offsets#hash-generation) of a the list of contact IDs in ascending order may be passed in this parameter. If the contact set was not changed, [(contacts.contactsNotModified)](https://docs.madelineproto.xyz/API_docs/constructors/contacts.contactsNotModified.html) will be returned. @see https://docs.madelineproto.xyz/API_docs/types/int|string.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array{_: 'contacts.contactsNotModified'}|array{_: 'contacts.contacts', contacts: list<array{_: 'contact', mutual: bool, user_id: int}>, saved_count: int, users: list<array|int|string>} @see https://docs.madelineproto.xyz/API_docs/types/contacts.Contacts.html
     */
    public function getContacts(array $hash = [], ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Imports contacts: saves a full list on the server, adds already registered contacts to the contact list, returns added contacts and their info.
     *
     * Use [contacts.addContact](https://docs.madelineproto.xyz/API_docs/methods/contacts.addContact.html) to add Telegram contacts without actually using their phone number.
     *
     * @param list<array{_: 'inputPhoneContact', client_id?: int, phone?: string, first_name?: string, last_name?: string}>|array<never, never> $contacts Array of List of contacts to import @see https://docs.madelineproto.xyz/API_docs/types/InputContact.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array{_: 'contacts.importedContacts', imported: list<array{_: 'importedContact', user_id: int, client_id: int}>, popular_invites: list<array{_: 'popularContact', client_id: int, importers: int}>, retry_contacts: list<int>, users: list<array|int|string>} @see https://docs.madelineproto.xyz/API_docs/types/contacts.ImportedContacts.html
     */
    public function importContacts(array $contacts = [], ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Deletes several contacts from the list.
     *
     * @param list<array|int|string>|array<never, never> $id Array of User ID list @see https://docs.madelineproto.xyz/API_docs/types/InputUser.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array @see https://docs.madelineproto.xyz/API_docs/types/Updates.html
     */
    public function deleteContacts(array $id = [], ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Delete contacts by phone number.
     *
     * @param list<string>|array<never, never> $phones Phone numbers
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     */
    public function deleteByPhones(array $phones = [], ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): bool;

    /**
     * Adds a peer to a blocklist, see [here »](https://core.telegram.org/api/block) for more info.
     *
     * @param bool $my_stories_from Whether the peer should be added to the story blocklist; if not set, the peer will be added to the main blocklist, see [here »](https://core.telegram.org/api/block) for more info.
     * @param array|int|string $id Peer @see https://docs.madelineproto.xyz/API_docs/types/InputPeer.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     */
    public function block(bool|null $my_stories_from = null, array|int|string|null $id = null, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): bool;

    /**
     * Deletes a peer from a blocklist, see [here »](https://core.telegram.org/api/block) for more info.
     *
     * @param bool $my_stories_from Whether the peer should be removed from the story blocklist; if not set, the peer will be removed from the main blocklist, see [here »](https://core.telegram.org/api/block) for more info.
     * @param array|int|string $id Peer @see https://docs.madelineproto.xyz/API_docs/types/InputPeer.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     */
    public function unblock(bool|null $my_stories_from = null, array|int|string|null $id = null, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): bool;

    /**
     * Returns the list of blocked users.
     *
     * @param bool $my_stories_from Whether to fetch the story blocklist; if not set, will fetch the main blocklist. See [here »](https://core.telegram.org/api/block) for differences between the two.
     * @param int $offset The number of list elements to be skipped
     * @param int $limit The number of list elements to be returned
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array{_: 'contacts.blocked', blocked: list<array{_: 'peerBlocked', peer_id: array|int|string, date: int}>, chats: list<array|int|string>, users: list<array|int|string>}|array{_: 'contacts.blockedSlice', count: int, blocked: list<array{_: 'peerBlocked', peer_id: array|int|string, date: int}>, chats: list<array|int|string>, users: list<array|int|string>} @see https://docs.madelineproto.xyz/API_docs/types/contacts.Blocked.html
     */
    public function getBlocked(bool|null $my_stories_from = null, int|null $offset = 0, int|null $limit = 0, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Returns users found by username substring.
     *
     * @param string $q Target substring
     * @param int $limit Maximum number of users to be returned
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array{_: 'contacts.found', my_results: list<array|int|string>, results: list<array|int|string>, chats: list<array|int|string>, users: list<array|int|string>} @see https://docs.madelineproto.xyz/API_docs/types/contacts.Found.html
     */
    public function search(string|null $q = '', int|null $limit = 0, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Get most used peers.
     *
     * @param bool $correspondents Users we've chatted most frequently with
     * @param bool $bots_pm Most used bots
     * @param bool $bots_inline Most used inline bots
     * @param bool $phone_calls Most frequently called users
     * @param bool $forward_users Users to which the users often forwards messages to
     * @param bool $forward_chats Chats to which the users often forwards messages to
     * @param bool $groups Often-opened groups and supergroups
     * @param bool $channels Most frequently visited channels
     * @param bool $bots_app
     * @param int $offset Offset for [pagination](https://core.telegram.org/api/offsets)
     * @param int $limit Maximum number of results to return, [see pagination](https://core.telegram.org/api/offsets)
     * @param list<int|string>|array<never, never> $hash Array of [Hash for pagination, for more info click here](https://core.telegram.org/api/offsets#hash-generation) @see https://docs.madelineproto.xyz/API_docs/types/int|string.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array{_: 'contacts.topPeersNotModified'}|array{_: 'contacts.topPeers', categories: list<array{_: 'topPeerCategoryPeers', category: array{_: 'topPeerCategoryBotsPM'}|array{_: 'topPeerCategoryBotsInline'}|array{_: 'topPeerCategoryCorrespondents'}|array{_: 'topPeerCategoryGroups'}|array{_: 'topPeerCategoryChannels'}|array{_: 'topPeerCategoryPhoneCalls'}|array{_: 'topPeerCategoryForwardUsers'}|array{_: 'topPeerCategoryForwardChats'}|array{_: 'topPeerCategoryBotsApp'}, count: int, peers: list<array{_: 'topPeer', peer: array|int|string, rating: float}>}>, chats: list<array|int|string>, users: list<array|int|string>}|array{_: 'contacts.topPeersDisabled'} @see https://docs.madelineproto.xyz/API_docs/types/contacts.TopPeers.html
     */
    public function getTopPeers(bool|null $correspondents = null, bool|null $bots_pm = null, bool|null $bots_inline = null, bool|null $phone_calls = null, bool|null $forward_users = null, bool|null $forward_chats = null, bool|null $groups = null, bool|null $channels = null, bool|null $bots_app = null, int|null $offset = 0, int|null $limit = 0, array $hash = [], ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Reset [rating](https://core.telegram.org/api/top-rating) of top peer.
     *
     * @param array{_: 'topPeerCategoryBotsPM'}|array{_: 'topPeerCategoryBotsInline'}|array{_: 'topPeerCategoryCorrespondents'}|array{_: 'topPeerCategoryGroups'}|array{_: 'topPeerCategoryChannels'}|array{_: 'topPeerCategoryPhoneCalls'}|array{_: 'topPeerCategoryForwardUsers'}|array{_: 'topPeerCategoryForwardChats'}|array{_: 'topPeerCategoryBotsApp'} $category Top peer category @see https://docs.madelineproto.xyz/API_docs/types/TopPeerCategory.html
     * @param array|int|string $peer Peer whose rating should be reset @see https://docs.madelineproto.xyz/API_docs/types/InputPeer.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     */
    public function resetTopPeerRating(array $category, array|int|string|null $peer = null, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): bool;

    /**
     * Removes all contacts without an associated Telegram account.
     *
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     */
    public function resetSaved(?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): bool;

    /**
     * Get all contacts, requires a [takeout session, see here » for more info](https://core.telegram.org/api/takeout).
     *
     * @param int $takeoutId Takeout ID, generated using account.initTakeoutSession, see [the takeout docs](https://core.telegram.org/api/takeout) for more info.
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return list<array{_: 'savedPhoneContact', phone: string, first_name: string, last_name: string, date: int}> Array of  @see https://docs.madelineproto.xyz/API_docs/types/SavedContact.html
     */
    public function getSaved(int $takeoutId, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Enable/disable [top peers](https://core.telegram.org/api/top-rating).
     *
     * @param bool $enabled Enable/disable
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     */
    public function toggleTopPeers(bool $enabled, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): bool;

    /**
     * Add an existing telegram user as contact.
     *
     * Use [contacts.importContacts](https://docs.madelineproto.xyz/API_docs/methods/contacts.importContacts.html) to add contacts by phone number, without knowing their Telegram ID.
     *
     * @param bool $add_phone_privacy_exception Allow the other user to see our phone number?
     * @param array|int|string $id Telegram ID of the other user @see https://docs.madelineproto.xyz/API_docs/types/InputUser.html
     * @param string $first_name First name
     * @param string $last_name Last name
     * @param string $phone User's phone number, may be omitted to simply add the user to the contact list, without a phone number.
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array @see https://docs.madelineproto.xyz/API_docs/types/Updates.html
     */
    public function addContact(bool|null $add_phone_privacy_exception = null, array|int|string|null $id = null, string|null $first_name = '', string|null $last_name = '', string|null $phone = '', ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * If the [add contact action bar is active](https://core.telegram.org/api/action-bar#add-contact), add that user as contact.
     *
     * @param array|int|string $id The user to add as contact @see https://docs.madelineproto.xyz/API_docs/types/InputUser.html
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array @see https://docs.madelineproto.xyz/API_docs/types/Updates.html
     */
    public function acceptContact(array|int|string|null $id = null, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Get users and geochats near you, see [here »](https://core.telegram.org/api/nearby) for more info.
     *
     * @param bool $background While the geolocation of the current user is public, clients should update it in the background every half-an-hour or so, while setting this flag. <br>Do this only if the new location is more than 1 KM away from the previous one, or if the previous location is unknown.
     * @param array{_: 'inputGeoPointEmpty'}|array{_: 'inputGeoPoint', lat: float, long: float, accuracy_radius?: int} $geo_point Geolocation @see https://docs.madelineproto.xyz/API_docs/types/InputGeoPoint.html
     * @param int $self_expires If set, the geolocation of the current user will be public for the specified number of seconds; pass 0x7fffffff to disable expiry, 0 to make the current geolocation private; if the flag isn't set, no changes will be applied.
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array @see https://docs.madelineproto.xyz/API_docs/types/Updates.html
     */
    public function getLocated(bool|null $background = null, array|null $geo_point = null, int|null $self_expires = null, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Stop getting notifications about [discussion replies](https://core.telegram.org/api/discussion) of a certain user in `@replies`.
     *
     * @param bool $delete_message Whether to delete the specified message as well
     * @param bool $delete_history Whether to delete all `@replies` messages from this user as well
     * @param bool $report_spam Whether to also report this user for spam
     * @param int $msg_id ID of the message in the [@replies](https://core.telegram.org/api/discussion#replies) chat
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array @see https://docs.madelineproto.xyz/API_docs/types/Updates.html
     */
    public function blockFromReplies(bool|null $delete_message = null, bool|null $delete_history = null, bool|null $report_spam = null, int|null $msg_id = 0, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Resolve a phone number to get user info, if their privacy settings allow it.
     *
     * @param string $phone Phone number in international format, possibly obtained from a [phone number deep link](https://core.telegram.org/api/links#phone-number-links).
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array{_: 'contacts.resolvedPeer', peer: array|int|string, chats: list<array|int|string>, users: list<array|int|string>} @see https://docs.madelineproto.xyz/API_docs/types/contacts.ResolvedPeer.html
     */
    public function resolvePhone(string|null $phone = '', ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Generates a [temporary profile link](https://core.telegram.org/api/links#temporary-profile-links) for the currently logged-in user.
     *
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array{_: 'exportedContactToken', url: string, expires: int} @see https://docs.madelineproto.xyz/API_docs/types/ExportedContactToken.html
     */
    public function exportContactToken(?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;

    /**
     * Obtain user info from a [temporary profile link](https://core.telegram.org/api/links#temporary-profile-links).
     *
     * @param string $token The token extracted from the [temporary profile link](https://core.telegram.org/api/links#temporary-profile-links).
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array|int|string @see https://docs.madelineproto.xyz/API_docs/types/User.html
     */
    public function importContactToken(string|null $token = '', ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array|int|string;

    /**
     * Edit the [close friends list, see here »](https://core.telegram.org/api/privacy) for more info.
     *
     * @param list<int>|array<never, never> $id Full list of user IDs of close friends, see [here](https://core.telegram.org/api/privacy) for more info.
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     */
    public function editCloseFriends(array $id = [], ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): bool;

    /**
     * Replace the contents of an entire [blocklist, see here for more info »](https://core.telegram.org/api/block).
     *
     * @param bool $my_stories_from Whether to edit the story blocklist; if not set, will edit the main blocklist. See [here »](https://core.telegram.org/api/block) for differences between the two.
     * @param list<array|int|string>|array<never, never> $id Array of Full content of the blocklist. @see https://docs.madelineproto.xyz/API_docs/types/InputPeer.html
     * @param int $limit Maximum number of results to return, [see pagination](https://core.telegram.org/api/offsets)
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     */
    public function setBlocked(bool|null $my_stories_from = null, array $id = [], int|null $limit = 0, ?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): bool;

    /**
     *
     *
     * @param ?int $floodWaitLimit Can be used to specify a custom flood wait limit: if a FLOOD_WAIT_ rate limiting error is received with a waiting period bigger than this integer, an RPCErrorException will be thrown; otherwise, MadelineProto will simply wait for the specified amount of time. Defaults to the value specified in the settings: https://docs.madelineproto.xyz/PHP/danog/MadelineProto/Settings/RPC.html#setfloodtimeout-int-floodtimeout-self
     * @param ?string $queueId If specified, ensures strict server-side execution order of concurrent calls with the same queue ID.
     * @param ?\Amp\Cancellation $cancellation Cancellation
     * @return array{_: 'contacts.contactBirthdays', contacts: list<array{_: 'contactBirthday', birthday: array{_: 'birthday', day: int, month: int, year?: int}, contact_id: int}>, users: list<array|int|string>} @see https://docs.madelineproto.xyz/API_docs/types/contacts.ContactBirthdays.html
     */
    public function getBirthdays(?int $floodWaitLimit = null, ?string $queueId = null, ?\Amp\Cancellation $cancellation = null): array;
}
