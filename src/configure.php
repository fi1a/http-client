<?php

declare(strict_types=1);

use Fi1a\HttpClient\ContentTypeParsers\FormContentTypeParser;
use Fi1a\HttpClient\ContentTypeParsers\JsonContentTypeParser;
use Fi1a\HttpClient\ParserRegistry;

ParserRegistry::add('json', JsonContentTypeParser::class);
ParserRegistry::add('form', FormContentTypeParser::class);
