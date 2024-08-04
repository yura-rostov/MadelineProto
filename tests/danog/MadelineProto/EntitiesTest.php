<?php

declare(strict_types=1);

namespace danog\MadelineProto\Test;

use danog\MadelineProto\EventHandler\Message\Entities\BankCard;
use danog\MadelineProto\EventHandler\Message\Entities\Blockquote;
use danog\MadelineProto\EventHandler\Message\Entities\Bold;
use danog\MadelineProto\EventHandler\Message\Entities\BotCommand;
use danog\MadelineProto\EventHandler\Message\Entities\Cashtag;
use danog\MadelineProto\EventHandler\Message\Entities\Code;
use danog\MadelineProto\EventHandler\Message\Entities\CustomEmoji;
use danog\MadelineProto\EventHandler\Message\Entities\Email;
use danog\MadelineProto\EventHandler\Message\Entities\Hashtag;
use danog\MadelineProto\EventHandler\Message\Entities\Italic;
use danog\MadelineProto\EventHandler\Message\Entities\Mention;
use danog\MadelineProto\EventHandler\Message\Entities\MentionName;
use danog\MadelineProto\EventHandler\Message\Entities\MessageEntity;
use danog\MadelineProto\EventHandler\Message\Entities\Phone;
use danog\MadelineProto\EventHandler\Message\Entities\Pre;
use danog\MadelineProto\EventHandler\Message\Entities\Spoiler;
use danog\MadelineProto\EventHandler\Message\Entities\Strike;
use danog\MadelineProto\EventHandler\Message\Entities\TextUrl;
use danog\MadelineProto\EventHandler\Message\Entities\Underline;
use danog\MadelineProto\EventHandler\Message\Entities\Url;
use danog\MadelineProto\StrTools;
use danog\MadelineProto\TextEntities;
use danog\MadelineProto\Tools;
use PHPUnit\Framework\TestCase;

