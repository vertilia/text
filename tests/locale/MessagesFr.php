<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesFr extends \Vertilia\Text\Text
{
    protected array $translations =
array (
  'd0f0e2a5' => 'Guillemets: \'apostrophes\', "quotes"; interprétation de vars: $i, ${i}, {$i}; formats printf: %s, %2$s',
  'f67ebb98' => 'Une pomme',
  'ea7a212a' => 'Une pomme verte',
  '4ad68324' => 'Ceci est
une chaîne
multiligne',
  '0aabfa28' => 'Verte',
  'ccf6a32c' => '%u ligne',
  '3e9d202f' => '%u lignes',
  '95e6bcfe' => '%u envoyée',
  '678d3ffd' => '%u envoyées',
);

    protected function plural(int $n): int
    {
        return (int)(($n > 1));
    }
}
