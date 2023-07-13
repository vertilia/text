<?php

use Vertilia\Text\Text;

$txt = new Text();

/**
 *  untagged comment
 */
/** TRANSLATORS: tagged comment of type /* */
/// TRANSLATORS: tagged comment of type //
### TRANSLATORS: tagged comment of type #
Text::_('simple $string');  // 1
$txt->_('simple $string');  // 1
_('simple $string');        // 1
gettext('simple $string');  // 1
//gettext('single form');     // 1 <- this one will generate xgettext error since clashes with plural forms

Text::pget('context', 'simple message');    // 1c,2
$txt->pget('context', 'simple message');    // 1c,2

Text::nget('single form', 'plural form', 5);    // 1,2
$txt->nget('single form', 'plural form', 5);    // 1,2
ngettext('single form', 'plural form', 5);      // 1,2

Text::npget('context', 'single form', 'plural form', 5);    // 1c,2,3
$txt->npget('context', 'single form', 'plural form', 5);    // 1c,2,3

dgettext('domain', 'simple message');               // 2
dcgettext('domain', 'simple message', LC_MESSAGES); // 2

dngettext('domain', 'single form', 'plural form');                  // 2,3
dcngettext('domain', 'single form', 'plural form', 5, LC_MESSAGES); // 2,3

Text::_("complex \n\"\$string\"");

Text::_("complex and a very long message containing several paragraphs. Every paragraph is terminated by a new line character\n\"\$string\" is then continued on a new line and may end with a new line\n");

Text::_('single quoted
$string');

Text::_("double quoted
\$string");

Text::_(<<<EOS
simple
\$heredoc
EOS
);

Text::_(<<<"EOS"
double quoted simple
\$heredoc
EOS
);

Text::_(<<<'EOS'
simple
$nowdoc
EOS
);

Text::_(<<<EOS
    indented
    \$heredoc
    EOS
);

Text::_(<<<"EOS"
    double quoted indented
    \$heredoc
    EOS
);

Text::_(<<<'EOS'
    indented $nowdoc
    EOS
);

Text::npget(
    'test',
    <<<'EOS'
    indented $nowdoc
    EOS,
    'plural',
    2
);
