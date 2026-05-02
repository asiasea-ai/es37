<?php

namespace Es3\Constant;

class EsConst
{
    /** 目录结构定制 */
    const ES_DIRECTORY_MODULE_NAME = 'Module';
    const ES_DIRECTORY_CONTROLLER_NAME = 'Controller';
    const ES_DIRECTORY_RPC_NAME = 'Rpc';
    const ES_DIRECTORY_APP_NAME = 'App';
    const ES_DIRECTORY_CONF_NAME = 'Conf';
    const ES_DIRECTORY_CRONTAB_NAME = 'Crontab';
    const ES_DIRECTORY_PROCESS_NAME = 'Process';
    const ES_DIRECTORY_QUEUE_NAME = 'Queue';
    const ES_DIRECTORY_EVENT_NAME = 'Event';
    const ES_FILE_NAME_ROUTER = 'router.php';
    const ES_FILE_NAME_EVENT = 'Event.php';

    /** 字段key定义 */
    const ES_KEY_COLUMN = 'column';
    const ES_KEY_VALUE = 'value';

    /** 框架中存储 */
    const ES_LOG_MYSQL_QUERY = 'ES_LOG_MYSQL_QUERY';
    const ES_TRACE_ID = 'ES_TRACE_ID';
    const ES_RABBIT = 'rabbit';
    const ES_REDIS = 'redis';

    /** DI */
    const ES_DI_TRACE_CODE = 'request_code';
    const ES_DI_AUTO_LIMITER = 'DI_AUTO_LIMITER';

    /** 策略 */
    const ES_POLICY_CONF_IS_AUTH = 'is_auth';
    const ES_POLICY_CONF_IS_SIGN = 'is_sign';
    const ES_POLICY_CONF_ATOMIC_LIMIT_URL = 'atomic_limit_url';
    const POLICY_CONF_ATOMIC_LIMIT_URL = 'atomic_limit_url';
    const POLICY_CONF_IS_AUTH = 'is_auth';
    const POLICY_CONF_IS_SIGN = 'is_sign';
}
