<?php

declare(strict_types=1);

namespace Dsxwk\Framework\WebmanHelper;

class Install
{
    /**
     * 标识为 Webman 插件
     */
    const WEBMAN_PLUGIN = true;

    /**
     * 如果插件需要额外复制文件，可以在这里配置
     * @var array
     */
    protected static array $pathRelation = [];

    /**
     * 插件安装
     * @return void
     */
    public static function install(): void
    {
        $command_file = config_path('/plugin/webman/console/command.php');
        if (!is_file($command_file)) {
            echo 'Create config/plugin/webman/console/command.php' . PHP_EOL;
            copy(__DIR__ . '/config/plugin/webman/console/command.php', $command_file);
        }
        static::installByRelation();
    }

    /**
     * 插件卸载
     * @return void
     */
    public static function uninstall(): void
    {
        $command_file = config_path('/plugin/webman/console/command.php');
        if (is_file($command_file)) {
            echo 'Remove config/plugin/webman/console/command.php' . PHP_EOL;
            unlink($command_file);
        }
        self::uninstallByRelation();
    }

    /**
     * 根据 $pathRelation 安装文件/目录
     * @return void
     */
    public static function installByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path().'/'.substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            copy_dir(__DIR__ . "/$source", base_path()."/$dest");
        }
    }

    /**
     * 根据 $pathRelation 卸载文件/目录
     * @return void
     */
    public static function uninstallByRelation(): void
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path()."/$dest";
            if (!is_dir($path) && !is_file($path)) {
                continue;
            }
            remove_dir($path);
        }
    }
}