<?php

namespace Vertilia\Text\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Vertilia\Text\Text
 * to generate MessagesFr catalog:
 * $ bin/po2php -n Vertilia\\Text\\Tests\\Locale -c MessagesFr tests/locale/fr/tests.po >tests/locale/MessagesFr.php
 * to generate MessagesRu catalog:
 * $ bin/po2php -n Vertilia\\Text\\Tests\\Locale -c MessagesRu tests/locale/ru/tests.po >tests/locale/MessagesRu.php
 *
 * using docker:
 * $docker run --rm --volume $PWD:/app php /app/bin/po2php ... /app/tests/locale/fr/tests.po ...
 *
 * N.B. update plural rule in locale/ru/tests.po to match php ternary operator precedence, different from c
 */
class TextTest extends TestCase
{
    const DOMAIN = 'tests';

    /**
     * Reset Text class
     */
    public function setUp(): void
    {
        $this->text_fr = new Locale\MessagesFr();
        $this->text_ru = new Locale\MessagesRu();
    }

    /**
     * @covers ::_
     */
    public function test_()
    {
        $this->assertEquals('Une pomme', $this->text_fr->_('An apple'), 'existing translation in Fr');
        $this->assertEquals('Яблоко', $this->text_ru->_('An apple'), 'existing translation in Ru');
        $unknown = 'non-existent ' . rand(100, 999);
        $this->assertEquals($unknown, $this->text_fr->_($unknown), 'non-existent translation');
    }

    /**
     * @covers ::pget
     */
    public function testPget()
    {
        $this->assertEquals(
            'Verte',
            $this->text_fr->pget('grass', 'Green'),
            'existing translation using context in Fr'
        );
        $this->assertEquals(
            'Зелёная',
            $this->text_ru->pget('grass', 'Green'),
            'existing translation using context in Ru'
        );
        $unknown = 'non-existent ' . rand(100, 999);
        $this->assertEquals(
            $unknown,
            $this->text_fr->pget('grass', $unknown),
            'non-existent translation using context'
        );
    }

    /**
     * @covers ::nget
     * @dataProvider ngetProvider
     */
    public function testNget($n, $expected)
    {
        $this->assertEquals($expected['fr'], sprintf($this->text_fr->nget('%u line', '%u lines', $n), $n));
        $this->assertEquals($expected['ru'], sprintf($this->text_ru->nget('%u line', '%u lines', $n), $n));
    }

    public function ngetProvider()
    {
        return [
            'zero lines' => [0, ['fr' => '0 ligne', 'ru' => '0 строк']],
            'one line' => [1, ['fr' => '1 ligne', 'ru' => '1 строка']],
            'two lines' => [2, ['fr' => '2 lignes', 'ru' => '2 строки']],
            'five lines' => [5, ['fr' => '5 lignes', 'ru' => '5 строк']],
        ];
    }

    /**
     * @covers ::npget
     * @dataProvider pgetProvider
     */
    public function testNpget($n, $expected)
    {
        $this->assertEquals($expected['fr'], sprintf($this->text_fr->npget('line', '%u sent', '%u sent', $n), $n));
        $this->assertEquals($expected['ru'], sprintf($this->text_ru->npget('line', '%u sent', '%u sent', $n), $n));
    }

    public function pgetProvider()
    {
        return [
            'zero lines' => [0, ['fr' => '0 envoyée', 'ru' => '0 отправлено']],
            'one line' => [1, ['fr' => '1 envoyée', 'ru' => '1 отправлена']],
            'two lines' => [2, ['fr' => '2 envoyées', 'ru' => '2 отправлены']],
            'five lines' => [5, ['fr' => '5 envoyées', 'ru' => '5 отправлено']],
        ];
    }
}
