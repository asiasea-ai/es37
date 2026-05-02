<?php
declare(strict_types=1);

namespace Es3\Route;

/**
 * 路由助手 —— 把 Module 下的控制器方法（`AppBi\Module\<M>\Controller\<Ring>\<Entity>::method`）
 * 包装成 FastRoute 可用的闭包 handler，绕开 Es3 Dispatcher 只认 `AppBi\Controller\*` 字符串前缀的限制。
 *
 * 路由混淆段（mask）：为防止 URL 暴露明显的 entity 段被外部猜测，每条路由 path 头部都要拼一个
 * 12 位 [a-z0-9] 的随机段。该段直接写死在 path 字符串里，由项目侧的代码生成器（如
 * `Tools/generate-module.php`）在生成 Route 模板时一次性写入，激活时不需要任何额外包装；
 * 运行期代码不需要再生成 mask。
 *
 * 用法（在各 Module 的 router.php 里）：
 *
 *   use Es3\Route\Router as R;
 *   $route->addRoute('GET', '/aBcDeFgHiJkL/credential/detail', R::modHandler(CredentialCtrl::class, 'detail'));
 *
 * 完整 URL = outer + inner + path（path 里已含 mask）
 *   例：outer=/web，inner=/identity/account，path=/aBcDeFgHiJkL/credential/detail
 *       → /web/identity/account/aBcDeFgHiJkL/credential/detail
 *
 * 历史：原来位于业务侧 `AppBi\Util\Router`，2026-05-01 迁回 Es3 vendor。
 * 详见 `Doc/vendor/Es3.md` #4。
 */
class Router
{
    /**
     * 将控制器类名和方法名包装为 FastRoute 闭包 handler。
     *
     * Dispatcher 收到 callable handler 时直接 call($request, $response)，
     * 闭包内实例化 Controller 并调 `__hook()`（完整生命周期：onRequest 鉴权/签名校验 → 方法分派 → 异常处理）。
     *
     * @param string $fqcn   控制器完全限定类名（FQCN）
     * @param string $method 控制器方法名
     * @return \Closure 接受 ($request, $response) 的闭包 handler
     */
    public static function modHandler(string $fqcn, string $method): \Closure
    {
        return static function ($request, $response) use ($fqcn, $method) {
            $ctrl = new $fqcn($request, $response, $method);
            $ctrl->__hook();
            return false; // 终止 Dispatcher 的后续处理
        };
    }
}
