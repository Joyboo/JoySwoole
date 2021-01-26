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
            $logDir = config('LOG_DIR');
        }
        $this->logDir = $logDir;

        // 日志是否需要分目录，及分目录的格式
        if ($format = config('logger_dir_format')) {
            $this->logDir .= '/' . date($format);
        }
        if (!is_dir($this->logDir)) {
            File::createDirectory($logDir, 0777);
        }
    }

    function log(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'debug'):string
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);

        $file = date('d') . ($category ? "_{$category}" : '') . '.log';
        $filePath = $this->logDir . "/{$file}";
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