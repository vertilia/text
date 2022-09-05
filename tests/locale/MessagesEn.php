<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesEn extends \Vertilia\Text\Text
{
    protected array $translations = array (
  859572441 => 'An apple',
  718478652 => 'Green',
  1304870824 => '%u line',
  985763646 => '%u lines',
  2551675992 => '%u sent',
  4010822862 => '%u sent',
);

    protected function plural(int $n): int
    {
        return (int)(($n != 1));
    }
}
