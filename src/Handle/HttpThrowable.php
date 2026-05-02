<?php

namespace Es37\Handle;

use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use Es37\Output\Json;

class HttpThrowable
{
    public static function run(\Throwable $throwable, Request $request, Response $response)
    {
        Json::fail($throwable, $throwable->getCode(), $throwable->getMessage());
    }
}