# text

Translation library based on [gettext](https://www.gnu.org/software/gettext) concept, tools and PO files.

## Description

This library is intended to continue using gettext as project internationalization approach, keeping proven localization
methodology but eliminate dependency on complex target system configuration and on `gettext` php extension. This latter
may become unsuitable for specific environments.

`xgettext` extractor from standard gettext toolchain does not handle correctly PHP heredoc/nowdoc syntax on all systems.
Here we provide a replacement `xtext` script that extracts strings into a POT format, which may be used as a source file
for your gettext workflow.

PO catalogue (representing a gettext domain), extracted from the codebase (by standard gettext method or using `xtext`)
is then transformed by the bundled `po2php` tool to a native `.php` class. This class keeps translations in memory and
implements language-specific rules as native php code.

Simple messages are maintained via the `_()` method, plural forms and contexts are maintained via corresponding
`nget()`, `pget()` and `npget()` methods.

## Advantages

> **From manual:**<br>
> GNU `gettext` is designed to minimize the impact of internationalization on program sources, keeping this impact as
> small and hardly noticeable as possible. Internationalization has better chances of succeeding if it is very light
> weighted, or at least, appear to be so, when looking at program sources.

- no need to use constants or other intermediate constructs replacing messages in source code;
- handling of plural forms;
- handling of context-aware functions (`pgettext` family) that are currently missing from php extension;
- no need to install additional locales on target OS or setting environment variables;
- standard translation process based on PO files; multitude of editors or processes may be used;
- translations are stored in `.php` files which allows for a quick autoloading and opcode caching, minimized runtime
  effort to get translation into memory and finding translation for source string;
- stable and predictable work in multiprocess web environments, not tied to host system configuration and currently
  installed system locales;
- bundled tools to correctly handle PHP heredoc/nowdoc syntax and to compile .PO files to native php code.

## Installation

```shell
composer require vertilia/text
```

## Usage

Programming in C with gettext historically consists of the following phases (simplified):

1. define the source messages language used in your code (normally english)
2. use gettext functions in your source code when working with localized messages
   - (without existing translations, gettext functions simply return the passed strings, so the code is already
     working at this stage, returning english messages in all environments)
3. use gettext utilities to scan your code and extract localized messages, producing (or updating) `.po` text files
   - (`.po` text files contain messages extracted from code, translations to the target language and rules for plural
     forms of the target language)
4. translate new/updated messages in `.po` file
5. compile text `.po` file into binary `.mo` file
6. copy `.mo` file into your code, so that now gettext functions (mentioned in 2.) could use them to extract
   translations for their arguments
7. if new language is added to application, assure the corresponding locale is configured on target system

In `Text` we can bypass phases 5., 6. and 7., and compile `.po` files directly into `.php` classes, containing
translated strings and plural forms rules for target language.

These generated classes are stored in `locale/` folder and are configured via composer autoloader. They are handled by
opcode cache just like other php code and use the lowest footprint at runtime since only need one CRC32 transformation
to return existing translation (or a lack of one).

For large codebases, as with normal gettext, you can break down translations on domains, which mostly signifies using
different `.po` files per domain.

When you start the project, and while you have no `.po` file yet, you can use the base `\Vertilia\Text\Text` object to
provide translated text. It will simply return the passed argument as translated message, which is mostly ok for
debugging purposes. Even in its basic form, it will already be smart enough to correctly handle plural forms for English
messages:

```php
<?php

include __DIR__ . '/../vendor/autoload.php';

$t = new Vertilia\Text\Text();

echo $t->_('Just a test'), PHP_EOL;                      // output: Just a test
echo $t->pget('page', 'Next'), PHP_EOL;                  // output: Next
echo $t->nget('One page', 'Multiple pages', 1), PHP_EOL; // output: One page
echo $t->nget('One page', 'Multiple pages', 5), PHP_EOL; // output: Multiple pages
echo $t->npget('page', 'One sent', 'Multiple sent', 5), PHP_EOL; // output: Multiple sent
```

When you extract messages from the above code with gettext tools (we highly recommend using widely-available translation
tools, like [POedit](https://poedit.net/) or others) you'll produce a text file with `.po` extension containing source
language messages, placeholders for target language translations, say French, and a rule for French plural form
conversion. After translating the placeholders in `.po` file into French, you normally include the result file in your
project as `locale/fr_FR/LC_MESSAGES/messages.po`. This is a GNU norm, but with `Text` you may use any folder and
filename. The most important part here is that you (and other users of your codebase) could easily locate translation
files and clearly distinguish languages and domains.

> See [Keywords for `xgettext`](#keywords-for-xgettext) below for things to configure when running gettext tools on your
> codebase.

> See [`xtext` reference](#xtext-reference) below if `xgettext` is not working correctly on your system.

Simplified view of the contents of `messages.po` file for our project:

| source (en)                       | target (fr)          |
|-----------------------------------|----------------------|
| `plural form rule:`               | `(n > 1)`            |
| "Just a test"                     | "Juste un test"      |
| "Next" (context: "page")          | "Suivante"           |
| "One page"                        | "Une page"           |
| "Multiple pages"                  | "Plusieurs pages"    |
| "One sent" (context: "page")      | "Une envoyée"        |
| "Multiple sent" (context: "page") | "Plusieurs envoyées" |

Note where you stored the resulting `messages.po` file, since you'll need it right away to produce translations class.
To do this you'll run the bundled `po2php` command (see examples [below](#po2php-reference)) and give it the path to the
`messages.po` file. It will output the php code that you will include in your project as an easily located
`locale-src/MessagesFr.php` file:

```shell
vendor/bin/po2php -n App\\L10n -c MessagesFr \
  locale/fr_FR/LC_MESSAGES/messages.po \
  >src/L10n/MessagesFr.php
```

So for now, you generated 2 additional files in your project:

- `locale/fr_FR/LC_MESSAGES/messages.po`: text file with English source messages from your application code, French
  translations for every message and a simple rule that describes the use of plural form in target language, French. You
  will need this file to update existing translations, add new ones and remove unused ones. This is a standard PO file
  that you may edit with many available tools. Every translation bureau will handle this format (if it does not, you
  better choose another one).
- `locale-src/MessagesFr.php`: php class generated from `messages.po` file that encapsulates translations for target
  language and a method for handling French plural form.

Now it's time to use the generated `MessagesFr` class instead of the base `Text` to display your translated messages:

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$t = new App\L10n\MessagesFr();

echo $t->_('Just a test'), PHP_EOL;                      // output: Juste un test
echo $t->pget('page', 'Next'), PHP_EOL;                  // output: Suivante
echo $t->nget('One page', 'Multiple pages', 1), PHP_EOL; // output: Une page
echo $t->nget('One page', 'Multiple pages', 5), PHP_EOL; // output: Plusieurs pages
echo $t->npget('page', 'One sent', 'Multiple sent', 5), PHP_EOL; // output: Plusieurs envoyées
```

> See [Proposed configuration for `composer` and `git`](#proposed-configuration-for-composer-and-git) below for sample
> `composer` configuration.

Now it's up to you to create translations for other languages.

## Process overview

`Vertilia\Text\Text` object contains methods to handle translated messages. Base class does not have translations, so
its methods simply return passed arguments. When translations are added to `.po` files, they are saved as language
classes extending `Vertilia\Text\Text` base class with translations and overridden `plural()` method to select
correct plural forms in target languages.

Normally your code will consist of injecting `Vertilia\Text\TextInterface` objects, creating messages and work with
external PO editor program to extract messages from code, handle translations in `.po` files, and update language
classes with `po2php` tool.

Your localization process with `Text` will follow the following path:
```mermaid
graph
    A[Add/Update messages wrapped by Text methods] -->|gettext| B
    B[Update .po files] -->|po2php| C
    C[Update .php files] -->|Autoloader| A
```

## `Text` reference

### `Text::_()`

Translate message

```php
public function _(string $message): string;
```

#### Parameters
- `$message` Message in source language to translate.

#### Return value
Message translated into target language (or original message if translation not found).

#### Example 1: base class, no translation
```php
$t = \Vertilia\Text\Text();
echo $t->_("Several words"); // output: Several words
```

#### Example 2: `MessagesRu` class created after translating `messages.po` into Russian
```php
$t = \App\L10n\MessagesRu();
echo $t->_("Several words"); // output: Несколько слов
```

### `Text::nget()`

Translation for plural forms of the message, based on argument.

```php
public function nget(string $singular, string $plural, int $count): string;
```

#### Parameters
- `$singular` Singular form of message in source language.
- `$plural` Plural form of message in source language.
- `$count` Counter to select the plural form.

#### Return value
One of plural forms of translated message in target language for provided `$count`.

#### Example 1: base class, no translation
```php
$t = \Vertilia\Text\Text();
printf($t->nget("%u word", "%u words", 1), 1); // output: 1 word
printf($t->nget("%u word", "%u words", 2), 2); // output: 2 words
printf($t->nget("%u word", "%u words", 5), 5); // output: 5 words
```

#### Example 2: `MessagesRu` class created after translating `messages.po` into Russian
```php
$t = \App\L10n\MessagesRu();
printf($t->nget("%u word", "%u words", 1), 1); // output: 1 слово
printf($t->nget("%u word", "%u words", 2), 2); // output: 2 слова
printf($t->nget("%u word", "%u words", 5), 5); // output: 5 слов
```

### `Text::npget()`

Translate plural form of the message in given context, based on argument.

```php
public function npget(string $context, string $singular, string $plural, int $count): string;
```

#### Parameters
- `$context` Context of message in source language.
- `$singular` Singular form of message in source language.
- `$plural` Plural form of message in source language.
- `$count` Counter to select the plural form.

#### Return value
One of plural forms of translated message in target language and context for provided `$count`.

#### Example 1: base class, no translation
```php
$t = \Vertilia\Text\Text();
printf($t->npget("star", "%u bright", "%u bright", 1), 1); // output: 1 bright
printf($t->npget("star", "%u bright", "%u bright", 2), 2); // output: 2 bright
printf($t->npget("star", "%u bright", "%u bright", 5), 5); // output: 5 bright
```

#### Example 2: `MessagesRu` class created after translating `messages.po` into Russian
```php
$t = \App\L10n\MessagesRu();
printf($t->npget("star", "%u bright", "%u bright", 1), 1); // output: 1 яркая
printf($t->npget("star", "%u bright", "%u bright", 2), 2); // output: 2 яркие
printf($t->npget("star", "%u bright", "%u bright", 5), 5); // output: 5 ярких
```

### `Text::pget()`

Translate the message in given context.

```php
public function pget(string $context, string $message): string;
```

#### Parameters
- `$context` Context of the message in source language.
- `$message` Message in source language to translate.

#### Return value
Translated message in target language and context.

#### Example 1: base class, no translation
```php
$t = \Vertilia\Text\Text();
printf($t->npget("star", "It's bright")); // output: It's bright
```

#### Example 2: `MessagesRu` class created after translating `messages.po` into Russian
```php
$t = \App\L10n\MessagesRu();
printf($t->npget("star", "It's bright")); // output: Она яркая
```

## Keywords for `xgettext`

To allow `xgettext` to extract messages from `Text` methods that replace classic gettext functions, the
following configuration should be provided for `xgettext` command line utility:
```
xgettext ... --keyword=_ --keyword=pget:1c,2 --keyword=nget:1,2 --keyword=npget:1c,2,3
```

GUI utilities like POedit will provide a configuration screen where the keywords may be specified as the following list:

- `nget:1,2`
- `pget:1c,2`
- `npget:1c,2,3`

## Proposed configuration for `composer` and `git`

When producing `Text` classes we recommend you to store them in `L10n/` folder of your application. Consider the
following layout (simplified, 3 languages):
```
/app/
├─ locale/
│  ├─ en_US/
│  │  └─ LC_MESSAGES/
│  │     └─ messages.po
│  ├─ fr_FR/
│  │  └─ LC_MESSAGES/
│  │     └─ messages.po
│  ├─ ru_RU/
│  │  └─ LC_MESSAGES/
│  │     └─ messages.po
│  └─ messages.pot
├─ src/
│  ├─ L10n/
│  │  ├─ MessagesEn.php
│  │  ├─ MessagesFr.php
│  │  └─ MessagesRu.php
│  └─ ...
├─ vendor/
│  ├─ autoload.php
│  ├─ composer/
│  └─ vertilia/
├─ www/
│  └─ index.php
├─ .gitattributes
└─ composer.json
```

Here, your application code is located in `src/` and `www/` folders and, presuming the application namespace is `App`,
messages classes namespace is `App\L10n`, your composer `autoload` directive is configured as follows:
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

Please note, `locale/` folder storing `.po` files is separated from `L10n/` folder with `Text` message classes to
simplify exclusion of this folder from the binary version of your application. You don't need intermediate files on
production hosts, so you will most likely include the following line into your `.gitattributes`:
```
/locale export-ignore
```

## `xtext` reference

```shell
vendor/bin/xtext -h
```
```
Usage: xtext [OPTIONS] FILES,
OPTIONS:
-h      Display usage message and quit
-x EXCLUDE_PATH
        Pattern to exclude when scanning FILES, may be repeated to
        provide multiple patterns. Executes before -i.
-i INCLUDE_PATH
        Pattern to include when scanning FILES, may be repeated to
        provide multiple paths. Executes after -x. Default: *
-c COMMENT_TAG
        Comment tag to mark extractable comments from the source code.
        Use empty string to extract all comments
FILES:
        A list of one or more file or directory. If directory names are
        specified, they are scanned recursively. Options -x and -i are
        used to limit processed files.
EXAMPLES:
xtext *.php
        Scan all .php files in current dir
xtext -c '' *.php
        Scan all .php files in current dir, also extract comments
xtext -x /*/vendor -x /*/tests -i '*.php' -c TRANSLATORS: /app >msg.pot
        Scan all .php files in /app directory and sub-directories,
        excluding vendor and tests folders, extract comments starting
        with TRANSLATORS: tag and write output to msg.pot file
```

On some systems, `xgettext` tool used to scan php sources and extract translatable strings may break if php files use
heredoc/nowdoc syntax, especially with indented closing identifier (available since php 7.3).

To allow correct extraction of gettext lines from the sources, bundled `xtext` utility may be used instead. This tool
will scan the source folders, find the translatable strings and output a POT file, containing all detected strings. It
maintains the code references for translations and may also include comments that precede calls to corresponding `Text`
and `gettext` functions in the code.

POedit tool mentioned above may use both methods of updating existing PO files, either by scanning source directories
with `xgettext` to produce the POT file and transparently merge it with existing translations, or use an existing POT
file with extracted translations. First method is simpler, but if POedit cannot extract translations from all files in
your codebase automatically, you may generate the POT file with `xtext` and use it to update translations.

## `po2php` reference

```shell
vendor/bin/po2php --help
```
```
Usage: po2php [OPTIONS] messages.po
OPTIONS:
-n, --namespace=NAMESPACE   Namespace to use (default: none)
-c, --class=CLASS_NAME      Class name (default: Messages)
-e, --extends=PARENT_CLASS  Parent class name implementing \Vertilia\Text\TextInterface
                            (default: \Vertilia\Text\Text)
-5, --php5                  Produce php5-compatible code (use php5 branch of Text)
-h, --help                  Print this screen
```

#### Example 1: generate `MessagesRu` catalog in `tests/locale`

```shell
vendor/bin/po2php -n App\\Tests\\Locale -c MessagesRu \
  tests/locale/ru_RU/LC_MESSAGES/messages.po \
  >tests/locale/MessagesRu.php
```

#### Example 2: generate `MessagesRu` catalog in `tests/locale` with `docker`

```shell
docker run --rm --volume "$PWD":/app php \
  /app/vendor/bin/po2php -n App\\Tests\\Locale -c MessagesRu \
  /app/tests/locale/ru_RU/LC_MESSAGES/messages.po \
  >tests/locale/MessagesRu.php
```

## Plural forms in different languages

The plural form selector, incorporated into PO files, is in fact a C language code snippet that returns a 0-based index
of a plural form. In most cases this code translates to php in quite a straightforward way, like in example below:
```
# English, nplurals = 2
(n != 1)
```
Germanic-family languages like English or German only have 2 plural forms and every number which is not 1 is actually a
plural:

| example | plural form |
|---------|-------------|
| 0 lines | 1           |
| 1 line  | 0           |
| 2 lines | 1           |
| 3 lines | 1           |

Other language families may contain a more complex condition:
```
# Russian, nplurals = 3
(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<12 || n%100>14) ? 1 : 2)
```
```
# Russian (alternative form), nplurals = 3
(n%10==0 || n%10>4 || (n%100>=11 && n%100<=14) ? 2 : n%10 != 1)
```
Slavic-family languages like Russian or Serbian have 3 plural forms, which are close to impossible to describe in one
phrase. Single form is for every number that terminates by 1 (but not by 11). First plural form is for numbers
terminated by 2, 3 or 4 (but not by 12, 13 or 14). All the rest (including 0, 11, 12, 13 and 14) are second plural form.
Examples:

| example    | plural form |
|------------|-------------|
| 0 строк    | 2           |
| 1 строка   | 0           |
| 2 строки   | 1           |
| 5 строк    | 2           |
| 10 строк   | 2           |
| 11 строк   | 2           |
| 12 строк   | 2           |
| 15 строк   | 2           |
| 20 строк   | 2           |
| 21 строка  | 0           |
| 22 строки  | 1           |
| 25 строк   | 2           |
| 100 строк  | 2           |
| 101 строка | 0           |
| 102 строки | 1           |
| 105 строк  | 2           |

You can find more examples at [gettext manual](https://www.gnu.org/software/gettext/manual/html_node/Plural-forms.html).

## Plural forms rules rewrite for specific languages (before php 8.0)

Ternary condition statement in php before version 8.0 has other associativity than in C, so the default rule in PO file
for languages using chained ternary operators for plural form selector needs to be corrected with additional parenthesis
in PO file:
```
# Russian (php7-compat), nplurals = 3
(n%10==1 && n%100!=11 ? 0 : (n%10>=2 && n%10<=4 && (n%100<12 || n%100>14) ? 1 : 2))
```
```
# Russian (php7-compat alternative form), nplurals = 3
(n%10==0 || n%10>4 || (n%100>=11 && n%100<=14) ? 2 : (n%10 != 1))
```

## Plural forms usage with `printf()`

In brief: to produce lines using plural forms based on a variable value, use the following construct:
```php
printf($t->nget("%d file removed", "%d files removed", $n), $n);
```

Here, the first call to `nget` with `$n` will select corresponding format string either for single or for plural form,
and then this selected string will be passed to `printf` with another `$n` which will now be inserted in corresponding
`%d` placeholder.

For detailed discussion see [gettext manual](https://www.gnu.org/software/gettext/manual/html_node/Plural-forms.html).

## Class methods replacement for gettext functions

| `gettext` function                  | `Text` method |
|-------------------------------------|---------------|
| `_()`, `gettext()`                  | `_()`         |
| `ngettext()`                        | `nget()`      |
| context-aware (`pgettext` in C API) | `pget()`      |
| `npgettext()`                       | `npget()`     |

Note the lack of domain functions (`dgettext`, ...) Domains in gettext are represented by different translation files,
so to use a translation from another domain one should instantiate another `Text` object.

Example:
- `gettext`-style:
  ```php
  putenv('LC_ALL=fr_FR');
  setlocale(LC_ALL, 'fr_FR');
  bindtextdomain("myPHPApp", "./locale");
  textdomain("myPHPApp");
  echo gettext("Welcome to My PHP Application"), "\n";
  echo dgettext("anotherPHPApp", "Welcome to Another PHP Application"), "\n";
  ```
- `Text`-style:
  ```php
  $myDomain = \App\L10n\MyPhpAppFr();
  $anotherDomain = \App\L10n\AnotherPhpAppFr();
  echo $myDomain->_("Welcome to My PHP Application"), "\n";
  echo $anotherDomain->_("Welcome to Another PHP Application"), "\n";
  ```

Also, context-aware functions (`pgettext` family) are missing from bundled PHP gettext extension.

## Resources

- GNU gettext manual:
  https://www.gnu.org/software/gettext/manual/gettext.html
- Preparing translatable strings:
  https://www.gnu.org/software/gettext/manual/html_node/Preparing-Strings.html#Preparing-Strings
- POedit translations editor:
  https://poedit.net/
- `.gitattributes`:
  https://git-scm.com/docs/gitattributes#_creating_an_archive