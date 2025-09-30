<?php

declare(strict_types=1);

namespace Dsxwk\Framework\WebmanHelper\Console\Make;

use Dsxwk\Framework\WebmanHelper\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Webman\Console\Util;

#[AsCommand('make:request', 'Make request')]
class MakeRequestCommand extends BaseCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Request name');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Make request $name");
        $suffix = config('app.request_suffix', '');

        if ($suffix && !strpos($name, $suffix)) {
            $name .= $suffix;
        }

        $name        = str_replace('\\', '/', $name);
        $request_str = Util::guessPath(app_path(), 'request');

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

        $file  = app_path() . DIRECTORY_SEPARATOR . $request_str . DIRECTORY_SEPARATOR . $name . '.php';
        $upper = $request_str === 'Request';
        if (empty($path)) {
            $namespace = $upper ? 'App\Request' : 'app\request';
        } else {
            $namespace = $upper ? 'App\Request' . '\\' . $path : 'app\request' . '\\' . $path;
        }

        if (is_file($file)) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion("$file already exists. Do you want to override it? (yes/no)", false);
            if (!$helper->ask($input, $output, $question)) {
                return self::SUCCESS;
            }
        }

        $this->createRequest(ucfirst($end), $namespace, $file);

        return self::SUCCESS;
    }

    /**
     * @param $name
     * @param $namespace
     * @param $file
     *
     * @return void
     */
    protected function createRequest($name, $namespace, $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $requestContent = <<<EOF
<?php

declare(strict_types=1);

namespace $namespace;

use Dsxwk\Framework\WebmanHelper\Validate\Think\BaseRequest;

class $name extends BaseRequest
{
    /**
     * 自定义验证场景规则
     *
     * @return array
     */
    protected function sceneRules(): array
    {
        return [
            'create' => [],
        ];
    }

    /**
     * 验证字段描述
     *
     * @var array
     */
    protected \$field   = [];

    /**
     * 验证提示信息
     *
     * @var array
     */
    protected \$message = [];
}

EOF;
        file_put_contents($file, $requestContent);
    }

}
