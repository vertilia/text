<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesRu extends \Vertilia\Text\Text
{
    protected array $translations = array (
  859572441 => 'Яблоко',
  718478652 => 'Зелёная',
  1304870824 => '%u строка',
  985763646 => '%u строки',
  2747850372 => '%u строк',
  2551675992 => '%u отправлена',
  4010822862 => '%u отправлены',
  1981357428 => '%u отправлено',
);

    protected function plural(int $n): int
    {
        return (int)(($n%10==1 && $n%100!=11 ? 0 : ($n%10>=2 && $n%10<=4 && ($n%100<12 || $n%100>14) ? 1 : 2)));
    }
}
