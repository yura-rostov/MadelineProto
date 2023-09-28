<?php declare(strict_types=1);

/**
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

namespace danog\MadelineProto\EventHandler;

use Amp\ByteStream\ReadableStream;
use danog\MadelineProto\Ipc\IpcCapable;
use danog\MadelineProto\MTProto;
use JsonSerializable;

/**
 * Represents a generic media.
 */
abstract class Media extends IpcCapable implements JsonSerializable
{
    /** Media filesize */
    public readonly int $size;

    /** Media file name */
    public readonly string $fileName;

    /** Media file extension */
    public readonly string $fileExt;

    /** Media creation date */
    public readonly int $creationDate;

    /** Media MIME type */
    public readonly string $mimeType;

    /** Time-to-live of media */
    public readonly ?int $ttl;

    /** @var list<array> Thumbnails */
    public readonly array $thumbs;

    /** @var list<array> Video thumbnails */
    public readonly array $videoThumbs;

    /** Whether the media should be hidden behind a spoiler */
    public readonly bool $spoiler;

    /** File ID in bot API format (always present even for users) */
    public readonly string $botApiFileId;

    /** Unique file ID in bot API format (always present even for users) */
    public readonly string $botApiFileUniqueId;

    /** @internal Media location */
    public readonly array $location;

    /** @internal */
    public function __construct(
        MTProto $API,
        array $rawMedia,

        /** Whether this media is protected */
        public readonly bool $protected
    ) {
        parent::__construct($API);
        [
            'name' => $name,
            'ext' => $this->fileExt,
            'mime' => $this->mimeType,
            'size' => $this->size,
            'InputFileLocation' => $this->location
        ] = $API->getDownloadInfo($rawMedia);
        $this->fileName = $name.$this->fileExt;

        [
            'file_id' => $this->botApiFileId,
            'file_unique_id' => $this->botApiFileUniqueId
        ] = $API->extractBotAPIFile($API->MTProtoToBotAPI($rawMedia));

        $this->creationDate = ($rawMedia['document'] ?? $rawMedia['photo'])['date'];
        $this->ttl = $rawMedia['ttl_seconds'] ?? null;
        $this->spoiler = $rawMedia['spoiler'] ?? false;
    }

    /**
     * Gets a download link for any file up to 4GB.
     *
     * @param string|null $scriptUrl Optional path to custom download script (not needed when running via web)
     */
    public function getDownloadLink(?string $scriptUrl = null): string
    {
        return $this->getClient()->getDownloadLink($this, $scriptUrl);
    }

    /** @internal */
    public function jsonSerialize(): mixed
    {
        $v = \get_object_vars($this);
        unset($v['API'], $v['session'], $v['location']);
        $v['_'] = static::class;
        return $v;
    }

    /**
     * Get a readable amp stream with the file contents.
     *
     * @param (callable(float, float, float): void)|null $cb Progress callback
     */
    public function getStream(?callable $cb = null, int $offset = 0, int $end = -1): ReadableStream
    {
        return $this->getClient()->downloadToReturnedStream($this, $cb, $offset, $end);
    }

    /**
     * Download the media to working directory or passed path.
     */
    public function downloadToDir(?string $path = null): string
    {
        $path ??= \getcwd();
        return $this->getClient()->downloadToDir($this, $path);
    }
    /**
     * Download the media to file.
     */
    public function downloadToFile(string $path): string
    {
        return $this->getClient()->downloadToFile($this, $path);
    }
}
