# PHP HTTP-client

[![Latest Version][badge-release]][packagist]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
![Coverage Status][badge-coverage]
[![Total Downloads][badge-downloads]][downloads]
[![Support mail][badge-mail]][mail]

Библиотека HTTP-клиента для PHP 7.3+, которая упрощает отправку HTTP-запросов и интеграцию с веб-сервисами.

Возможности:

- Указание заголовков при запросе;
- Поддержка методов HTTP (GET, PUT, POST, DELETE, HEAD, PATCH и OPTIONS);
- Автоматическая сериализация тела запроса и ответа;
- Наличие middleware для запросов и ответов позволяет дополнять и формировать поведение HTTP-клиента;
- Поставляются middleware для реализации авторизации (ApiKey, Basic, Bearer);
- Имеет слой абстракции, позволяя писать код, не зависящий от среды и транспорта (нет жесткой привязки к cURL или потокам PHP);
- Поддержка cookies;
- Поддержка proxy (http и socks5 proxy).

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\Http\MimeInterface;
use Fi1a\Http\Uri;

$client = new HttpClient();

$uri = new Uri('https://httpbin.org/get');
$uri = $uri->withQueryParams(['foo' => 'bar']);

$response = $client->get($uri, MimeInterface::JSON);

$response->getStatusCode(); // 200
$response->getLastHeader('Content-Type')->getValue(); // application/json
$json = $response->getBody()->get();
$json['args']['foo']; // bar
```

## Установка

Установить этот пакет можно как зависимость, используя Composer.

``` bash
composer require fi1a/http-client
```

## Запросы с помощью HttpClient

Вы можете отправлять запросы, используя объект `Fi1a\HttpClient\HttpClientInterface`.

### Создание клиента

```php
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\HttpClient;

$client = new HttpClient(
    new Config([
        'sslVerify' => false,
        'timeout' => 2,
        'cookie' => false,
    ]),
    StreamHandler::class,
    new CookieStorage()
);
```

Конструктор клиента принимает:

- Объект настроек `Fi1a\HttpClient\ConfigInterface`;
- Класс обработчика запросов `Fi1a\HttpClient\Handlers\HandlerInterface`;
- Класс для хранения cookie `Fi1a\HttpClient\Cookie\CookieStorageInterface`.

Из этих аргументов обязательным являются только первые два (объект настроек и класс обработчика запросов).

Доступны два класса обработчиков запросов:

- `Fi1a\HttpClient\Handlers\StreamHandler` - на основе потоков PHP;
- `Fi1a\HttpClient\Handlers\CurlHandler` - на основе cURL.

### Отправка запросов

Методы упрощающие конфигурирование и отправку запроса:

| Метод                                                                                                            | Описание           |
|------------------------------------------------------------------------------------------------------------------|--------------------|
| get($uri, ?string $mime = null): ResponseInterface                                                               | HTTP Метод Get     |
| post($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null): ResponseInterface  | HTTP Метод Post    |
| put($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null): ResponseInterface   | HTTP Метод Put     |
| patch($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null): ResponseInterface | HTTP Метод Patch   |
| delete($uri, ?string $mime = null): ResponseInterface                                                            | HTTP Метод Delete  |
| head($uri): ResponseInterface                                                                                    | HTTP Метод Head    |
| options($uri): ResponseInterface                                                                                 | HTTP Метод Options |

```php
use Fi1a\HttpClient\HttpClient;

$client = new HttpClient();

$response = $client->get('https://httpbin.org/get');
$response = $client->post('https://httpbin.org/post');
$response = $client->put('https://httpbin.org/put');
$response = $client->delete('https://httpbin.org/delete');
$response = $client->head('https://httpbin.org/get');
$response = $client->patch('https://httpbin.org/patch');
$response = $client->options('https://httpbin.org/get');
```

## Объект настроек

Объект настроек `Fi1a\HttpClient\ConfigInterface` передается в качестве аргумента конструктору класса `Fi1a\HttpClient\HttpClientInterface`
и может содержать следующие опции:

- sslVerify (true) - проверка сертификата при https соединении;
- timeout (10) - таймаут запросов;
- compress (null) - если значение задано, то выставляется заголовок Accept-Encoding (доступное значение - "gzip");
- allowRedirects (true) - автоматическое следование перенаправлениям;
- maxRedirects (10) - максимальное число перенаправлений;
- cookie (false) - определяет использование cookie.

```php
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\HttpClient;

