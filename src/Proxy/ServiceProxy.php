<?php

namespace Es37\Proxy;

use App\Constant\AppConst;
use Es37\Constant\EsConst;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Logger;
use Es37\Base\Service;
use Es37\EsUtility;

class ServiceProxy
{
    protected $service;

    function __construct($namespace)
    {
        $className = EsUtility::getControllerClassName($namespace);
        $moduleName = EsUtility::getControllerModuleName($namespace);

        $moduleDirName = EsConst::ES_DIRECTORY_MODULE_NAME;
        $namespace = "AppBi\\{$moduleDirName}\\{$moduleName}\\Service\\{$className}Service";

        if ($moduleName == EsConst::ES_DIRECTORY_CONTROLLER_NAME) {
            return;
        }

        if (class_exists($namespace) && $moduleDirName != 'Controller') {
            $this->service = new $namespace();
        } else {
            if (!isProduction()) {
                $msg = 'service 加载失败 : ' . $namespace;
                Logger::getInstance()->console($msg, 3, 'proxy');
            }
        }
    }

    public function getService()
    {
        return $this->service;
    }
}
