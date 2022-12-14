<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\Response;
use Fi1a\HttpClient\ResponseBodyInterface;
use Fi1a\HttpClient\ResponseInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Объект ответа
 */
class ResponseTest extends TestCase
{
    /**
     * Возвращает объект ответа
     */
    private function getResponse(): ResponseInterface
    {
        return new Response();
    }

    /**
     * Статус
     */
    public function testStatus(): void
    {
        $response = $this->getResponse();
        $response->withStatus(400, 'Bad Request');
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Bad Request', $response->getReasonPhrase());
    }

    /**
     * Исключение при отрицательном статусе
     */
    public function testStatusException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $response = $this->getResponse();
        $response->withStatus(-1, 'Bad Request');
    }

    /**
     * Наличие ошибки при начальном состоянии
     */
    public function testHasErrorsDefault(): void
    {
        $response = $this->getResponse();
        $this->assertFalse($response->hasErrors());
    }

    /**
     * Наличие ошибки
     */
    public function testHasErrors(): void
    {
        $response = $this->getResponse();
        $response->withStatus(400, 'Bad Request');
        $this->assertTrue($response->hasErrors());
    }

    /**
     * Отсутсвие ошибки
     */
    public function testNoHasErrors(): void
    {
        $response = $this->getResponse();
        $response->withStatus(200, 'OK');
        $this->assertFalse($response->hasErrors());
    }

    /**
     * Успешно выполнен
     */
    public function testIsSuccess(): void
    {
        $response = $this->getResponse();
        $response->withStatus(200, 'OK');
        $this->assertTrue($response->isSuccess());
    }

    /**
     * Успешно выполнен
     */
    public function testIsNotSuccess(): void
    {
        $response = $this->getResponse();
        $response->withStatus(400, 'Bad Request');
        $this->assertFalse($response->isSuccess());
    }

    /**
     * Значение тела ответа по умолчанию
     */
    public function testDefaultBody(): void
    {
        $response = $this->getResponse();
        $this->assertInstanceOf(ResponseBodyInterface::class, $response->getBody());
        $this->assertEquals(null, $response->getBody()->get());
        $this->assertEquals('', $response->getBody()->getRaw());
    }

    /**
     * Значение тела ответа
     */
    public function testBody(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $response = $this->getResponse();
        $response->withBody($json, 'json');
        $this->assertEquals(MimeInterface::JSON, $response->getBody()->getContentType());
        $this->assertEquals($json, $response->getBody()->getRaw());
        $this->assertEquals($array, $response->getBody()->get());
    }
}