$client = new HttpClient(
    new Config([
        'sslVerify' => false,
        'timeout' => 2,
        'cookie' => false,
    ])
);
```

## Использование запроса

Объекты запросов `Fi1a\HttpClient\RequestInterface` обеспечивают большую гибкость в том как передается запрос, включая параметры запроса, middleware, cookie и т.п.

| Метод                                                                                         | Описание                                                                            |
|-----------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------|
| create()                                                                                      | Создать объект запроса                                                              |
| withMethod(string $method)                                                                    | Метод запроса                                                                       |
| getMethod(): string                                                                           | Возвращает метод запроса                                                            |
| get($uri, ?string $mime = null)                                                               | HTTP Метод Get                                                                      |
| post($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null)  | HTTP Метод Post                                                                     |
| put($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null)   | HTTP Метод Put                                                                      |
| patch($uri, $body = null, ?string $mime = null, ?UploadFileCollectionInterface $files = null) | HTTP Метод Patch                                                                    |
| delete($uri, ?string $mime = null)                                                            | HTTP Метод Delete                                                                   |
| head($uri)                                                                                    | HTTP Метод Head                                                                     |
| options($uri)                                                                                 | HTTP Метод Options                                                                  |
| getUri(): UriInterface                                                                        | Возвращает URI запроса                                                              |
| withUri(UriInterface $uri)                                                                    | Устанавливает URI запроса                                                           |
| withMime(?string $mime = null)                                                                | Устанавливаем Content type и Expected type                                          |
| withExpectedType(?string $mime = null)                                                        | Устанавливаем expected type                                                         |
| getExpectedType(): ?string                                                                    | Expected type                                                                       |
| withBody($body, ?string $mime = null, ?UploadFileCollectionInterface $files = null)           | Тело запроса                                                                        |
| getBody(): RequestBodyInterface                                                               | Возвращает тело запроса                                                             |
| withMiddleware(MiddlewareInterface $middleware, ?int $sort = null)                            | Добавить промежуточное ПО                                                           |
| getMiddlewares(): MiddlewareCollectionInterface                                               | Возвращает промежуточное ПО                                                         |
| withProxy(?ProxyInterface $proxy)                                                             | Использовать прокси для соединения                                                  |
| getProxy(): ?ProxyInterface                                                                   | Возвращает прокси                                                                   |
| getProtocolVersion(): string                                                                  | Возвращает версию протокола HTTP                                                    |
| withProtocolVersion(string $version)                                                          | Устанавливает версию протокола HTTP                                                 |
| getEncoding(): string                                                                         | Возвращает кодировку                                                                |
| withEncoding(string $encoding)                                                                | Устанавливает кодировку                                                             |
| getHeaders(): HeaderCollectionInterface                                                       | Возвращает коллекцию заголовков                                                     |
| withHeaders(HeaderCollectionInterface $headers)                                               | Устанавливает коллекцию заголовков                                                  |
| addHeader(HeaderInterface $header)                                                            | Добавить заголовок к коллекции                                                      |
| hasHeader(string $name): bool                                                                 | Проверяет наличие заголовка с определенным именем                                   |
| getHeader(string $name): HeaderCollectionInterface                                            | Возвращает заголовок с определенным именем                                          |
| getFirstHeader(string $name): ?HeaderInterface                                                | Возвращает первый найденный заголовок с определенным именем                         |
| getLastHeader(string $name): ?HeaderInterface                                                 | Возвращает последний найденный заголовок с определенным именем                      |
| withHeader(string $name, string $value)                                                       | Добавляет заголовок с определенным именем и значением                               |
| withAddedHeader(string $name, string $value): HeaderInterface                                 | Добавляет заголовок с определенным именем и значением и возвращает объект заголовка |
| withoutHeader(string $name): bool                                                             | Удаляет заголовок с определенным именем                                             |
| clearHeaders(): bool                                                                          | Удаляет все заголовки                                                               |
| getCookies(): CookieCollectionInterface                                                       | Возвращает коллекцию cookies                                                        |
| withCookies(CookieCollectionInterface $collection)                                            | Устанавливает коллекцию cookies                                                     |

Вы можете создать и сконфигурировать запрос, а затем отправить его:

```php
use Fi1a\Http\Uri;
use Fi1a\HttpClient\HttpClient;
use Fi1a\Http\HttpInterface;
use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\Request;

