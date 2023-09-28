<?php declare(strict_types=1);

// Simple example bot.
// PHP 8.1.15+ or 8.2.4+ is required.

// Run via CLI (recommended: `screen php bot.php`) or via web.

// To reduce RAM usage, follow these instructions: https://docs.madelineproto.xyz/docs/DATABASE.html

use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\SimpleEventHandler;

// Load via composer (RECOMMENDED, see https://docs.madelineproto.xyz/docs/INSTALLATION.html#composer-from-scratch)
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    // Otherwise download an !!! alpha !!! version of MadelineProto via madeline.php
    if (!file_exists('madeline.php')) {
        copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
    }
    require_once 'madeline.php';
}

class MyEventHandler extends SimpleEventHandler
{
    // !!! Change this to your username !!!
    const ADMIN = "@me";

    /**
     * Get peer(s) where to report errors.
     */
    public function getReportPeers()
    {
        return [self::ADMIN];
    }

    /**
     * Handle incoming updates from users, chats and channels.
     */
    #[Handler]
    public function handleMessage(Incoming&Message $message): void
    {
        // Code that uses $message...
        // See the following pages for more examples and documentation:
        // - https://github.com/danog/MadelineProto/blob/v8/examples/bot.php
        // - https://docs.madelineproto.xyz/docs/UPDATES.html
        // - https://docs.madelineproto.xyz/docs/FILTERS.html
        // - https://docs.madelineproto.xyz/
    }
}

MyEventHandler::startAndLoop('bot.madeline');
