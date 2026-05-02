<?php

namespace Es3\AutoLoad;

use App\Constant\AppConst;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Http\AbstractInterface\AbstractRouter;
use Es3\EsConst;
use FastRoute\RouteCollector;

/**
 * 路由注册类
 * Class Router
 * @package AppBi\HttpControllers
 */
class Router
{
    protected $router = [];

    use Singleton;

    /**
     * 待注入路由配置
     */
    public function autoLoad(): void
    {
        try {
            $path = EASYSWOOLE_ROOT . '/' . \Es3\Constant\EsConst::ES_DIRECTORY_APP_NAME . '/' . \Es3\Constant\EsConst::ES_DIRECTORY_MODULE_NAME . '/';
            $files = scandir($path) ?? [];

            foreach ($files as $key => $dir) {
                //过滤非目录
                if (strpos($dir, '.') !== false) {
                    unset($files[$key]);
                }
            }

            // 本项目用 2 级模块结构(AppBi/Module/<L1>/<L2>/router.php),
            // 上游原版只扫 L1,本补丁:L1 直接命中就用 L1,否则扫 L2 目录;
            // 见 Doc/vendor/Es3.md #1
            foreach ($files as $dir) {
                $l1RouterFile = $path . $dir . '/' . \Es3\Constant\EsConst::ES_FILE_NAME_ROUTER;
                if (file_exists($l1RouterFile)) {
                    // L1 命中:沿用旧行为
                    $data = require_once $l1RouterFile;
                    echo Utility::displayItem('Router', $l1RouterFile);
                    echo "\n";
                    $this->router[] = $data;
                    continue;
                }

                // L1 没 router.php → 当作 L1 容器,继续扫 L2
                $l1Dir = $path . $dir;
                if (!is_dir($l1Dir)) {
                    continue;
                }
                $l2List = scandir($l1Dir) ?: [];
                foreach ($l2List as $l2) {
                    if (strpos($l2, '.') !== false) {
                        continue;
                    }
                    $l2RouterFile = $l1Dir . '/' . $l2 . '/' . \Es3\Constant\EsConst::ES_FILE_NAME_ROUTER;
                    if (!file_exists($l2RouterFile)) {
                        continue;
                    }
                    $data = require_once $l2RouterFile;
                    echo Utility::displayItem('Router', $l2RouterFile);
                    echo "\n";
                    $this->router[] = $data;
                }
            }

        } catch (\Throwable $throwable) {
            echo 'Router Initialize Fail :' . $throwable->getMessage();
        }
    }

    /**
     * 路由注册
     */
    public function initialize(RouteCollector $routeCollector): void
    {
        foreach ($this->router as $file) {
            foreach ($file as $rKey => $rType) {
                foreach ($rType as $perfix => $routerFunction) {
                    $routeCollector->addGroup($rKey . $perfix, $routerFunction);
                }
            }
        }
    }
}
