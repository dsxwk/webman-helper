<?php

declare(strict_types=1);

use Dsxwk\Framework\WebmanHelper\Console\Make\MakeCommandCommand;
use Dsxwk\Framework\WebmanHelper\Console\Make\MakeControllerCommand;
use Dsxwk\Framework\WebmanHelper\Console\Make\MakeModelCommand;
use Dsxwk\Framework\WebmanHelper\Console\Make\MakeParamCommand;
use Dsxwk\Framework\WebmanHelper\Console\Make\MakeRequestCommand;
use Dsxwk\Framework\WebmanHelper\Console\Make\MakeRouteCommand;
use Dsxwk\Framework\WebmanHelper\Console\Make\MakeServiceCommand;

return [
    MakeCommandCommand::class,
    MakeControllerCommand::class,
    MakeModelCommand::class,
    MakeParamCommand::class,
    MakeRequestCommand::class,
    MakeRouteCommand::class,
    MakeServiceCommand::class,
];