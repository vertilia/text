<?php

namespace Vertilia\Text\Tests;

use PHPUnit\Framework\TestCase;
use Vertilia\Text\PoDomain;

/**
 * @coversDefaultClass PoDomain
 */
class PoDomainTest extends TestCase
{
    /**
     * @covers PoDomain::addMsg
     * @dataProvider providerAddMsg
     */
    public function testAddMsg($pattern, $comment_tag, $msg)
    {
        $po = (new PoDomain($comment_tag))->addMsg($msg);
print_r("$po");
        $this->assertMatchesRegularExpression($pattern, $po);
    }

    public static function providerAddMsg(): array
    {
        return [
            'empty POT with header' =>
                ['/POT-Creation-Date/', '', []],
            'POT with header' =>
                ['/POT-Creation-Date/', '', ['msgid' => 'An apple']],
            'POT with msgid' =>
                ['/msgid "An apple"/', '', ['msgid' => 'An apple']],
            'POT with msgid and msgstr' =>
                ['/msgid "An apple"\s+msgstr "Une pomme"/', '', ['msgid' => 'An apple', 'msgstr' => 'Une pomme']],
            'POT with msgid with quotes and dollar' =>
                ['/msgid "An \\\\"\\$apple\\\\""/', '', ['msgid' => 'An "$apple"']],
            'POT with msgid with specials' =>
                ['/msgid "An apple\\\\a\\\\b\\\\f\\\\r\\\\t\\\\v"/', '', ['msgid' => "An apple\a\b\f\r\t\v"]],
            'POT with msgid with newline' =>
                ['/msgid ""\s+"An apple\\\\n"/', '', ['msgid' => "An apple\n"]],
            'POT with plural and flag' =>
                [
                    '/#, php-format\s+msgid "%u apple"\s+msgid_plural "%u apples"/',
                    '',
                    ['msgid' => '%u apple', 'msgid_plural' => '%u apples']
                ],
            'POT with plural translations' =>
                [
                    '/msgid "An apple"\s+msgid_plural "Apples"\s+msgstr\[0] "Une pomme"\s+msgstr\[1] "Pommes"/',
                    '',
                    ['msgid' => 'An apple', 'msgid_plural' => 'Apples', 'msgstr' => ['Une pomme', 'Pommes']]
                ],
            'POT with context' =>
                ['/msgctxt "An apple"\s+msgid "green"/', '', ['msgctxt' => 'An apple', 'msgid' => 'green']],
            'POT with code reference' =>
                ['/#: file:42\s+msgid "An apple"/', '', ['msgid' => 'An apple', '#:' => 'file:42']],
            'POT with comments' =>
                [
                    '/#\. TRAD: comment1\s+#\. comment2\s+#\. comment3\s+msgid "An apple"/',
                    '',
                    ['msgid' => 'An apple', '#.' => ['/** TRAD: comment1 */', '// comment2', '# comment3']]
                ],
            'POT with comment tag' =>
                [
                    '/#\. TRAD: comment1\s+msgid "An apple"/',
                    'TRAD:',
                    ['msgid' => 'An apple', '#.' => ['/*** TRAD: comment1 */', '/// comment2', '### comment3']]
                ],
        ];
    }
}
