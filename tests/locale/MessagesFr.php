<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesFr extends \Vertilia\Text\Text
{
    protected array $translations = array (
  '333c08d9' => 'Une pomme',
  86727238 => 'Ceci est
une chaîne
multiligne',
  '2ad31d3c' => 'Verte',
  '4dc6bfa8' => '%u ligne',
  '3ac18f3e' => '%u lignes',
  '98177c58' => '%u envoyée',
  'ef104cce' => '%u envoyées',
);

    protected function plural(int $n): int
    {
        return (int)(($n > 1));
    }
}
