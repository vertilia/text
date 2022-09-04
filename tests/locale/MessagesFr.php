<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesFr extends \Vertilia\Text\Text
{
    protected array $translations = array (
  859572441 => 'Une pomme',
  718478652 => 'Verte',
  1304870824 => '%u ligne',
  985763646 => '%u lignes',
  2551675992 => '%u envoyée',
  4010822862 => '%u envoyées',
);

    protected function plural(int $n): int
    {
        return (int)(($n > 1));
    }
}
