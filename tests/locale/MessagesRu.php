<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesRu extends \Vertilia\Text\Text
{
    protected array $translations =
array (
  'd0f0e2a5' => 'Кавычки: \'апострофы\', "кавычки"; обработка переменных: $i, ${i}, {$i}; форматы printf: %s, %2$s',
  'f67ebb98' => 'Яблоко',
  'ea7a212a' => 'Зелёное яблоко',
  '4ad68324' => 'Вот это
многострочный
текст',
  '0aabfa28' => 'Зелёная',
  'ccf6a32c' => '%u строка',
  '3e9d202f' => '%u строки',
  '2dcdd3db' => '%u строк',
  '95e6bcfe' => '%u отправлена',
  '678d3ffd' => '%u отправлены',
  '74ddcc09' => '%u отправлено',
);

    protected function plural(int $n): int
    {
        return (int)(($n%10==0 || $n%10>4 || ($n%100>=11 && $n%100<=14) ? 2 : ($n%10 != 1)));
    }
}
