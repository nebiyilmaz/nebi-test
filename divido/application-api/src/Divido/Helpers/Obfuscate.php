<?php

declare(strict_types=1);
namespace Divido\Helpers;

class Obfuscate
{
    /**
     * @param string $name
     * @return string
     */
    public static function name(string $name): string
    {
        return mb_substr($name, 0, 2) . str_repeat('*', mb_strlen($name) - 2);
    }

    /**
     * @param string $email
     * @return string
     */
    public static function email(string $email): string
    {
        $domain = explode("@", $email);
        $name = implode(array_slice($domain, 0, count($domain) - 1), '@');

        return mb_substr($name, 0, 2) . str_repeat('*', mb_strlen($name) - 2) . "@" . end($domain);
    }

    /**
     * @param string $phoneNumber
     * @return string
     */
    public static function phoneNumber(string $phoneNumber): string
    {
        return (!empty($phoneNumber)) ? mb_substr($phoneNumber, 0, 2) . str_repeat('*', mb_strlen($phoneNumber) - 6) . mb_substr($phoneNumber, -4) : "";
    }
}
