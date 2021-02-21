<?php


namespace App\Command;

use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Mysqli\Client;
use EasySwoole\Mysqli\Config;
use EasySwoole\Command\CommandManager;

abstract class Base implements CommandInterface
{
    /** @var Client $mysqliClient */
    protected $mysqliClient;

    protected $config = [
        'host'          => '127.0.0.1',
        'port'          => 3306,
        'user'          => 'root',
        'password'      => '0987abc123',
        'database'      => 'new_central',
    ];

    public function __construct(array $config = [])
    {
        $config = new Config(array_merge($this->config, $config));
        $this->mysqliClient = new Client($config);
    }

    public function commandName(): string
    {
        $arr = explode("\\", static::class);
        return strtolower(end($arr));
    }

    protected function CommandManager()
    {
        return CommandManager::getInstance();
    }
}