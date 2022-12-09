<?php

declare(strict_types=1);

use Fi1a\HttpClient\ContentTypeParsers\JsonContentTypeParser;
use Fi1a\HttpClient\ParserRegistry;

ParserRegistry::add('json', JsonContentTypeParser::class);
