<?php

declare(strict_types=1);

namespace Wnull\Selective\Helper;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

use function array_filter;
use function explode;
use function implode;
use function sprintf;
use function trim;

trait CookieAssistant
{
    protected function cookiesStringToArray(string $cookies): array
    {
        $result = [];

        $list = array_filter(explode(';', $cookies));
        foreach ($list as $cookie) {
            [$key, $value] = explode('=', $cookie);
            $result[trim($key)] = trim($value);
        }

        return $result;
    }

    protected function guzzleCookiesArrayNormalize(array $cookies): array
    {
        $result = [];

        foreach ($cookies as $cookie) {
            if (isset($cookie['Name'], $cookie['Value'])) {
                $result[$cookie['Name']] = $cookie['Value'];
            }
        }

        return $result;
    }

    protected function arrayCookiesToString(array $cookies): string
    {
        $result = [];

        foreach ($cookies as $name => $value) {
            $result[] = sprintf('%s=%s', $name, $value);
        }

        return implode(';', $result);
    }

    protected function arrayCookiesToJar(array $cookies, string $host): CookieJar
    {
        $jar = new CookieJar();

        foreach ($cookies as $cookieName => $cookieValue) {
            $newCookie = new SetCookie();
            $newCookie->setName($cookieName);
            $newCookie->setValue($cookieValue);
            $newCookie->setDomain($host);

            $jar->setCookie($newCookie);
        }

        return $jar;
    }
}
