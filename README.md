# Cookie Extractor

A sifter of unnecessary cookies with a custom callback. 

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

$myCustomClosure = static function (\Psr\Http\Message\ResponseInterface $response): bool {
    return $response->getStatusCode() === 200;
};

try {
    $extractor = new \Wnull\CookieExtractor\CookieExtractor(['cookies' => $cookieJar]);

    $cookiesAsArray = $extractor->exclude($request, $myCustomClosure)->getNeededCookies();
    $cookiesAsJar = $extractor->exclude($request, $myCustomClosure)->getNeededCookiesJar();
    
    print_r($cookiesAsArray);
} catch (Throwable $exception) {
    echo $exception->getMessage(); exit();
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
