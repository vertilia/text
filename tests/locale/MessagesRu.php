<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesRu extends \Vertilia\Text\Text
{
    protected array $translations =
array (
  '6176c157' => 'Кавычки: \'апострофы\', "кавычки"; обработка переменных: $i, ${i}, {$i}; форматы printf: %s, %2$s',
  '333c08d9' => 'Яблоко',
  86727238 => 'Вот это
многострочный
текст',
  '2ad31d3c' => 'Зелёная',
  '4dc6bfa8' => '%u строка',
  '3ac18f3e' => '%u строки',
  'a3c8de84' => '%u строк',
  '98177c58' => '%u отправлена',
  'ef104cce' => '%u отправлены',
  '76191d74' => '%u отправлено',
);

    protected function plural(int $n): int
    {
        return (int)(($n%10==0 || $n%10>4 || ($n%100>=11 && $n%100<=14) ? 2 : ($n%10 != 1)));
    }
}
