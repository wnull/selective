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

use function reset;

final class CookieExtractor
{
    use CookieAssistant, Reflective;

    protected array $clientOptions;

    public function __construct(array $clientOptions = [])
    {
        $this->clientOptions = $clientOptions;
    }

    /**
     * @throws ReflectionException
     * @throws ClientExceptionInterface
     */
    public function exclude(RequestInterface $request, Closure $closure): void
    {
        $this->reflectionIsBooleanReturnTypeClosure($closure);

        $cookieJar = $this->getCookieJarViaReflection($request);
        $client = new Client($this->clientOptions);

        if ($cookieJar->count() === 0) {
            throw new EmptyCookiesException('A logical error, there are no cookies to work with');
        }

        $cookies = $this->guzzleCookiesArrayNormalize($cookieJar->toArray());

        // TODO: Implement the exclusion of unnecessary cookies.

        /*
            $response = $closure(
                $client->send(
                    $request->withHeader('cookie', $this->arrayCookiesToString($cookies)),
                    $this->clientOptions,
                )
            );
        */
    }

    /**
     * @throws ReflectionException
     */
    private function getCookieJarViaReflection(RequestInterface &$request): CookieJar
    {
        $cookies = new CookieJar();

        if (
            isset($this->clientOptions['cookies'])
            && $this->clientOptions['cookies'] instanceof CookieJarInterface
        ) {
            $cookies = $this->clientOptions['cookies'];
            unset($this->clientOptions['cookies']);
        }

        $requestCookies = $this->reflectionPropertyValue($request, 'headers');
        $host = $requestCookies['Host'][0] ?? '';

        if (!empty($requestCookies['cookie'])) {
            $cookieString = reset($requestCookies['cookie']);
            $this->mergeCookies($cookieString, $host, $cookies);
        }

        if (!empty($this->clientOptions['headers']['cookie'])) {
            $this->mergeCookies($this->clientOptions['headers']['cookie'], $host, $cookies);
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
