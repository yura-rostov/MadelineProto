<?php

declare(strict_types=1);

namespace danog\MadelineProto\TL\Conversion;

use danog\MadelineProto\Exception;
use danog\MadelineProto\StrTools;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Throwable;

final class DOMEntities
{
    /**
     * @readonly-allow-private-mutation
     */
    public array $entities = [];
    /**
     * @readonly-allow-private-mutation
     */
    public string $message = '';
    public function __construct(string $html)
    {
        try {
            $dom = new DOMDocument();
            $html = \preg_replace('/\<br(\s*)?\/?\>/i', "\n", $html);
            $dom->loadxml('<body>' . \trim($html) . '</body>');
            $this->parseNode($dom->getElementsByTagName('body')->item(0), 0);
        } catch (Throwable $e) {
            throw new Exception("An error occurred while parsing $html: {$e->getMessage()}", $e->getCode());
        }
    }
    /**
     * @return integer Length of the node
     */
    private function parseNode(DOMNode|DOMText $node, int $offset): int
    {
        if ($node instanceof DOMText) {
            $this->message .= $node->wholeText;
            return StrTools::mbStrlen($node->wholeText);
        }
        if ($node->nodeName === 'br') {
            $this->message .= "\n";
            return 1;
        }
        /** @var DOMElement $node */
        $entity = match ($node->nodeName) {
            's', 'strike', 'del' =>['_' => 'messageEntityStrike'],
            'u' =>  ['_' => 'messageEntityUnderline'],
            'blockquote' => ['_' => 'messageEntityBlockquote'],
            'b', 'strong' => ['_' => 'messageEntityBold'],
            'i', 'em' => ['_' => 'messageEntityItalic'],
            'code' => ['_' => 'messageEntityCode'],
            'spoiler', 'tg-spoiler' => ['_' => 'messageEntitySpoiler'],
            'pre' => ['_' => 'messageEntityPre', 'language' => $node->getAttribute('language') ?? ''],
            'a' => $this->handleA($node),
            default => null,
        };
        $length = 0;
        foreach ($node->childNodes as $sub) {
            $length += $this->parseNode($sub, $offset+$length);
        }
        if ($entity !== null) {
            $lengthReal = $length;
            for ($x = \strlen($this->message)-1; $x >= 0; $x--) {
                if (!(
                    $this->message[$x] === ' '
                    || $this->message[$x] === "\r"
                    || $this->message[$x] === "\n"
                )) {
                    break;
                }
                $lengthReal--;
            }
            if ($lengthReal > 0) {
                $entity['offset'] = $offset;
                $entity['length'] = $lengthReal;
                $this->entities []= $entity;
            }
        }
        return $length;
    }

    private function handleA(DOMElement $node): array
    {
        $href = $node->getAttribute('href');
        if (\preg_match('|^mention:(.+)|', $href, $matches) || \preg_match('|^tg://user\\?id=(.+)|', $href, $matches)) {
            return ['_' => 'inputMessageEntityMentionName', 'user_id' => $matches[1]];
        }
        if (\preg_match('|^emoji:(\d+)$|', $href, $matches)) {
            return ['_' => 'messageEntityCustomEmoji', 'document_id' => (int) $matches[1]];
        }
        return ['_' => 'messageEntityTextUrl', 'url' => $href];
    }
}
