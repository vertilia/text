<?php

namespace Vertilia\Text\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Vertilia\Text\Text
 * to generate MessagesEn catalog:
 * $ bin/po2php -n Vertilia\\Text\\Tests\\Locale -c MessagesEn tests/locale/en/messages.po >tests/locale/MessagesEn.php
 * to generate MessagesFr catalog:
 * $ bin/po2php -n Vertilia\\Text\\Tests\\Locale -c MessagesFr tests/locale/fr/messages.po >tests/locale/MessagesFr.php
 * to generate MessagesRu catalog:
 * $ bin/po2php -n Vertilia\\Text\\Tests\\Locale -c MessagesRu tests/locale/ru/messages.po >tests/locale/MessagesRu.php
 *
 * using docker:
 * $docker run --rm -v "$PWD":/app php /app/bin/po2php -n ... -c ... /app/tests/locale/fr/messages.po >...
 *
 * N.B. updated plural rule in locale/ru/tests.po to match php (pre-v8) ternary operator precedence, different from C
 */
class TextTest extends TestCase
{
    const DOMAIN = 'tests';

    /**
     * @covers ::_
     * @covers ::pget
     * @dataProvider _PgetProvider
     */
    public function test_Pget($expected, $actual, $comment)
    {
        $this->assertEquals($expected, $actual, $comment);
    }

    public function _PgetProvider()
    {
        $xx = new \Vertilia\Text\Text();
        $text_en = new Locale\MessagesEn();
        $text_fr = new Locale\MessagesFr();
        $text_ru = new Locale\MessagesRu();

        $msg1 = $xx->_('Quotes: \'apostrophes\', "quotes"; var parsing: $i, ${i}, {$i}; printf formats: %s, %2$s');
        $msg2 = $xx->_("Quotes: 'apostrophes', \"quotes\"; var parsing: \$i, \${i}, {\$i}; printf formats: %s, %2\$s");

        $unknown = 'non-existent ' . rand(100, 999);

        return [
            ['An apple', $text_en->_('An apple'), 'existing translation in En'],
            ['Une pomme', $text_fr->_('An apple'), 'existing translation in Fr'],
            ['Яблоко', $text_ru->_('An apple'), 'existing translation in Ru'],
            [
                'Quotes: \'apostrophes\', "quotes"; var parsing: $i, ${i}, {$i}; printf formats: %s, %2$s',
                $text_en->_($msg1),
                'special chars in En'
            ],
            [
                'Guillemets: \'apostrophes\', "quotes"; interprétation de vars: $i, ${i}, {$i}; formats printf: %s, %2$s',
                $text_fr->_($msg1),
                'special chars in En'
            ],
            [
                'Кавычки: \'апострофы\', "кавычки"; обработка переменных: $i, ${i}, {$i}; форматы printf: %s, %2$s',
                $text_ru->_($msg2),
                'special chars in Ru'
            ],

            [
                "This is a\nmultiline\nstring",
                $text_en->_("This is a\nmultiline\nstring"),
                'existing multiline translation in En'
            ],
            [
                "Ceci est\nune chaîne\nmultiligne",
                $text_fr->_("This is a\nmultiline\nstring"),
                'existing multiline translation in Fr'
            ],
            [
                "Вот это\nмногострочный\nтекст",
                $text_ru->_("This is a\nmultiline\nstring"),
                'existing multiline translation in Ru'
            ],

            [$unknown, $text_en->_($unknown), 'non-existent translation'],

            ['Green', $text_en->pget('grass', 'Green'), 'existing translation using context in En'],
            ['Verte', $text_fr->pget('grass', 'Green'), 'existing translation using context in Fr'],
            ['Зелёная', $text_ru->pget('grass', 'Green'), 'existing translation using context in Ru'],

            [$unknown, $text_fr->pget('grass', $unknown), 'non-existent translation using context'],
        ];
    }

    /**
     * @covers ::nget
     * @dataProvider ngetProvider
     */
    public function testNget($expected, $n)
    {
        $text_en = new Locale\MessagesEn();
        $text_fr = new Locale\MessagesFr();
        $text_ru = new Locale\MessagesRu();

        $unknown = 'non-existent ' . rand(100, 999);

        $this->assertEquals(
            $expected['en'],
            sprintf($text_en->nget('%u line', '%u lines', $n), $n),
            'existing plural form in En'
        );
        $this->assertEquals(
            $expected['fr'],
            sprintf($text_fr->nget('%u line', '%u lines', $n), $n),
            'existing plural form in Fr'
        );
        $this->assertEquals(
            $expected['ru'],
            sprintf($text_ru->nget('%u line', '%u lines', $n), $n),
            'existing plural form in Ru'
        );

        $this->assertEquals($unknown, $text_ru->nget($unknown, $unknown, $n), 'non-existent plural form');
    }

    public function ngetProvider()
    {
        return [
            'zero lines' => [['en' => '0 lines', 'fr' => '0 ligne', 'ru' => '0 строк'], 0],
            'one line' => [['en' => '1 line', 'fr' => '1 ligne', 'ru' => '1 строка'], 1],
            'two lines' => [['en' => '2 lines', 'fr' => '2 lignes', 'ru' => '2 строки'], 2],
            'five lines' => [['en' => '5 lines', 'fr' => '5 lignes', 'ru' => '5 строк'], 5],
        ];
    }

    /**
     * @covers ::npget
     * @dataProvider npgetProvider
     */
    public function testNpget($expected, $n)
    {
        $text_en = new Locale\MessagesEn();
        $text_fr = new Locale\MessagesFr();
        $text_ru = new Locale\MessagesRu();

        $unknown = 'non-existent ' . rand(100, 999);

        $this->assertEquals(
            $expected['en'],
            sprintf($text_en->npget('line', '%u sent', '%u sent', $n), $n),
            'existing plural form using context in En'
        );
        $this->assertEquals(
            $expected['fr'],
            sprintf($text_fr->npget('line', '%u sent', '%u sent', $n), $n),
            'existing plural form using context in Fr'
        );
        $this->assertEquals(
            $expected['ru'],
            sprintf($text_ru->npget('line', '%u sent', '%u sent', $n), $n),
            'existing plural form using context in Ru'
        );

        $this->assertEquals($unknown, $text_en->npget('line', $unknown, $unknown, $n), 'non-existent plural form');
    }

    public function npgetProvider()
    {
        return [
            'zero sent (line)' => [['en' => '0 sent', 'fr' => '0 envoyée', 'ru' => '0 отправлено'], 0],
            'one sent (line)' => [['en' => '1 sent', 'fr' => '1 envoyée', 'ru' => '1 отправлена'], 1],
            'two sent (line)' => [['en' => '2 sent', 'fr' => '2 envoyées', 'ru' => '2 отправлены'], 2],
            'five sent (line)' => [['en' => '5 sent', 'fr' => '5 envoyées', 'ru' => '5 отправлено'], 5],
        ];
    }
}
