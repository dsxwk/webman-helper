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

#[AsCommand('make:command', 'Make command')]
class MakeCommandCommand extends BaseCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Command name');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $name = trim($input->getArgument('name'));
        $output->writeln("Make command $name");

        $name        = str_replace('\\', '/', $name);
        $command_str = Util::guessPath(app_path(), 'command');

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

        $file  = app_path() . DIRECTORY_SEPARATOR . $command_str . DIRECTORY_SEPARATOR . "$name.php";
        $upper = $command_str === 'Command';
        if (empty($path)) {
            $namespace = $upper ? 'App\Command' : 'app\command';
        } else {
            $namespace = $upper ? 'App\Command' . '\\' . $path : 'app\command' . '\\' . $path;
        }

        if (is_file($file)) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion("$file already exists. Do you want to override it? (yes/no)", false);
            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }
        }

        $this->createCommand(ucfirst($end), $namespace, $file, $end);

        return self::SUCCESS;
    }

    protected function getClassName($name): string
    {
        return preg_replace_callback(
                   '/:([a-zA-Z])/',
                   function ($matches) {
                       return strtoupper($matches[1]);
                   },
                   ucfirst($name)
               ) . 'Command';
    }

    /**
     * @param $name
     * @param $namespace
     * @param $file
     * @param $command
     *
     * @return void
     */
    protected function createCommand($name, $namespace, $file, $command): void
    {
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $desc           = str_replace(':', ' ', $command);
        $commandContent = <<<EOF
<?php

declare(strict_types=1);

namespace $namespace;

use Dsxwk\Framework\WebmanHelper\Console\BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('$command', '$desc')]
class $name extends BaseCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        \$this->addArgument('name', InputArgument::OPTIONAL, 'Name description');
    }

    /**
     * @param InputInterface \$input
     * @param OutputInterface \$output
     * @return int
     */
    protected function execute(InputInterface \$input, OutputInterface \$output): int
    {
        \$name = \$input->getArgument('name');
        \$output->writeln('Hello $command');
        return self::SUCCESS;
    }

}

EOF;
        file_put_contents($file, $commandContent);
    }

}
