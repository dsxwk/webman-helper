<?php

declare(strict_types=1);

namespace Dsxwk\Framework\WebmanHelper\Console\Make;

use Dsxwk\Framework\WebmanHelper\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand('make:route', 'Make route')]
class MakeRouteCommand extends BaseCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Route name');
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
        $output->writeln("Make route $name");

        $name      = str_replace('\\', '/', $name);
        $route_str = app_path() . '/../router';

        $items = explode('/', $name);
        $name  = '';
        $end   = end($items);
        $path  = '';
        foreach ($items as $item) {
            if ($item !== $end) $path = $item . '\\';
            $name .= $item . '/';
        }
        $name = rtrim($name, '/');
        $path = rtrim($path, '\\');

        $file = $route_str . DIRECTORY_SEPARATOR . $name . '.php';

        if (is_file($file)) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion("$file already exists. Do you want to override it? (yes/no)", false);
            if (!$helper->ask($input, $output, $question)) {
                return self::SUCCESS;
            }
        }

        $this->createRoute($file);

        return self::SUCCESS;
    }

    /**
     * @param $file
     *
     * @return void
     */
    protected function createRoute($file): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $routeContent = <<<EOF
<?php

declare(strict_types=1);

use Webman\Route;



EOF;
        file_put_contents($file, $routeContent);
    }

}
