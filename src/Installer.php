<?php
namespace Dsxwk\Framework\WebmanHelper;

class Installer
{
    public static function install(): void
    {
        $projectRoot = getcwd();
        $target = $projectRoot . '/config/plugin/webman/console/command.php';
        if (!file_exists($target)) {
            $source = __DIR__ . '/Console/Resource/command.php';
            @mkdir(dirname($target), 0777, true);
            copy($source, $target);
            echo "Published config file: config/plugin/webman/console/command.php\n";
        }
    }
}