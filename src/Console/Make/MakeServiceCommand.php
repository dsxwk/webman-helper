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

#[AsCommand('make:service', 'Make service')]
class MakeServiceCommand extends BaseCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Service name');
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
        $output->writeln("Make service $name");
        $suffix = config('app.service_suffix', '');

        if ($suffix && !strpos($name, $suffix)) {
            $name .= $suffix;
        }

        $name        = str_replace('\\', '/', $name);
        $service_str = Util::guessPath(app_path(), 'service');

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

        $file  = app_path() . DIRECTORY_SEPARATOR . $service_str . DIRECTORY_SEPARATOR . $name . '.php';
        $upper = $service_str === 'Service';
        if (empty($path)) {
            $namespace = $upper ? 'App\Service' : 'app\service';
        } else {
            $namespace = $upper ? 'App\Service' . '\\' . $path : 'app\service' . '\\' . $path;
        }

        if (is_file($file)) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion("$file already exists. Do you want to override it? (yes/no)", false);
            if (!$helper->ask($input, $output, $question)) {
                return self::SUCCESS;
            }
        }

        $this->createService(ucfirst($end), $namespace, $file);

        return self::SUCCESS;
    }

    /**
     * @param $name
     * @param $namespace
     * @param $file
     *
     * @return void
     */
    protected function createService($name, $namespace, $file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $serviceContent = <<<EOF
<?php

declare(strict_types=1);

namespace $namespace;

class $name
{
    public function index(): array
    {
        return [];
    }
}

EOF;
        file_put_contents($file, $serviceContent);
    }

}
