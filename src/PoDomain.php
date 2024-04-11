<?php

namespace Vertilia\Text;

class PoDomain
{
    protected int $line_length;
    protected array $messages = [];
    protected ?string $comment_tag;

    // regex to detect a php sprintf string, we only exclude the space flag
    // because that is the default and the most likely to raise false positives
    //                            %|argnum$|flags_|w_|precision___|specifier________
    private string $fmt_regex = '/%(\d+\$)?[+0#-]*\d*(\.\d+|\.\*)?[bcdeEfFgGhHosuxX]/';

    public function __construct(?string $comment_tag = null, int $line_length = 80)
    {
        $this->comment_tag = $comment_tag;
        $this->line_length = $line_length;
    }

    public function addMsg(array $msg): self
    {
        if (!isset($msg['msgid'])) {
            return $this;
        }

        // define unique id
        $msguid = sprintf(
            '%s%s%s',
            $msg['msgid'],
            isset($msg['msgctxt']) ? "\f$msg[msgctxt]" : '',
            isset($msg['msgid_plural']) ? "\f$msg[msgid_plural]" : ''
        );

        // add php-format flag if needed
        if (preg_match($this->fmt_regex, $msguid)) {
            $flag = 'php-format';
            if (isset($msg['#,'])) {
                if (false === strpos($msg['#,'], $flag)) {
                    $msg['#,'] = "$flag,{$msg['#,']}";
                }
            } else {
                $msg['#,'] = $flag;
            }
        }

        // handle reference
        if (isset($this->messages[$msguid])) {
            if (isset($msg['#:']) and is_scalar($msg['#:'])) {
                $this->messages[$msguid]['#:'][] = $msg['#:'];
            }
        } else {
            if (isset($msg['#:']) and is_scalar($msg['#:'])) {
                $msg['#:'] = [$msg['#:']];
            }
            $this->messages[$msguid] = $msg;
        }

        return $this;
    }

    protected function wrapString(string $string): string
    {
        if (strlen($string) > $this->line_length or false !== strpos($string, "\n")) {
            $ret = ['""'];
            foreach (explode("\n", $string) as $block) {
                $block .= "\n";
                foreach (mb_str_split($block, $this->line_length) as $line) {
                    $ret[] = sprintf('"%s"', addcslashes($line, "\0..\37\""));
                }
            }
            $last = array_key_last($ret);
            $ret[$last] = substr($ret[$last], 0, -3) . '"';
            if ($ret[$last] === '""') {
                unset($ret[$last]);
            }
            return implode("\n", $ret);
        } else {
            return sprintf('"%s"', addcslashes($string, "\0..\37\""));
        }
    }

    protected function extractComment(string $comment): ?string
    {
        if (preg_match('%^/\*[\s*]*(.+)\*+/$%s', $comment, $m)
            or preg_match('%^/+(.+)%', $comment, $m)
            or preg_match('%^#+(.+)%', $comment, $m)
        ) {
            $clean_comment = trim($m[1]);
            if (is_string($this->comment_tag)) {
                $tag_len = strlen($this->comment_tag);
                if ($tag_len) {
                    if (strncmp($this->comment_tag, $clean_comment, $tag_len) === 0) {
                        return $clean_comment;
                    } else {
                        return null;
                    }
                } else {
                    return $clean_comment;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function __toString(): string
    {
        // sort reference array of every message and create the sorting value
        foreach ($this->messages as &$_msg) {
            if (isset($_msg['#:'])) {
                natsort($_msg['#:']);
                $_msg['sortby'] = join(' ', $_msg['#:']);
            } else {
                $_msg['sortby'] = '';
            }
        }

        // sort messages by the sorting value
        uasort($this->messages, fn($a, $b) => strnatcmp($a['sortby'], $b['sortby']));

        // add header if not set
        if (!isset($this->messages[0])) {
            array_unshift(
                $this->messages,
                [
                    'msgid' => '',
                    'msgstr' => sprintf("POT-Creation-Date: %s\n", date('c')),
                ]
            );
        }

        //
        $records = [];
        foreach ($this->messages as $msg) {
            $lines = [];
            if (isset($msg['#'])) {
                if (is_array($msg['#'])) {
                    foreach ($msg['#'] as $line) {
                        $lines[] = sprintf("# %s\n", $line);
                    }
                } else {
                    $lines[] = sprintf("# %s\n", $msg['#']);
                }
            }
            if (isset($msg['#.'])) {
                if (is_scalar($msg['#.'])) {
                    $comment_valid = $this->extractComment($msg['#.']);
                    if (isset($comment_valid)) {
                        $lines[] = sprintf("#. %s\n", $comment_valid);
                    }
                } elseif (is_array($msg['#.'])) {
                    $comments = [];
                    foreach ($msg['#.'] as $comment) {
                        $comment_valid = $this->extractComment($comment);
                        if (isset($comment_valid)) {
                            $comments[] = str_replace("\n", "\n#. ", $comment_valid);
                        }
                    }
                    if ($comments) {
                        $lines[] = sprintf("#. %s\n", implode("\n#. ", $comments));
                    }
                }
            }
            if (isset($msg['#:'])) {
                $refs = wordwrap(implode(' ', $msg['#:']), $this->line_length);
                $lines[] = sprintf("#: %s\n", str_replace("\n", "\n#: ", $refs));
            }
            if (isset($msg['#,'])) {
                $lines[] = sprintf("#, %s\n", $msg['#,']);
            }
            if (isset($msg['msgctxt'])) {
                $lines[] = sprintf("msgctxt %s\n", $this->wrapString($msg['msgctxt']));
            }
            if (isset($msg['msgid'])) {
                $lines[] = sprintf("msgid %s\n", $this->wrapString($msg['msgid']));
            }
            if (isset($msg['msgid_plural'])) {
                $lines[] = sprintf("msgid_plural %s\n", $this->wrapString($msg['msgid_plural']));
            }
            if (isset($msg['msgstr'])) {
                if (is_array($msg['msgstr'])) {
                    foreach ($msg['msgstr'] as $k => $form) {
                        $lines[] = sprintf("msgstr[%u] %s\n", $k, $this->wrapString($form));
                    }
                } else {
                    $lines[] = sprintf("msgstr %s\n", $this->wrapString($msg['msgstr']));
                }
            }
            $records[] = implode('', $lines);
        }

        return implode("\n", $records);
    }
}