$client = new HttpClient();

$request = Request::create()
    ->withMethod(HttpInterface::GET)
    ->withUri(new Uri('https://httpbin.org/get'))
    ->withExpectedType(MimeInterface::JSON);

$response = $client->send($request);
```

### Тело запроса

Тело запроса релизованно классом `Fi1a\HttpClient\RequestBodyInterface` и имеет следующие методы:

| Метод                                                                                    | Описание                                                |
|------------------------------------------------------------------------------------------|---------------------------------------------------------|
| withBody($raw, ?string $mime = null, ?UploadFileCollectionInterface $files = null): void | Установить тело запроса                                 |
| get(): string                                                                            | Возвращает тело запроса                                 |
| getRaw()                                                                                 | Возвращает тело запроса без примененного преобразования |
| getSize(): int                                                                           | Возвращает размер тела запроса                          |
| has(): bool                                                                              | Есть тело запроса или нет                               |
| withUploadFiles(?UploadFileCollectionInterface $files)                                   | Прикрепить файлы к телу запроса                         |
| addUploadFile(string $name, FileInterface $file)                                         | Добавить загружаемый файл                               |
| getUploadFiles(): UploadFileCollectionInterface                                          | Возвращает прикрепленные файлы                          |
| getContentTypeHeader(): ?string                                                          | Content type для заголовков                             |

### Заголовки запроса

Для отправки заголовков вместе с запросом, можно использовать метод `withHeader`. Пример:

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Request;

$client = new HttpClient();

$request = Request::create()
    ->get('https://httpbin.org/headers')
    ->withHeader('X-Header', 'headerValue');

$response = $client->send($request);

$response->getStatusCode(); // 200
```

Также доступны другие методы связанные с заголовками у класса запроса `Fi1a\HttpClient\RequestInterface`:

| Метод                                                         | Описание                                                                            |
|---------------------------------------------------------------|-------------------------------------------------------------------------------------|
| getHeaders(): HeaderCollectionInterface                       | Возвращает коллекцию заголовков                                                     |
| withHeaders(HeaderCollectionInterface $headers)               | Устанавливает коллекцию заголовков                                                  |
| addHeader(HeaderInterface $header)                            | Добавить заголовок к коллекции                                                      |
| hasHeader(string $name): bool                                 | Проверяет наличие заголовка с определенным именем                                   |
| getHeader(string $name): HeaderCollectionInterface            | Возвращает заголовок с определенным именем                                          |
| getFirstHeader(string $name): ?HeaderInterface                | Возвращает первый найденный заголовок с определенным именем                         |
| getLastHeader(string $name): ?HeaderInterface                 | Возвращает последний найденный заголовок с определенным именем                      |
| withHeader(string $name, string $value)                       | Добавляет заголовок с определенным именем и значением                               |
| withAddedHeader(string $name, string $value): HeaderInterface | Добавляет заголовок с определенным именем и значением и возвращает объект заголовка |
| withoutHeader(string $name): bool                             | Удаляет заголовок с определенным именем                                             |
| clearHeaders(): bool                                          | Удаляет все заголовки                                                               |

## Использование ответа

Объект ответа реализует `Fi1a\HttpClient\ResponseInterface` и содержит информацию полученную в результате выполнения запроса.

Получить код состояния и фразу ответа:

```php
$code = $response->getStatusCode(); // 200
$reason = $response->getReasonPhrase(); // OK
```

Заголовки ответа:

```php
if ($response->hasHeader('Content-Length')) {
    $response->getLastHeader('Content-Length')->getValue(); // 233
}

$response->getHeaders()->join(PHP_EOL);
//Content-Type: application/json
//Content-Length: 233
//...
```

