<?php


namespace App\Hander;

use EasySwoole\Log\LoggerInterface;
use EasySwoole\Utility\File;

/**
 * 日志处理器
 * @author Joyboo
 * @date 2021-01-26
 * Class LogHandel
 * @package App\Logger
 */
class LogHander implements LoggerInterface
{
    private $logDir;

    function __construct(string $logDir = null)
    {
        if(empty($logDir)){
            $logDir = getLogDirByStamp();
        }
        $this->logDir = $logDir;
    }

    function log(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'debug'):string
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);

        $file = date('d') . ($category ? "_{$category}" : '') . '.log';
        $filePath = $this->logDir . $file;
        File::touchFile($filePath, false);
        $str = "[{$date}][{$levelStr}] : {$msg}\n";
        file_put_contents($filePath,"{$str}",FILE_APPEND|LOCK_EX);
        return $str;
    }

    function console(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'console')
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $temp = "[{$date}][{$levelStr}]: {$msg}\n";
        fwrite(STDOUT, $temp);
    }

    private function levelMap(int $level)
    {
        switch ($level)
        {
            case self::LOG_LEVEL_INFO:
                return 'info';
            case self::LOG_LEVEL_NOTICE:
                return 'notice';
            case self::LOG_LEVEL_WARNING:
                return 'warning';
            case self::LOG_LEVEL_ERROR:
                return 'error';
            default:
                return 'unknown';
        }
    }
}