/** @internal */
class EntitiesTest extends TestCase
{
    public function testMb(): void
    {
        $this->assertEquals(1, StrTools::mbStrlen('t'));
        $this->assertEquals(1, StrTools::mbStrlen('я'));
        $this->assertEquals(2, StrTools::mbStrlen('👍'));
        $this->assertEquals(4, StrTools::mbStrlen('🇺🇦'));

        $this->assertEquals('st', StrTools::mbSubstr('test', 2));
        $this->assertEquals('aя', StrTools::mbSubstr('aяaя', 2));
        $this->assertEquals('a👍', StrTools::mbSubstr('a👍a👍', 3));
        $this->assertEquals('🇺🇦', StrTools::mbSubstr('🇺🇦🇺🇦', 4));

        $this->assertEquals(['te', 'st'], StrTools::mbStrSplit('test', 2));
        $this->assertEquals(['aя', 'aя'], StrTools::mbStrSplit('aяaя', 2));
        $this->assertEquals(['a👍', 'a👍'], StrTools::mbStrSplit('a👍a👍', 3));
        $this->assertEquals(['🇺🇦', '🇺🇦'], StrTools::mbStrSplit('🇺🇦🇺🇦', 4));
    }
    /**
     * @dataProvider provideEntityObjects
     */
    public function testAllEntities(MessageEntity $message): void
    {
        $this->assertTrue(MessageEntity::fromRawEntity($message->toBotAPI()) == $message);
        $this->assertTrue(MessageEntity::fromRawEntity($message->toMTProto()) == $message);
    }
    public static function provideEntityObjects(): array
    {
        return [
            [new BankCard(1, 2)],
            [new Blockquote(1, 2)],
            [new Bold(1, 2)],
            [new BotCommand(1, 2)],
            [new Cashtag(1, 2)],
            [new Code(1, 2)],
            [new CustomEmoji(1, 2, 12345)],
            [new Email(1, 2)],
            [new Hashtag(1, 2)],
            [new Italic(1, 2)],
            [new MentionName(1, 2, 12345)],
            [new Mention(1, 2)],
            [new Phone(1, 2)],
            [new Pre(1, 2, "language")],
            [new Spoiler(1, 2)],
            [new Strike(1, 2)],
            [new TextUrl(1, 2, "https://google.com")],
            [new Underline(1, 2)],
            [new Url(1, 2)],
        ];
    }
    private static function sendMessage(string $message, string $parse_mode): TextEntities
    {
        return match ($parse_mode) {
            'html' => TextEntities::fromHtml($message),
            'markdown' => TextEntities::fromMarkdown($message),
        };
    }
    private static function MTProtoToBotAPI(TextEntities $entities): array
    {
        $result = ['text' => $entities->message];
        $entities = $entities->entities;
        foreach ($entities as &$entity) {
            $entity = $entity->toBotAPI();
        }
        $result['entities'] = $entities;
        return $result;
    }
    /**
     * @dataProvider provideEntities
     */
    public function testEntities(string $mode, string $html, string $bare, array $entities, ?string $htmlReverse = null): void
    {
        $resultMTProto = self::sendMessage(message: $html, parse_mode: $mode);
        $result = self::MTProtoToBotAPI($resultMTProto);
        $this->assertEquals($bare, $result['text']);
        $this->assertEquals($entities, $result['entities']);
        if (strtolower($mode) === 'html') {
            $this->assertEquals(
                str_replace(['<br/>', ' </b>', 'mention:'], ['<br>', '</b> ', 'tg://user?id='], $htmlReverse ?? $html),
                StrTools::entitiesToHtml(
                    $resultMTProto->message,
                    $resultMTProto->entities,
                    true
                ),
            );
            $resultMTProto = self::sendMessage(message: StrTools::htmlEscape($html), parse_mode: $mode);
            $result = self::MTProtoToBotAPI($resultMTProto);
            $this->assertEquals($html, $result['text']);
            $this->assertNoRelevantEntities($result['entities']);
        } else {
            $resultMTProto = self::sendMessage(message: Tools::markdownEscape($html), parse_mode: $mode);
            $result = self::MTProtoToBotAPI($resultMTProto);
            $this->assertEquals($html, $result['text']);
            $this->assertNoRelevantEntities($result['entities']);

            $resultMTProto = self::sendMessage(message: "```\n".Tools::markdownCodeblockEscape($html)."\n```", parse_mode: $mode);
            $result = self::MTProtoToBotAPI($resultMTProto);
            $this->assertEquals($html, rtrim($result['text']));
            $this->assertEquals([['offset' => 0, 'length' => StrTools::mbStrlen($html), 'type' => 'pre']], $result['entities']);
        }
    }

