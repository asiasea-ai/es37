<?php

namespace Es37\Exception;

use App\Constant\AppConst;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\Logger;
use Es37\Constant\ResultConst;

class CurlException extends BaseException
{
    public function __construct(int $code, string $msg = '', \Throwable $previous = null)
    {
        $this->category = 'curl';
        $this->level = 'error';
        parent::__construct($code, $msg, $previous);
    }
}