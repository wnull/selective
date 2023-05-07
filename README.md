# Cookie Extractor

A sifter of unnecessary cookies with a custom callback. 

## Installation

Via Composer:

```shell
$ composer require wnull/selective
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

$stepClosure = static function (\Wnull\Selective\ValueObject\CookieStepIterate $stepIterate): void {
    echo sprintf(
        'cookie [%s] is %s' . PHP_EOL,
        $stepIterate->getCookieName(),
        $stepIterate->isNeeded() ? 'need' : 'trash'
    );
};

try {
    $extractor = new \Wnull\Selective\Selective(['cookies' => $cookieJar]);
    $exclude = $extractor->exclude($request, $closure, $stepClosure);
    
    $cookiesAsArray = $exclude->getNeededCookies();
    print_r($cookiesAsArray);
    $cookiesAsJar = $exclude->getNeededCookieJar();
    print_r($cookiesAsJar);
} catch (Throwable $exception) {
    echo $exception->getMessage();
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
