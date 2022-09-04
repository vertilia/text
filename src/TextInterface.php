<?php

namespace Vertilia\Text;

interface TextInterface
{
    /**
     * @param string $message
     * @return string translated message
     */
    public function _(string $message): string;

    /**
     * @param string $context
     * @param string $message
     * @return string translated message in given context
     */
    public function pget(string $context, string $message): string;

    /**
     * @param string $message1
     * @param string $message2
     * @param int $count
     * @return string plural form of translated message for provided $n
     */
    public function nget(string $message1, string $message2, int $count): string;

    /**
     * @param string $context
     * @param string $message1
     * @param string $message2
     * @param int $count
     * @return string plural form of translated message in given context for provided $n
     */
    public function npget(string $context, string $message1, string $message2, int $count): string;
}
