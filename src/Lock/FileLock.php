<?php

namespace Es37\Lock;

use EasySwoole\Core\Component\Logger;
use Es37\Exception\ErrorException;

class FileLock
{
    public static function get(string $fileName): EasyLock
    {
        $tempDir = \config('LOCK_DIR');
        $fileName = "{$tempDir}lock_{$fileName}.lock";

        $lock = new EasyLock($fileName);
        return $lock;
    }
}