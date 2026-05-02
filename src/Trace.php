<?php

namespace Es37;

use App\Constant\AppConst;
use App\Constant\ResultConst;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Component\Di;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Http\AbstractInterface\AbstractRouter;
use Es37\Constant\EsConst;
use FastRoute\RouteCollector;

/**
 * 配置自动加载
 * Class HttpRouter
 * @package Es37\Autoload
 */
class Trace
{
    /**
     * @return mixed
     * @throws \EasySwoole\Component\Context\Exception\ModifyError
     * @throws \Throwable
     */
    public static function getRequestId()
    {
        if (!ContextManager::getInstance()->get(EsConst::ES_DI_TRACE_CODE)) {
            Trace::createRequestId();
        }
        return ContextManager::getInstance()->get(EsConst::ES_DI_TRACE_CODE);
    }

    /**
     * @throws \EasySwoole\Component\Context\Exception\ModifyError
     */
    public static function createRequestId(): void
    {
//        Di::getInstance()->set(AppConst::DI_TRACE_CODE, md5(uniqid(microtime(true), true)));
        ContextManager::getInstance()->set(EsConst::ES_DI_TRACE_CODE, md5(uniqid(microtime(true), true)));
    }
}
