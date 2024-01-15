<?php

if (!function_exists('getLogPrefix')) {

    function getLogPrefix(array $parts): string
    {
        $parts = array_map(function($part) {
            return '[' . mb_strtolower($part) . ']';
        }, $parts);

        $prefix = implode('', $parts);

        return $prefix;
    }
}

if (!function_exists('getGameKeyFromUrl')) {

    function getGameKeyFromUrl(string $url): string
    {
        $parts = explode('-', $url);

        $key = $parts[count($parts) - 1];

        return $key;
    }
}