<?php

declare(strict_types=1);

namespace Dsxwk\Framework\WebmanHelper\Console\Make;

use Dsxwk\Framework\WebmanHelper\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Webman\Console\Util;

#[AsCommand('make:param', 'Make param')]
class MakeParamCommand extends BaseCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Param name');
        $this->addOption('camel', 'c', InputOption::VALUE_NONE, 'Camel case');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name  = $input->getArgument('name');
        $camel = $input->getOption('camel');
        $output->writeln("Make param $name");
        $suffix = config('app.param_suffix', '');

        if ($suffix && !strpos($name, $suffix)) {
            $name .= $suffix;
        }

        $name      = str_replace('\\', '/', $name);
        $param_str = Util::guessPath(app_path(), 'param');

        $items = explode('/', $name);
        $name  = '';
        $end   = end($items);
        $path  = '';
        foreach ($items as $item) {
            if ($item === $end) {
                $item = ucfirst($item);
            } else {
                $path = $item . '\\';
            }
            $name .= $item . '/';
        }
        $name = rtrim($name, '/');
        $path = rtrim($path, '\\');

        $file  = app_path() . DIRECTORY_SEPARATOR . $param_str . DIRECTORY_SEPARATOR . $name . '.php';
        $upper = $param_str === 'Param';
        if (empty($path)) {
            $namespace = $upper ? 'App\Param' : 'app\param';
        } else {
            $namespace = $upper ? 'App\Param' . '\\' . $path : 'app\param' . '\\' . $path;
        }

        $table      = lcfirst(str_replace('Param', '', $end));
        $database   = config(sprintf('database.connections.%s.database', config('database.default')));
        $properties = '';
        $format     = '    ';
        // MySQL 列信息
        foreach ($this->db::connection()->select(
            "select COLUMN_NAME,DATA_TYPE,COLUMN_KEY,COLUMN_COMMENT from INFORMATION_SCHEMA.COLUMNS where table_name = '$table' and table_schema = '$database' ORDER BY ordinal_position"
        ) as $item) {
            if ($item->COLUMN_KEY !== 'PRI') {
                $type = $this->getType($item->DATA_TYPE) === 'integer' ? 'int' : $this->getType($item->DATA_TYPE);
                // 是否转换成驼峰参数
                if ($camel) {
                    $item->COLUMN_NAME = lcfirst(toCamelCase($item->COLUMN_NAME));
                }
                $properties .= "{$format}/**\n{$format} * {$item->COLUMN_COMMENT}\n{$format} * \n{$format} * @var {$type}\n{$format} */\n{$format}public {$type} \${$item->COLUMN_NAME};\n\n";
            }
        }

        if (is_file($file)) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion("$file already exists. Do you want to override it? (yes/no)", false);
            if (!$helper->ask($input, $output, $question)) {
                return self::SUCCESS;
            }
        }

        $this->createParam(ucfirst($end), $namespace, rtrim($properties, "\n"), $file);

        return self::SUCCESS;
    }

    /**
     * @param $name
     * @param $namespace
     * @param $properties
     * @param $file
     *
     * @return void
     */
    protected function createParam($name, $namespace, $properties, $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $paramContent = <<<EOF
<?php

declare(strict_types=1);

namespace $namespace;

use Dsxwk\Framework\Utils\Param\BaseParam;

class $name extends BaseParam
{
$properties
}

EOF;
        file_put_contents($file, $paramContent);
    }

}