Методы класса `Fi1a\HttpClient\ResponseInterface`, реализующего ответ на запрос:

| Метод                                                         | Описание                                                                            |
|---------------------------------------------------------------|-------------------------------------------------------------------------------------|
| getStatusCode(): int                                          | Код статуса                                                                         |
| getReasonPhrase(): ?string                                    | Текст причины ассоциированный с кодом статуса                                       |
| withStatus(int $statusCode, string $reasonPhrase = '')        | Установить код статуса                                                              |
| hasErrors(): bool                                             | Запрос выполнен с ошибкой или нет                                                   |
| isSuccess(): bool                                             | Запрос выполнен успешно или нет                                                     |
| withBody(string $rawBody, ?string $mime = null)               | Установить тело ответа                                                              |
| getBody(): ResponseBodyInterface                              | Возвращает тело ответа                                                              |
| getProtocolVersion(): string                                  | Возвращает версию протокола HTTP                                                    |
| withProtocolVersion(string $version)                          | Устанавливает версию протокола HTTP                                                 |
| getEncoding(): string                                         | Возвращает кодировку                                                                |
| withEncoding(string $encoding)                                | Устанавливает кодировку                                                             |
| getHeaders(): HeaderCollectionInterface                       | Возвращает коллекцию заголовков                                                     |
| withHeaders(HeaderCollectionInterface $headers)               | Устанавливает коллекцию заголовков                                                  |
| addHeader(HeaderInterface $header)                            | Добавить заголовок к коллекции                                                      |
| hasHeader(string $name): bool                                 | Проверяет наличие заголовка с определенным именем                                   |
| getHeader(string $name): HeaderCollectionInterface            | Возвращает заголовок с определенным именем                                          |
| getFirstHeader(string $name): ?HeaderInterface                | Возвращает первый найденный заголовок с определенным именем                         |
| getLastHeader(string $name): ?HeaderInterface                 | Возвращает последний найденный заголовок с определенным именем                      |
| withHeader(string $name, string $value)                       | Добавляет заголовок с определенным именем и значением                               |
| withAddedHeader(string $name, string $value): HeaderInterface | Добавляет заголовок с определенным именем и значением и возвращает объект заголовка |
| withoutHeader(string $name): bool                             | Удаляет заголовок с определенным именем                                             |
| clearHeaders(): bool                                          | Удаляет все заголовки                                                               |
| getCookies(): CookieCollectionInterface                       | Возвращает коллекцию cookies                                                        |
| withCookies(CookieCollectionInterface $collection)            | Устанавливает коллекцию cookies                                                     |

Тело ответа можно получить с помощью метода getBody:

```php
use Fi1a\Http\Uri;
use Fi1a\HttpClient\HttpClient;
use Fi1a\Http\HttpInterface;
use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\Request;

$client = new HttpClient();

$request = Request::create()
    ->withMethod(HttpInterface::GET)
    ->withUri(new Uri('https://httpbin.org/get'))
    ->withExpectedType(MimeInterface::JSON);

$response = $client->send($request);

if ($response->getBody()->has()) {
    $response->getBody()->get(); // array
    $response->getBody()->getRaw(); // string
}
```

Тело ответа релизованно классом `Fi1a\HttpClient\ResponseBodyInterface` и имеет следующие методы:

| Метод                                             | Описание                                               |
|---------------------------------------------------|--------------------------------------------------------|
| withBody(string $raw, ?string $mime = null): void | Установить тело ответа                                 |
| get()                                             | Возвращает тело ответа                                 |
| getRaw(): string                                  | Возвращает тело ответа без примененного преобразования |
| has(): bool                                       | Есть тело ответа или нет                               |

## Кириллический домен

