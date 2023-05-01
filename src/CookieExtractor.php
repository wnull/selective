<?php

declare(strict_types=1);

namespace Wnull\CookieExtractor;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Cookie\SetCookie;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use ReflectionException;
use Wnull\CookieExtractor\Exception\EmptyCookiesException;
use Wnull\CookieExtractor\Helper\CookieAssistant;
use Wnull\CookieExtractor\Helper\Reflective;

use function array_filter;
use function in_array;
use function reset;

use const ARRAY_FILTER_USE_KEY;

final class CookieExtractor
{
    use CookieAssistant, Reflective;

    protected array $clientOptions;

    private array $neededCookies = [];
    private string $host = '';

    public function __construct(array $clientOptions = [])
    {
        $this->clientOptions = $clientOptions;
    }

    public function getNeededCookiesJar(): CookieJar
    {
        return $this->arrayCookiesToCookieJar($this->getNeededCookies(), $this->host);
    }

    public function getNeededCookies(): array
    {
        return $this->neededCookies;
    }

    /**
     * @throws ReflectionException
     * @throws ClientExceptionInterface
     */
    public function exclude(RequestInterface $request, Closure $closure): self
    {
        $this->reflectionIsBooleanReturnTypeClosure($closure);

        $cookieJar = $this->getCookieJarViaReflection($request);
        $client = new Client($this->clientOptions);

        if ($cookieJar->count() === 0) {
            throw new EmptyCookiesException('A logical error, there are no cookies to work with');
        }

        $cookies = $this->guzzleCookiesArrayNormalize($cookieJar->toArray());

        foreach ($cookies as $cookieName => $cookieValue) {
            unset($cookies[$cookieName]);

            $filtered = array_filter(
                $cookies,
                fn (string $key): bool => !in_array($key, $this->neededCookies, true),
                ARRAY_FILTER_USE_KEY
            );

            $cookiesForSend = $this->arrayCookiesToString($filtered);
            $response = $closure(
                $client->sendRequest(
                    $request->withHeader('cookie', $cookiesForSend)
                )
            );

            if ($response === false) {
                $cookies[$cookieName] = $cookieValue;
                $this->neededCookies[$cookieName] = $cookieValue;
            }
        }

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    private function getCookieJarViaReflection(RequestInterface &$request): CookieJar
    {
        if (
            isset($this->clientOptions['cookies'])
            && $this->clientOptions['cookies'] instanceof CookieJarInterface
        ) {
            $cookies = $this->clientOptions['cookies'];
            unset($this->clientOptions['cookies']);
        } else {
            $cookies = new CookieJar();
        }

        $requestCookies = $this->reflectionPropertyValue($request, 'headers');
        $this->host = $request->getUri()->getHost();

        if (!empty($requestCookies['cookie'])) {
            $cookieString = reset($requestCookies['cookie']);
            $this->mergeCookies($cookieString, $this->host, $cookies);
        }

        if (!empty($this->clientOptions['headers']['cookie'])) {
            $this->mergeCookies($this->clientOptions['headers']['cookie'], $this->host, $cookies);
        }

        $request = $request->withoutHeader('cookie');

        return $cookies;
    }

    private function mergeCookies(string $cookies, string $host, CookieJar $jar): void
    {
        foreach ($this->cookiesStringToArray($cookies) as $cookieName => $cookieValue) {
            $newCookie = new SetCookie();
            $newCookie->setName($cookieName);
            $newCookie->setValue($cookieValue);
            $newCookie->setDomain($host);

            $jar->setCookie($newCookie);
        }
    }
}
