<?php

declare(strict_types=1);

namespace Dsxwk\Framework\WebmanHelper\Console;

use Dotenv\Dotenv;
use Webman\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class  BaseCommand extends Command
{
    protected OutputInterface $output;

    /**
     * 初始化
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;

        // 加载 .env（支持加载失败不致命）
        if (file_exists(base_path('.env'))) {
            Dotenv::createImmutable(base_path())->safeLoad();
        }

        // 加载配置
        Config::load(config_path());

        $this->log("Environment initialized.");
    }

    /**
     * 统一日志输出
     *
     * @param string $message
     *
     * @return void
     */
    protected function log(string $message): void
    {
        $this->output->writeln("[LOG] " . $message);
    }

    /**
     * 错误日志输出
     *
     * @param string $message
     *
     * @return void
     */
    protected function error(string $message): void
    {
        $this->output->writeln("<error>[ERROR] $message</error>");
    }

    /**
     * 获取数据库字段类型
     *
     * @param string $type
     *
     * @return string
     */
    protected function getType(string $type): string
    {
        if (str_contains($type, 'int')) {
            return 'integer';
        }

        if (str_contains($type, 'character varying') || str_contains($type, 'varchar')) {
            return 'string';
        }

        if (str_contains($type, 'timestamp')) {
            return 'string';
        }

        switch ($type) {
            case 'varchar':
            case 'string':
            case 'text':
            case 'date':
                return 'string|null';
            case 'time':
            case 'guid':
            case 'datetimetz':
            case 'datetime':
                return 'string|null';
            case 'decimal':
                return 'float';
            case 'enum':
            case 'character':   // PostgreSQL类型
            case 'char':        // PostgreSQL类型
                return 'string';
            case 'json':        // PostgreSQL类型
                return 'mixed';
            case 'jsonb':       // PostgreSQL类型
            case 'uuid':        // PostgreSQL类型
            case 'timestamptz': // PostgreSQL类型
            case 'citext':      // PostgreSQL类型
                return 'string';
            case 'boolean':
            case 'bool':        // PostgreSQL类型
                return 'integer';
            case 'float':
                return 'float';
            case 'float4':      // PostgreSQL类型 (real)
                return 'float';
            case 'float8':      // PostgreSQL类型 (double precision)
                return 'float';
            case 'numeric':     // PostgreSQL类型
                return 'string';
            default:
                return 'mixed';
        }
    }
}