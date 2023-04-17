<?php

declare(strict_types=1);

/**
 * UDP stream wrapper.
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

namespace danog\MadelineProto\Stream\Common;

use Amp\ByteStream\ClosedException;
use danog\MadelineProto\Exception;
use danog\MadelineProto\NothingInTheSocketException;
use danog\MadelineProto\Stream\BufferedStreamInterface;
use danog\MadelineProto\Stream\ConnectionContext;
use danog\MadelineProto\Stream\MTProtoBufferInterface;
use danog\MadelineProto\Stream\RawStreamInterface;
use danog\MadelineProto\Stream\ReadBufferInterface;
use danog\MadelineProto\Stream\Transport\DefaultStream;
use danog\MadelineProto\Stream\WriteBufferInterface;

/**
 * UDP stream wrapper.
 *
 * @author Daniil Gentili <daniil@daniil.it>
 */
final class UdpBufferedStream extends DefaultStream implements BufferedStreamInterface, MTProtoBufferInterface
{
    /**
     * Connect to stream.
     *
     * @param ConnectionContext $ctx The connection context
     */
    public function connect(ConnectionContext $ctx, string $header = ''): void
    {
        $this->stream = $ctx->getStream($header);
    }
    /**
     * Async close.
     */
    public function disconnect(): void
    {
        $this->stream->disconnect();
    }
    /**
     * Get read buffer asynchronously.
     *
     * @param int $length Length of payload, as detected by this layer
     */
    public function getReadBuffer(?int &$length): \danog\MadelineProto\Stream\ReadBufferInterface
    {
        if (!$this->stream) {
            throw new ClosedException('MadelineProto stream was disconnected');
        }
        $chunk = $this->read();
        if ($chunk === null) {
            $this->disconnect();
            throw new NothingInTheSocketException();
        }
        $length = \strlen($chunk);
        return new class($chunk) implements ReadBufferInterface {
            /**
             * Buffer.
             *
             * @var resource
             */
            private $buffer;
            /**
             * Constructor function.
             *
             * @param string $buf Buffer
             */
            public function __construct(string $buf)
            {
                $this->buffer = \fopen('php://memory', 'r+');
                \fwrite($this->buffer, $buf);
                \fseek($this->buffer, 0);
            }
            /**
             * Read data from buffer.
             *
             * @param integer $length Length
             */
            public function bufferRead(int $length): string
            {
                return \fread($this->buffer, $length);
            }
            /**
             * Destructor function.
             */
            public function __destruct()
            {
                \fclose($this->buffer);
            }
        };
    }
    /**
     * Get write buffer asynchronously.
     *
     * @param int $length Total length of data that is going to be piped in the buffer
     */
    public function getWriteBuffer(int $length, string $append = ''): \danog\MadelineProto\Stream\WriteBufferInterface
    {
        return new class($length, $append, $this) implements WriteBufferInterface {
            private int $length;
            private string $append;
            private int $append_after;
            private RawStreamInterface $stream;
            private string $data = '';
            /**
             * Constructor function.
             */
            public function __construct(int $length, string $append, RawStreamInterface $rawStreamInterface)
            {
                $this->stream = $rawStreamInterface;
                $this->length = $length;
                if (\strlen($append)) {
                    $this->append = $append;
                    $this->append_after = $length - \strlen($append);
                }
            }
            /**
             * Async write.
             *
             * @param string $data Data to write
             */
            public function bufferWrite(string $data): void
            {
                $this->data .= $data;
                if ($this->append_after) {
                    $this->append_after -= \strlen($data);
                    if ($this->append_after === 0) {
                        $this->data .= $this->append;
                        $this->append = '';
                        $this->stream->write($this->data);
                    } elseif ($this->append_after < 0) {
                        $this->append_after = 0;
                        $this->append = '';
                        throw new Exception('Tried to send too much out of frame data, cannot append');
                    }
                }
            }
        };
    }
    public static function getName(): string
    {
        return self::class;
    }
}
