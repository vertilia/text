<?php

namespace Vertilia\Text\Tests\Locale;

class MessagesEn extends \Vertilia\Text\Text
{
    protected array $translations =
array (
  'd0f0e2a5' => 'Quotes: \'apostrophes\', "quotes"; var parsing: $i, ${i}, {$i}; printf formats: %s, %2$s',
  'f67ebb98' => 'An apple',
  'ea7a212a' => 'A green apple',
  '52be8a2d' => 'Concatenated line',
  '4ad68324' => 'This is a
multiline
string',
  '0aabfa28' => 'Green',
  'ccf6a32c' => '%u line',
  '3e9d202f' => '%u lines',
  '95e6bcfe' => '%u sent',
  '678d3ffd' => '%u sent',
);

    protected function plural(int $n): int
    {
        return (int)(($n != 1));
    }
}
