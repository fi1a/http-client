<?php

declare(strict_types=1);

namespace Fi1a\HttpClient\Cookie;

use Fi1a\Config\Config;
use Fi1a\Config\Exceptions\ReaderException;
use Fi1a\Config\Parsers\ParserInterface;
use Fi1a\Config\Readers\ReaderInterface;
use Fi1a\Config\Writers\WriterInterface;

/**
 * Хранилище кук в конфиге
 */
class ConfigCookieStorage extends CookieStorage implements ConfigCookieStorageInterface
{
    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(ReaderInterface $reader, WriterInterface $writer, ParserInterface $parser)
    {
        parent::__construct();
        $this->reader = $reader;
        $this->writer = $writer;
        $this->parser = $parser;

        $this->load();
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * @inheritDoc
     */
    public function load(): bool
    {
        try {
            $config = Config::load($this->reader, $this->parser);
            $this->cookies->exchangeArray([]);
            /**
             * @var mixed[] $data
             */
            foreach ($config as $data) {
                $this->cookies[] = $data;
            }
        } catch (ReaderException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        if (!$this->cookies->count()) {
            return false;
        }

        return Config::write(
            Config::create($this->cookies->toArray()),
            $this->writer,
            $this->parser
        );
    }
}
