<?php

namespace Vertilia\Text;

class PoDomain
{
    protected int $line_length;
    protected array $messages = [];
    protected ?string $comment_tag;


    public function __construct(?string $comment_tag = null, int $line_length = 80)
    {
        $this->comment_tag = $comment_tag;
        $this->line_length = $line_length;
    }

    public function addMsg(array $msg)
    {
        // define unique id
        $msguid = sprintf(
            '%s%s%s',
            $msg['msgid'],
            isset($msg['msgctxt']) ? "\f$msg[msgctxt]" : '',
            isset($msg['msgid_plural']) ? "\f$msg[msgid_plural]" : ''
        );
        // add php-format flag if needed
        if (false !== strpos($msguid, '%')) {
            $flag = 'php-format';
            if (isset($msg['#,'])) {
                if (false === strpos($msg['#,'] ?? '', $flag)) {
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
    }

    public function msgTxt(string $msg): string
    {
        if (strlen($msg) > $this->line_length or false !== strpos($msg, "\n")) {
            $ret = ['""'];
            foreach (explode("\n", $msg) as $block) {
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
            return sprintf('"%s"', addcslashes($msg, "\0..\37\""));
        }
    }

    public function commentTxt(string $comment): ?string
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
                        return ltrim(substr($clean_comment, $tag_len));
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
        $buffer = [];
        array_unshift($this->messages, ['msgid' => '', 'msgstr' => sprintf("POT-Creation-Date: %s\n", date('c'))]);
        foreach ($this->messages as $msg) {
            if (isset($msg['#'])) {
                $buffer[] = sprintf("# %s\n", $msg['#']);
            }
            if (isset($msg['#.'])) {
                if (is_scalar($msg['#.'])) {
                    $comment_valid = $this->commentTxt($msg['#.']);
                    if (isset($comment_valid)) {
                        $buffer[] = sprintf("#. %s\n", $comment_valid);
                    }
                } elseif (is_array($msg['#.'])) {
                    $parts = [];
                    foreach ($msg['#.'] as $comment) {
                        $comment_valid = $this->commentTxt($comment);
                        if (isset($comment_valid)) {
                            $parts[] = str_replace("\n", "\n#. ", $comment_valid);
                        }
                    }
                    if ($parts) {
                        $buffer[] = sprintf("#. %s\n", implode("\n#. ", $parts));
                    }
                }
            }
            if (isset($msg['#:'])) {
                $refs = wordwrap(implode(' ', $msg['#:']), $this->line_length);
                $buffer[] = sprintf("#: %s\n", str_replace("\n", "\n#: ", $refs));
            }
            if (isset($msg['#,'])) {
                $buffer[] = sprintf("#, %s\n", $msg['#,']);
            }
            if (isset($msg['msgctxt'])) {
                $buffer[] = sprintf("msgctxt %s\n", $this->msgTxt($msg['msgctxt']));
            }
            if (isset($msg['msgid'])) {
                $buffer[] = sprintf("msgid %s\n", $this->msgTxt($msg['msgid']));
            }
            if (isset($msg['msgid_plural'])) {
                $buffer[] = sprintf("msgid_plural %s\n", $this->msgTxt($msg['msgid_plural']));
            }
            if (isset($msg['msgstr'])) {
                if (is_array($msg['msgstr'])) {
                    foreach ($msg['msgstr'] as $k => $form) {
                        $buffer[] = sprintf("msgstr[%u] %s\n", $k, $this->msgTxt($form));
                    }
                } else {
                    $buffer[] = sprintf("msgstr %s\n", $this->msgTxt($msg['msgstr']));
                }
            }
            $buffer[] = "\n";
        }

        return implode('', $buffer);
    }
}
