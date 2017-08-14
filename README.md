# url-status
Passing the url returns the status by looking at the header information

Inspired by [https://secure.php.net/manual/ja/function.get-headers.php#119497](https://secure.php.net/manual/ja/function.get-headers.php#119497 "PHP: get_headers - Manual")

## Installation

```
$ composer require shimabox/url-status
```

## Usage

```php
<?php
require_once 'vendor/autoload.php';

$ret = \SMB\UrlStatus::get($url);

$ret->targetUrl();      // string 対象のURL
$ret->reachedUrl();     // string 最後に到達したURL
$ret->isValidUrl();     // bool   有効なURLか
$ret->redirectedUrls(); // array  リダイレクトがあった際のURL
$ret->redirectedCode(); // array  リダイレクト時のHTTPステータスコード
$ret->code();           // int    最終的なHTTPステータスコード

// is{数値3桁}() でマジックメソッドが呼ばれます
$ret->is200();          // bool   HTTPステータスコードが200かどうか
$ret->is401();          // bool   HTTPステータスコードが401かどうか
$ret->is403();          // bool   HTTPステータスコードが403かどうか
$ret->is404();          // bool   HTTPステータスコードが404かどうか
$ret->is405();          // bool   HTTPステータスコードが405かどうか
$ret->is500();          // bool   HTTPステータスコードが500かどうか
$ret->is503();          // bool   HTTPステータスコードが503かどうか
.
.
$ret->isxxx();          // /\Ais(\d{3})\z/ will call the magic method
```

## Example

```php
<?php
require_once 'vendor/autoload.php';

$ret = \SMB\UrlStatus::get('https://google.com/webhp?gl=us&hl=en&gws_rd=cr');

$ret->targetUrl();      // => 'https://google.com/webhp?gl=us&hl=en&gws_rd=cr'
$ret->reachedUrl();     // => 'https://www.google.com/webhp?gl=us&hl=en&gws_rd=cr'
$ret->isValidUrl();     // => true
$ret->redirectedUrls(); // => ['https://www.google.com/webhp?gl=us&hl=en&gws_rd=cr']
$ret->redirectedCode(); // => [301]
$ret->code();           // => 200
$ret->is200();          // => true

// for the POST method
$data = http_build_query(['foo' => 'bar', 'hoge' => 'piyo']);
$header = [
    'Content-Type: application/x-www-form-urlencoded',
    'Content-Length: ' . strlen($data)
];
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => implode("\r\n", $header),
        'content' => $data
    ]
];

$ret = \SMB\UrlStatus::get('http://localhost/post.php', $options);
```

## Testing

```
$ vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
