<?php

declare(strict_types=1);

namespace Dsxwk\Framework\WebmanHelper\Console\Make;

use Dsxwk\Framework\WebmanHelper\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Webman\Console\Util;

#[AsCommand('make:controller', 'Make controller')]
class MakeControllerCommand extends BaseCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Controller name');
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
        $output->writeln("Make controller $name");
        $suffix = config('app.controller_suffix', '');

        if ($suffix && !strpos($name, $suffix)) {
            $name .= $suffix;
        }

        $name           = str_replace('\\', '/', $name);
        $controller_str = Util::guessPath(app_path(), 'controller');

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

        $file  = app_path() . DIRECTORY_SEPARATOR . $controller_str . DIRECTORY_SEPARATOR . "$name.php";
        $upper = $controller_str === 'Controller';
        if (empty($path)) {
            $namespace = $upper ? 'App\Controller' : 'app\controller';
        } else {
            $namespace = $upper ? 'App\Controller' . '\\' . $path : 'app\controller' . '\\' . $path;
        }

        if (is_file($file)) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion("$file already exists. Do you want to override it? (yes/no)", false);
            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }
        }

        $this->createController(ucfirst($end), $namespace, $file);

        return self::SUCCESS;
    }

    /**
     * @param $name
     * @param $namespace
     * @param $file
     *
     * @return void
     */
    protected function createController($name, $namespace, $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $controllerContent = <<<EOF
<?php

declare(strict_types=1);

namespace $namespace;

use support\Request;
use support\Response;

class $name
{
    public function index(Request \$request): Response
    {
        return apiResponse();
    }
}

EOF;
        file_put_contents($file, $controllerContent);
    }

}
