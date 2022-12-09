<?php

declare(strict_types=1);

namespace Fi1a\Unit\HttpClient;

use Fi1a\HttpClient\MimeInterface;
use Fi1a\HttpClient\Response;
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
     * Тестирование тела ответа
     */
    public function testWithBody(): void
    {
        $array = ['foo' => 'bar'];
        $json = json_encode($array);

        $response = $this->getResponse();
        $response->withBody($json, 'json');
        $this->assertEquals(MimeInterface::JSON, $response->getContentType());
        $this->assertEquals($json, $response->getRawBody());
        $this->assertEquals($array, $response->getBody());
    }

    /**
     * Тестирование тела ответа при пустом типе контента
     */
    public function testWithBodyWithoutContentType(): void
    {
        $content = 'content';
        $response = $this->getResponse();
        $response->withBody($content);
        $this->assertEquals($content, $response->getRawBody());
        $this->assertEquals($content, $response->getBody());
    }

    /**
     * Тестирование тела ответа при пустом теле запроса
     */
    public function testWithBodyEmptyContent(): void
    {
        $content = '';
        $response = $this->getResponse();
        $response->withBody($content);
        $this->assertEquals($content, $response->getRawBody());
        $this->assertEquals($content, $response->getBody());
    }

    /**
     * Наличие тела ответа по умолчанию
     */
    public function testHasBodyDefault(): void
    {
        $response = $this->getResponse();
        $this->assertFalse($response->hasBody());
    }

    /**
     * Наличие тела ответа
     */
    public function testHasBody(): void
    {
        $response = $this->getResponse();
        $response->withBody('content');
        $this->assertTrue($response->hasBody());
    }

    /**
     * Наличие тела ответа
     */
    public function testHasBodyEmptyString(): void
    {
        $response = $this->getResponse();
        $response->withBody('');
        $this->assertFalse($response->hasBody());
    }
}