    private function assertNoRelevantEntities(array $entities): void
    {
        $entities = array_filter($entities, static fn (array $e) => !\in_array(
            $e['type'],
            ['url', 'email', 'phone_number', 'mention', 'bot_command'],
            true
        ));
        $this->assertEmpty($entities);
    }
    public function provideEntities(): array
    {
        $this->setUpBeforeClass();
        return [
            [
                'html',
                '<b>test</b>',
                'test',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'bold',
                    ],
                ],
            ],
            [
                'html',
                '<b>test</b><br>test',
                "test\ntest",
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'bold',
                    ],
                ],
            ],
            [
                'html',
                '<b>test</b><br/>test',
                "test\ntest",
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'bold',
                    ],
                ],
            ],
            [
                'html',
                '🇺🇦<b>🇺🇦</b>',
                '🇺🇦🇺🇦',
                [
                    [
                        'offset' => 4,
                        'length' => 4,
                        'type' => 'bold',
                    ],
                ],
            ],
            [
                'html',
                'test<b>test </b>',
                'testtest',
                [
                    [
                        'offset' => 4,
                        'length' => 4,
                        'type' => 'bold',
                    ],
                ],
                'test<b>test</b>',
            ],
            [
                'html',
                'è»test<b>test </b>test',
                'è»testtest test',
                [
                    [
                        'offset' => 6,
                        'length' => 4,
                        'type' => 'bold',
                    ],
                ],
            ],
            [
                'html',
                'test<b> test</b>',
                'test test',
                [
                    [
                        'offset' => 4,
                        'length' => 5,
                        'type' => 'bold',
                    ],
                ],
            ],
            [
                'markdown',
                'test* test*',
                'test test',
                [
                    [
                        'offset' => 4,
                        'length' => 5,
                        'type' => 'bold',
                    ],
                ],
            ],
            [
                'html',
                '<b>test</b><br><i>test</i> <code>test</code> <pre language="html">test</pre> <a href="https://example.com/">test</a> <s>strikethrough</s> <u>underline</u> <blockquote>blockquote</blockquote> https://google.com daniil@daniil.it +39398172758722 @daniilgentili <tg-spoiler>spoiler</tg-spoiler> &lt;b&gt;not_bold&lt;/b&gt;',
                "test\ntest test test test strikethrough underline blockquote https://google.com daniil@daniil.it +39398172758722 @daniilgentili spoiler <b>not_bold</b>",
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'bold',
                    ],
                    [
                        'offset' => 5,
                        'length' => 4,
                        'type' => 'italic',
                    ],
                    [
                        'offset' => 10,
                        'length' => 4,
                        'type' => 'code',
                    ],
                    [
                        'offset' => 15,
                        'length' => 4,
                        'language' => 'html',
                        'type' => 'pre',
                    ],
                    [
                        'offset' => 20,
                        'length' => 4,
                        'url' => 'https://example.com/',
                        'type' => 'text_link',
                    ],
                    [
                        'offset' => 25,
                        'length' => 13,
                        'type' => 'strikethrough',
                    ],
                    [
                        'offset' => 39,
                        'length' => 9,
                        'type' => 'underline',
                    ],
                    [
                        'offset' => 49,
                        'length' => 10,
                        'type' => 'block_quote',
                    ],
                    [
                        'offset' => 127,
                        'length' => 7,
                        'type' => 'spoiler',
                    ],
                ],
                '<b>test</b><br><i>test</i> <code>test</code> <pre language="html">test</pre> <a href="https://example.com/">test</a> <s>strikethrough</s> <u>underline</u> <blockquote>blockquote</blockquote> https://google.com daniil@daniil.it +39398172758722 @daniilgentili <tg-spoiler>spoiler</tg-spoiler> &lt;b&gt;not_bold&lt;/b&gt;',
            ],
            [
                'markdown',
                'test *bold _bold and italic_ bold*',
                'test bold bold and italic bold',
                [
                    [
                        'offset' => 10,
                        'length' => 15,
                        'type' => 'italic',
                    ],
                    [
                        'offset' => 5,
                        'length' => 25,
                        'type' => 'bold',
                    ],
                ],
            ],
            [
                'markdown',
                "a\nb\nc",
                "a\nb\nc",
                [],
            ],
            [
                'markdown',
                "a\n\nb\n\nc",
                "a\n\nb\n\nc",
                [],
            ],
            [
                'markdown',
                "a\n\n\nb\n\n\nc",
                "a\n\n\nb\n\n\nc",
                [],
            ],
            [
                'markdown',
                "a\n```php\n<?php\necho 'yay';\n```",
                "a\n<?php\necho 'yay';",
                [
                    [
                        'offset' => 2,
                        'length' => 17,
                        'type' => 'pre',
                        'language' => 'php',
                    ],
                ],
            ],
            [
                'html',
                '<b>\'"</b>',
                '\'"',
                [
                    [
                        'offset' => 0,
                        'length' => 2,
                        'type' => 'bold',
                    ],
                ],
                '<b>&apos;&quot;</b>',
            ],
            [
                'html',
                '<a href="mention:101374607">mention1</a> <a href="tg://user?id=101374607">mention2</a>',
                'mention1 mention2',
                [
                    [
                        'offset' => 0,
                        'length' => 8,
                        'type' => 'text_mention',
                        'user' => ['id' => 101374607],
                    ],
                    [
                        'offset' => 9,
                        'length' => 8,
                        'type' => 'text_mention',
                        'user' => ['id' => 101374607],
                    ],
                ],
            ],
            [
                'markdown',
                '_a b c <b> & " \' \_ \* \~ \\__',
                'a b c <b> & " \' _ * ~ _',
                [
                    [
                        'offset' => 0,
                        'length' => 23,
                        'type' => 'italic',
                    ],
                ],
            ],
            [
                'markdown',
                StrTools::markdownEscape('\\ test testovich _*~'),
                '\\ test testovich _*~',
                [],
            ],
            [
                'markdown',
                "```\na_b\n".StrTools::markdownCodeblockEscape('\\ ```').'```',
                "a_b\n\\ ```",
                [
                    [
                        'offset' => 0,
                        'length' => 9,
                        'type' => 'pre',
                    ],
                ],
            ],
            [
                'markdown',
                '`a_b '.StrTools::markdownCodeEscape('`').'`',
                'a_b `',
                [
                    [
                        'offset' => 0,
                        'length' => 5,
                        'type' => 'code',
                    ],
                ],
            ],
            [
                'markdown',
                '[link ](https://google.com/)test',
                'link test',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'text_link',
                        'url' => 'https://google.com/',
                    ],
                ],
            ],
            [
                'markdown',
                '[link]('.StrTools::markdownUrlEscape('https://transfer.sh/(/test/test.PNG,/test/test.MP4).zip').')',
                'link',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'text_link',
                        'url' => 'https://transfer.sh/(/test/test.PNG,/test/test.MP4).zip',
                    ],
                ],
            ],
            [
                'markdown',
                '[link]('.StrTools::markdownUrlEscape('https://google.com/').')',
                'link',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'text_link',
                        'url' => 'https://google.com/',
                    ],
                ],
            ],
            [
                'markdown',
                '[link]('.StrTools::markdownUrlEscape('https://google.com/?v=\\test').')',
                'link',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'text_link',
                        'url' => 'https://google.com/?v=\\test',
                    ],
                ],
            ],
            [
                'markdown',
                '[link ](https://google.com/)',
                'link',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'text_link',
                        'url' => 'https://google.com/',
                    ],
                ],
            ],
            [
                'markdown',
                '![link ](https://google.com/)',
                'link',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'text_link',
                        'url' => 'https://google.com/',
                    ],
                ],
            ],
            [
                'markdown',
                '[not a link]',
                '[not a link]',
                [],
            ],
            [
                'html',
                '<a href="https://google.com/">link </a>test',
                'link test',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'text_link',
                        'url' => 'https://google.com/',
                    ],
                ],
                '<a href="https://google.com/">link</a> test',
            ],
            [
                'html',
                '<a href="https://google.com/">link </a>',
                'link',
                [
                    [
                        'offset' => 0,
                        'length' => 4,
                        'type' => 'text_link',
                        'url' => 'https://google.com/',
                    ],
                ],
                '<a href="https://google.com/">link</a>',
            ],
            [
                'markdown',
                'test _italic_ *bold* __underlined__ ~strikethrough~ ```test pre``` `code` ||spoiler||',
                'test italic bold underlined strikethrough  pre code spoiler',
                [
                    [
                        'offset' => 5,
                        'length' => 6,
                        'type' => 'italic',
                    ],
                    [
                        'offset' => 12,
                        'length' => 4,
                        'type' => 'bold',
                    ],
                    [
                        'offset' => 17,
                        'length' => 10,
                        'type' => 'underline',
                    ],
                    [
                        'offset' => 28,
                        'length' => 13,
                        'type' => 'strikethrough',
                    ],
                    [
                        'offset' => 42,
                        'length' => 4,
                        'type' => 'pre',
                        'language' => 'test',
                    ],
                    [
                        'offset' => 47,
                        'length' => 4,
                        'type' => 'code',
                    ],
                    [
                        'offset' => 52,
                        'length' => 7,
                        'type' => 'spoiler',
                    ],
                ],
            ],
        ];
    }
}
