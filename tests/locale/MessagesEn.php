<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesEn extends \Vertilia\Text\Text
{
    protected array $translations = array (
  '333c08d9' => 'An apple',
  86727238 => 'This is a
multiline
string',
  '2ad31d3c' => 'Green',
  '4dc6bfa8' => '%u line',
  '3ac18f3e' => '%u lines',
  '98177c58' => '%u sent',
  'ef104cce' => '%u sent',
);

    protected function plural(int $n): int
    {
        return (int)(($n != 1));
    }
}