Кириллический домен — это название сайта на русском языке (например, домен.рф) в формате IDNA ASCII.
Преобразование доменного имени в формат IDNA ASCII осуществляется с помощью функции `idn_to_ascii`
входящей в модуль [интернационализации (Intl)](https://www.php.net/manual/ru/book.intl.php) php.
Поэтому для работы с кирилическими доменами необходим установленный этот модуль.

## URI и параметры GET запроса

Для конфигурирования адреса и параметров GET запроса нужно использовать класс `Fi1a\HttpClient\UriInterface`.
Задать параметры GET запроса можно несколькими способами.

В адресе запроса:

```php
use Fi1a\Http\Uri;

$uri = new Uri('https://httpbin.org/get?foo=bar&baz=qux');

$response = $client->get($uri);
```

Строкой с помощью метода `withQuery`:

```php
use Fi1a\Http\Uri;

$uri = new Uri('https://httpbin.org/get');
$uri = $uri->withQuery('foo=bar&baz=qux');

$response = $client->get($uri);
```

Массивом с помощью метода `withQueryParams`:

```php
use Fi1a\Http\Uri;

$uri = new Uri('https://httpbin.org/get');
$uri = $uri->withQueryParams([
    'foo' => 'bar',
    'baz' => 'qux',
]);

$response = $client->get($uri);
```

Доступные методы `Fi1a\HttpClient\UriInterface`:

| Метод                                                | Описание                                      |
|------------------------------------------------------|-----------------------------------------------|
| __construct(string $uri = '', array $variables = []) | Конструктор                                   |
| scheme(): string                                     | Схема                                         |
| withScheme(string $scheme)                           | Задать схему                                  |
| userInfo(): string                                   | Компонент информации о пользователе URI       |
| user(): string                                       | Возвращает имя пользователя                   |
| password(): ?string                                  | Возвращает пароль                             |
| withUserInfo(string $user, ?string $password = null) | Задать информацию о пользователе              |
| host(): string                                       | Хост                                          |
| withHost(string $host)                               | Задать хост                                   |
| port(): ?int                                         | Порт                                          |
| withPort(?int $port)                                 | Задать порт                                   |
| path(): string                                       | Часть пути URI                                |
| withPath(string $path)                               | Установить часть пути URI                     |
| query(): string                                      | Строка запроса в URI                          |
| withQuery(string $query)                             | Задать строку запроса URI                     |
| queryParams(): array                                 | Массив запроса в URI                          |
| withQueryParams(array $queryParams)                  | Задать массив запроса в URI                   |
| fragment(): string                                   | Фрагмент URI                                  |
| withFragment(string $fragment)                       | Задать фрагмент URI                           |
| url(): string                                        | Возвращает URL                                |
| uri(): string                                        | Возвращает URI                                |
| authority(): string                                  | Компонент полномочий URI                      |
| maskedUri(): string                                  | Возвращает URI с маской на данных авторизации |
| replace(string $uri = '', array $variables = [])     | Заменить адрес переданным значением           |

## Отправить POST/Form запрос

Для отправки POST-запросов application/x-www-form-urlencoded необходимо указать поля POST в виде массива в методе `post`.

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\Http\MimeInterface;

$client = new HttpClient();

$response = $client->post(
    'https://httpbin.org/post',
    [
        'foo' => 'bar',
    ],
    MimeInterface::FORM
);
```

## Отправить JSON строку в теле запроса

Для отправки данных в теле запроса следует задать значение в классе `Fi1a\HttpClient\RequestBodyInterface`.
Пример отправки JSON строки методом POST:

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\Request;

$client = new HttpClient();

$request = Request::create()->post('https://httpbin.org/post');
$request->getBody()->withBody(['foo' => 'bar'], MimeInterface::JSON);

$response = $client->send($request);
```

## Отправить файлы через POST-запрос

Для того чтобы отправить файлы через POST-запрос, следует передать коллекцию подготовленных файлов `Fi1a\HttpClient\UploadFileCollectionInterface`.
Для обеспечения абстракции файловой системы используются классы пакета [fi1a/filesystem](https://github.com/fi1a/filesystem)

Отправить файлы через POST-запрос:

```php
use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\Filesystem;
use Fi1a\HttpClient\HttpClient;
use Fi1a\Http\MimeInterface;
use Fi1a\HttpClient\UploadFile;
use Fi1a\HttpClient\UploadFileCollection;

$client = new HttpClient();

$filesystem = new Filesystem(new LocalAdapter(__DIR__));
$files = new UploadFileCollection();
$files[] = new UploadFile('fooFile', $filesystem->factoryFile('./fooFile.txt'));
$files[] = new UploadFile('barFile', $filesystem->factoryFile('./barFile.txt'));

$response = $client->post(
    'https://httpbin.org/post',
    [
        'foo' => 'bar',
    ],
    MimeInterface::UPLOAD,
    $files
);
```

## Cookies

Пакет поддерживает использования cookies.
Для использования cookies следует передать в конфигурации параметр `'cookie' => true`.

Пример с получением и установкой новой cookie приведен ниже:

```php
use Fi1a\Config\Parsers\JSONParser;
use Fi1a\Config\Readers\FileReader;
use Fi1a\Config\Writers\FileWriter;
use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\Filesystem;
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Cookie\ConfigCookieStorage;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Request;

$filesystem = new Filesystem(new LocalAdapter(__DIR__));
$file = $filesystem->factoryFile('./cookie.json');

$cookieStorage = new ConfigCookieStorage(
    new FileReader($file),
    new FileWriter($file),
    new JSONParser()
);

$client = new HttpClient(
    new Config([
        'cookie' => true,
    ]),
    StreamHandler::class,
    $cookieStorage
);

$response = $client->get('https://httpbin.org/cookies/set/cookieName1/cookieValue1');
$response->getCookies(); // Fi1a\HttpClient\Cookie\CookieCollection

// Установить новую cookie
$request = Request::create()
    ->get('https://httpbin.org/cookies');
$request->withCookie('cookieName2', 'cookieValue2');

$response = $client->send($request);
$response->getCookies(); // Fi1a\HttpClient\Cookie\CookieCollection
```

Доступны два варианта хранения cookies:

- `Fi1a\HttpClient\Cookie\CookieStorageInterface` - хранение cookies в рамках одной сессии (по умолчанию);
- `Fi1a\HttpClient\Cookie\ConfigCookieStorageInterface` - хранение cookies в конфигурационных файлах.

Для хранение cookies в конфигурационных файлах используются классы пакета [fi1a/config](https://github.com/fi1a/config).

Вы можете получить cookie по его имени с помощью метода `getByName(string $name)`,
который возвращает экземпляр `Fi1a\HttpClient\Cookie\CookieInterface`.

```php
$cookies = $response->getCookies(); // Fi1a\HttpClient\Cookie\CookieCollection
$cookie = $cookies->getByName('cookieName2');
$cookie->getValue(); // cookieValue2
```

Удаление cookie осуществляется из хранилища cookies методом `deleteCookie`. Пример:

```php
use Fi1a\Config\Parsers\JSONParser;
use Fi1a\Config\Readers\FileReader;
use Fi1a\Config\Writers\FileWriter;
use Fi1a\Filesystem\Adapters\LocalAdapter;
use Fi1a\Filesystem\Filesystem;
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Cookie\ConfigCookieStorage;
use Fi1a\HttpClient\Handlers\StreamHandler;
use Fi1a\HttpClient\HttpClient;

$filesystem = new Filesystem(new LocalAdapter(__DIR__));
$file = $filesystem->factoryFile('./cookie.json');

$cookieStorage = new ConfigCookieStorage(
    new FileReader($file),
    new FileWriter($file),
    new JSONParser()
);

$client = new HttpClient(
    new Config([
        'cookie' => true,
    ]),
    StreamHandler::class,
    $cookieStorage
);

$cookieStorage->deleteCookie('cookieName2');
```

Доступные методы `Fi1a\HttpClient\Cookie\CookieInterface`:

| Метод                             | Описание                            |
|-----------------------------------|-------------------------------------|
| getName(): ?string                | Возвращает имя                      |
| setName(?string $name)            | Устанавливает имя                   |
| getValue(): ?string               | Возвращает значение                 |
| setValue(?string $value)          | Устанавливает значение              |
| getDomain(): ?string              | Возвращает домен                    |
| setDomain(?string $domain)        | Устанавливает домен                 |
| getPath(): string                 | Возвращает путь                     |
| setPath(string $path)             | Устанавливает путь                  |
| getMaxAge(): ?int                 | Время жизни cookie в секундах       |
| setMaxAge(?int $maxAge)           | Время жизни cookie в секундах       |
| getExpires(): ?int                | UNIX timestamp когда cookie истечет |
| setExpires($timestamp)            | UNIX timestamp когда cookie истечет |
| isExpired(): bool                 | Истекла cookie или нет              |
| getSecure(): bool                 | Флаг secure                         |
| setSecure(bool $secure)           | Флаг secure                         |
| getHttpOnly(): bool               | Флаг HttpOnly                       |
| setHttpOnly(bool $httpOnly)       | Флаг HttpOnly                       |
| getSession(): bool                | Действует только на эту сессию      |
| setSession(bool $secure)          | Действует только на эту сессию      |
| matchDomain(string $domain): bool | Проверяет, соответствует ли домен   |
| matchPath(string $path): bool     | Проверяет, соответствует ли пути    |
| validate(): void                  | Валидация cookie                    |
| ::fromString(string $string)      | Создать cookie из строки            |

## Редиректы

Если опция `allowRedirects` в конфигурации выставлена в `true`, то будет осуществлено автоматическое следование перенаправлениям (по умолчанию true).
Также доступна опция `maxRedirects`, определяющая максимальное количество переходов по перенаправлениям (по умолчанию равно 10).

```php
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Handlers\Exceptions\ErrorException;

$client = new HttpClient(
    new Config([
        'allowRedirects' => true,
        'maxRedirects' => 6,
    ])
);

$response = $client->get('https://httpbin.org/redirect/5');
$response->getStatusCode(); // 200
```

Исключение при превышении установленного лимита на количество перенаправлений:

```php
try {
    $response = $client->get('https://httpbin.org/redirect/10');
} catch (ErrorException $exception) {
    echo $exception->getMessage(); // Максимальное число редиректов 6 было достигнуто
}
```

Автоматическое следование перенаправлениям отключено:

```php
$client->getConfig()->setAllowRedirects(false);
$response = $client->get('https://httpbin.org/redirect/5');
$response->getStatusCode(); // 302
```

## Прокси

Пакет предоставляет возможность использовать HTTP и Socks5 прокси при запросах. Установить прокси можно с помощью метода
`withProxy` класса `Fi1a\HttpClient\HttpClientInterface`. Данный метод принимает объект, реализующий интерфейс
`Fi1a\HttpClient\Proxy\ProxyInterface`.

- `Fi1a\HttpClient\Proxy\HttpProxy` - реализует HTTP прокси;
- `Fi1a\HttpClient\Proxy\Socks5Proxy` - реализует Socks5 прокси.

Пример использования HTTP прокси:

```php
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Handlers\CurlHandler;
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Proxy\HttpProxy;

$client = new HttpClient(new Config(), CurlHandler::class);
$client->withProxy(new HttpProxy('127.0.0.1', 50100, 'user1', 'password1'));

$response = $client->get('https://httpbin.org/get');
$response->getStatusCode(); // 200
```

Пример использования Socks5 прокси:

```php
use Fi1a\HttpClient\Config;
use Fi1a\HttpClient\Handlers\CurlHandler;
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Proxy\Socks5Proxy;

$client = new HttpClient(new Config(), CurlHandler::class);

$client->withProxy(new Socks5Proxy('127.0.0.1', 50101, 'user1', 'password1'));

$response = $client->get('https://httpbin.org/get');
$response->getStatusCode(); // 200
```

## Промежуточное ПО (middleware)

Промежуточное ПО (middleware) расширяет функциональные возможности. Они вызываются в процессе генерации запросов и ответов.
Промежуточное ПО (middleware) должно реализовывать интерфейс `Fi1a\HttpClient\Middlewares\MiddlewareInterface`.
В процессе генерации запроса вызывается метод `handleRequest` с параметрами
(RequestInterface $request, ResponseInterface $response, HttpClientInterface $httpClient), а ответа `handleResponse`
с параметрами (RequestInterface $request, ResponseInterface $response, HttpClientInterface $httpClient).

Для всех запросов можно добавить промежуточное ПО (middleware) с помощью метода `withMiddleware` класса `Fi1a\HttpClient\HttpClientInterface`.

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Middlewares\BasicAuthMiddleware;

$client = new HttpClient();

$client->withMiddleware(new BasicAuthMiddleware('user1', 'password1'));
$response = $client->get('https://httpbin.org/hidden-basic-auth/user1/password1');
$response->getStatusCode(); // 200
```

Для одного запроса можно добавить промежуточное ПО (middleware) с помощью метода `withMiddleware` класса запроса `Fi1a\HttpClient\RequestInterface`.

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Middlewares\BasicAuthMiddleware;
use Fi1a\HttpClient\Request;

$client = new HttpClient();

$request = Request::create()
    ->withMiddleware(new BasicAuthMiddleware('user1', 'password1'))
    ->get('https://httpbin.org/hidden-basic-auth/user1/password1');
$response = $client->send($request);

$response->getStatusCode(); // 200
```

### Авторизация по ключу (ApiKeyAuthMiddleware)

Данное промежуточное ПО (middleware) реализует авторизацию по ключу.
Ключ можно передать в заголовке или как GET параметр.

| Аргумент      | Описание                                                                                                            |
|---------------|---------------------------------------------------------------------------------------------------------------------|
| string $key   | Название ключа                                                                                                      |
| string $value | Значение ключа                                                                                                      |
| string $place | Где передать ключ (в заголовке ApiKeyAuthMiddleware::IN_HEADER или как GET параметр ApiKeyAuthMiddleware::IN_QUERY) |

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Middlewares\ApiKeyAuthMiddleware;

$client = new HttpClient();

$response = $client->withMiddleware(
    new ApiKeyAuthMiddleware('token', 'api-token', ApiKeyAuthMiddleware::IN_HEADER)
)->get('https://some-domain.ru/api-key-auth');

$response->getStatusCode(); // 200
```

### Basic авторизация (BasicAuthMiddleware)

Данное промежуточное ПО (middleware) реализует Basic авторизацию.

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Middlewares\BasicAuthMiddleware;

$client = new HttpClient();

$response = $client->withMiddleware(
    new BasicAuthMiddleware('user1', 'password1')
)->get('https://httpbin.org/hidden-basic-auth/user1/password1');

$response->getStatusCode(); // 200
```

### Bearer авторизация (BearerAuthMiddleware)

Данное промежуточное ПО (middleware) реализует Bearer авторизацию.

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Middlewares\BearerAuthMiddleware;

$client = new HttpClient();

$response = $client->withMiddleware(
    new BearerAuthMiddleware('token')
)->get('https://domain.ru/bearer-auth');

$response->getStatusCode(); // 200
```

### Повторная отправка запросов при ошибке (RetryMiddleware)

При статусе >= 400 осуществляет повторную отправку запроса.
Количество попыток повторной отправки запроса передается в конструкторе первым аргументом.

```php
use Fi1a\HttpClient\HttpClient;
use Fi1a\HttpClient\Middlewares\RetryMiddleware;

$client = new HttpClient();

$response = $client->withMiddleware(
    new RetryMiddleware(3)
)->get('https://httpbin.org/status/400');

$response->getStatusCode(); // 400
```

[badge-release]: https://img.shields.io/packagist/v/fi1a/http-client?label=release
[badge-license]: https://img.shields.io/github/license/fi1a/http-client?style=flat-square
[badge-php]: https://img.shields.io/packagist/php-v/fi1a/http-client?style=flat-square
[badge-coverage]: https://img.shields.io/badge/coverage-100%25-green
[badge-downloads]: https://img.shields.io/packagist/dt/fi1a/http-client.svg?style=flat-square&colorB=mediumvioletred
[badge-mail]: https://img.shields.io/badge/mail-support%40fi1a.ru-brightgreen

[packagist]: https://packagist.org/packages/fi1a/http-client
[license]: https://github.com/fi1a/http-client/blob/master/LICENSE
[php]: https://php.net
[downloads]: https://packagist.org/packages/fi1a/http-client
[mail]: mailto:support@fi1a.ru