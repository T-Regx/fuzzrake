<?php

namespace App\Utils;

use App\Entity\Artisan;
use App\Utils\Regexp\Utils as Regexp;

class StrUtils
{
    private function __construct()
    {
    }

    public static function artisanNamesSafeForCli(Artisan ...$artisans): string
    {
        $names = $makerIds = [];

        foreach (array_filter($artisans) as $artisan) {
            $names = array_merge($names, $artisan->getAllNamesArr());
            $makerIds = array_merge($makerIds, $artisan->getAllMakerIdsArr());
        }

        return self::strSafeForCli(implode(' / ', array_merge(
            array_filter(array_unique($names)),
            array_filter(array_unique($makerIds))
        )));
    }

    public static function strSafeForCli(string $input): string
    {
        return str_replace(["\r", "\n", '\\'], ['\r', '\n', '\\'], $input);
    }

    public static function undoStrSafeForCli(string $input): string
    {
        return str_replace(['\r', '\n', '\\'], ["\r", "\n", '\\'], $input);
    }

    public static function shortPrintUrl(string $originalUrl): string
    {
        $url = Regexp::replace('#^https?://(www\.)?#', '', $originalUrl);
        $url = Regexp::replace('/\/?(#profile)?$/', '', $url);
        $url = str_replace('/user/', '/u/', $url);
        $url = str_replace('/journal/', '/j/', $url);

        if (strlen($url) > 50) {
            $url = substr($url, 0, 40).'...';
        }

        return $url;
    }

    public static function ucfirst(string $input): string
    {
        return mb_strtoupper(mb_substr($input, 0, 1)).mb_substr($input, 1);
    }
}
