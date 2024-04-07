<?php

use Vertilia\Text\Text;

$txt = new Text();

/**
 *  untagged comment
 */
/** TRANSLATORS: tagged comment of type /* */
/// TRANSLATORS: tagged comment of type //
### TRANSLATORS: tagged comment of type #
Text::_(/* TRANSLATORS: simple arg comment */ 'simple $string');  // 1
$txt->_('simple $string');  // 1
_('simple $string');        // 1
gettext('simple $string');  // 1
gettext(/* TRANSLATORS: concat arg comment */ 'concat' . ' $string');  // 1
//gettext('single form');     // 1 <- this one will generate xgettext error since clashes with plural forms

// TRANSLATORS: simple message comment 1
Text::pget('context', /* TRANSLATORS: simple message comment 2 */ 'simple message');    // 1c,2
$txt->pget('context', 'simple message');    // 1c,2
pgettext('pgettext', 'simple message');     // 1c,2

/* TRANSLATORS: outer comment */
Text::nget(
    /* TRANSLATORS: singular form comment */ 'singular form %u',
    /* TRANSLATORS: plural comment */ 'plural form %u',
    5
);    // 1,2
$txt->nget('singular form %u', 'plural form %u', 5);    // 1,2
ngettext('singular form %u', 'plural form %u', 5);      // 1,2

Text::npget(
    'context',
    /* TRANSLATORS: singular comment */'singular form %u',
    /* TRANSLATORS: plural comment */'plural form %u',
    5
);    // 1c,2,3
$txt->npget('context', 'singular form %u', 'plural form %u', 5);    // 1c,2,3
npgettext('context', 'singular form %u', 'plural form %u', 5);      // 1c,2,3

/* TRANSLATORS: domain comment */
dgettext('domain', 'simple message');               // 2
dcgettext('domain', 'simple message', LC_MESSAGES); // 2

dngettext('domain', 'singular form %u', 'plural form %u');                  // 2,3
dcngettext('domain', 'singular form %u', 'plural form %u', 5, LC_MESSAGES); // 2,3

$txt->_("complex \n\"\$string\"");

$txt->_("complex and a very long message containing several paragraphs. Every paragraph is terminated by a new line character\n\"\$string\" is then continued on a new line and may end with a new line\n");

$txt->_('single quoted
$string');

$txt->_("double quoted
\$string");

$txt->_(<<<EOS
simple
\$heredoc
EOS
);

$txt->_(<<<"EOS"
double quoted simple
\$heredoc
EOS
);

$txt->_(<<<'EOS'
simple
$nowdoc
EOS
);

$txt->_(<<<EOS
    indented
    \$heredoc
    EOS
);

$txt->_(<<<"EOS"
    double quoted indented
    \$heredoc
    EOS
);

$txt->_(<<<'EOS'
    indented $nowdoc
    EOS
);

$txt->npget(
    'test',
    <<<'EOS'
    indented $nowdoc
    EOS,
    'plural',
    2
);
