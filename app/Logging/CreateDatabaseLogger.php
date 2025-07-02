<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\PDOHandler;
use Illuminate\Support\Facades\DB;

class CreateDatabaseLogger
{
    /**
     * This will be called by the “via” key in config/logging.php
     */
    public function __invoke(array $config)
    {
        // get the PDO instance
        $pdo = DB::connection($config['connection'] ?? null)->getPdo();

        // level is a string like 'debug' — Monolog needs a numeric level
        $level = Logger::toMonologLevel($config['level'] ?? 'debug');

        // handler: writes into `logs` table
        $handler = new PDOHandler(
            $pdo,
            $config['table']   ?? 'logs',
            $level,
            $config['bubble']  ?? true,
            $config['column_map'] ?? [
                'level'   => 'level',
                'message' => 'message',
                'context' => 'context',
            ]
        );
        

        $logger = new Logger('database');
        $logger->pushHandler($handler);

        return $logger;
    }
}
