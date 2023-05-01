# Cookie Extractor

A sifter of unnecessary cookies with a custom callback. 

## Installation

Via Composer:

```shell
$ composer require wnull/cookie-extractor
```

## Quickstart

Basic example

```php
$url = 'https://example.com/api/conversations';
$cookiesArray = [
    'key1' => 'value1',
    'key2' => 'value2',
];

$cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray($cookiesArray, 'example.com');

$request = new \GuzzleHttp\Psr7\Request('GET', $url, ['cookie' => 'variant=yes']);

$closure = static function (\Psr\Http\Message\ResponseInterface $response): bool {
    return $response->getStatusCode() === 200;
};

try {
    $extractor = new \Wnull\CookieExtractor\CookieExtractor(['cookies' => $cookieJar]);
    $exclude = $extractor->exclude($request, $closure);
    
    $cookiesAsArray = $exclude->getNeededCookies();
    print_r($cookiesAsArray);
    $cookiesAsJar = $exclude->getNeededCookiesJar();
    print_r($cookiesAsJar);
} catch (Throwable $exception) {
    echo $exception->getMessage();
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
