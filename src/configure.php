<?php

declare(strict_types=1);

use Fi1a\HttpClient\ContentTypeEncodeRegistry;
use Fi1a\HttpClient\ContentTypeEncodes\FormContentTypeEncode;
use Fi1a\HttpClient\ContentTypeEncodes\JsonContentTypeEncode;
use Fi1a\HttpClient\ContentTypeEncodes\MultipartFormDataContentTypeEncode;

ContentTypeEncodeRegistry::add('json', JsonContentTypeEncode::class);
ContentTypeEncodeRegistry::add('form', FormContentTypeEncode::class);
ContentTypeEncodeRegistry::add('upload', MultipartFormDataContentTypeEncode::class);
