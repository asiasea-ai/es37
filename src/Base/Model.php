<?php

namespace Es37\Base;

use App\Constant\AppConst;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\Db\ClientInterface;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Utility\Schema\Table;
use Es37\Constant\ResultConst;
use Es37\EsUtility;
use Es37\Exception\DbException;
use Es37\Exception\ErrorException;
use Es37\Exception\InfoException;

class Model extends AbstractModel
{
    protected $autoTimeStamp = false;

    protected $createTime = false;

    protected $updateTime = false;

    /**
     * 调整where条件
     */
    public function adjustWhere(array $params): array
    {
        $schemaInfo = $this->schemaInfo();
        $columns = $schemaInfo->getColumns();

        foreach ($params as $field => $value) {
            /** 如果不是该字段的数据 自动删除掉 */
            if (!isset($columns[$field])) {
                unset($params[$field]);
            }
        }

        return $params;
    }

    public function autoCreateUser($params): array
    {
        $schemaInfo = $this->schemaInfo();
        $columns = $schemaInfo->getColumns();

        $paramsKeys = array_keys($params);
        foreach ($columns as $key => $column) {

            /** 如果外界传进来user code 就不自动增加 */
            if (!array_intersect($paramsKeys, AppConst::TABLE_AUTO_CREATE_USER_CODE)) {
                /** 增加user code */
                if (in_array($key, AppConst::TABLE_AUTO_CREATE_USER_CODE) && createUserCode()) {
                    $params[$key] = createUserCode();
                }

                /** 增加user name */
                if (in_array($key, AppConst::TABLE_AUTO_CREATE_USER_NAME) && createUserName()) {
                    $params[$key] = createUserName();
                }
            }
        }

        return $params;
    }

    public function autoUpdateUser($params): array
    {
        $schemaInfo = $this->schemaInfo();
        $columns = $schemaInfo->getColumns();

        $paramsKeys = array_keys($params);

        foreach ($columns as $key => $column) {

            if (array_intersect($paramsKeys, AppConst::TABLE_AUTO_CREATE_USER_CODE)) {
                /** 增加user code */
                if (in_array($key, AppConst::TABLE_AUTO_UPDATE_USER_CODE) && createUserCode()) {
                    $params[$key] = createUserCode();
                }
            }

            if (array_intersect($paramsKeys, AppConst::TABLE_AUTO_UPDATE_USER_NAME)) {
                /** 增加user name */
                if (in_array($key, AppConst::TABLE_AUTO_UPDATE_USER_NAME) && createUserName()) {
                    $params[$key] = createUserName();
                }
            }
        }

        return $params;
    }

    /**
     * 获取逻辑标志
     */
    public function getLogicDelete(string $value = '0'): array
    {
        $schemaInfo = $this->schemaInfo();
        $columns = $schemaInfo->getColumns();

        foreach (AppConst::TABLE_LOGIC_DELETE as $field) {
            if (isset($columns[$field])) {
                return [$field => $value];
            }
        }

        return [];
    }

    /**
     * 重写删除方法 为了兼容逻辑删除
     */
    public function delete($where = null, $allow = false): int
    {
        /** 如果有逻辑删除标识 就添加上条件 */
        $LogicDelete = $this->getLogicDelete('1');

        if (empty($LogicDelete)) {
            $count = intval(parent::destroy($where, $allow));
        } else {
            $count = intval(parent::update($LogicDelete, $where));
        }

        return $count;
    }

    /**
     * 重写model中的自动开启事物
     */
    public function insertAll($data): array
    {
        $tableName = $this->getTableName();
        $data = \Es37\Utility\Model::insertAll($tableName, $data);

        $sql = $data[ResultConst::DB_QUERY];
        $bind = $data[ResultConst::DB_BIND];

        try {
            $queryBuild = new QueryBuilder();
            $queryBuild->raw($sql, $bind);

            $results = DbManager::getInstance()->query($queryBuild, true);

            $results->getResult();

            $lastErrorNo = $results->getLastErrorNo();
            $lastError = $results->getLastError();

            if ($lastErrorNo !== 0) {
                throw new InfoException(1011, "批量写入失败: " . $lastError);
            }

            return [
                ResultConst::RESULT_AFFECTED_ROWS_KEY => $results->getAffectedRows(),
                ResultConst::RESULT_LAST_INSERT_ID_KEY => $results->getLastInsertId()
            ];
        } catch (\Throwable $throwable) {
            // 手动设置异常位置
            setResultFile($throwable, 2);
            throw new DbException($throwable->getCode(), $throwable->getMessage());
        }
    }

    /**
     * 取整张表的字段元数据 map(即 FIELDS 常量)。
     *
     * 命名带 get 前缀,避开 EasySwoole ORM AbstractModel 已存在的 instance 方法 field()。
     * 历史:本来在项目侧 App\Base\BaseModel,2026-05-02 合并到 Es37\Base\Model
     * (登记 Doc/vendor/Es3.md #5)。
     *
     * @return array<string, array{name: string, type: string, value?: array<int|string, string>}>
     */
    public static function getFields(): array
    {
        return defined(static::class . '::FIELDS') ? static::FIELDS : [];
    }

    /**
     * 取单字段元数据 wrapper:`{name, type, [value]}`。未命中返回空 []。
     *
     * @return array<string, mixed>
     */
    public static function getField(string $field): array
    {
        $fields = self::getFields();
        $meta = $fields[$field] ?? [];
        return is_array($meta) ? $meta : [];
    }

    /**
     * 取字段中文名(fallback 字段名本身)。
     */
    public static function fieldName(string $field): string
    {
        $fields = self::getFields();
        return $fields[$field]['name'] ?? $field;
    }

    /**
     * 取全字段的 `[field => name]` map,给 OperationLog / 前端表头等批量场景用。
     *
     * @return array<string, string>
     */
    public static function fieldNames(): array
    {
        $out = [];
        foreach (self::getFields() as $field => $meta) {
            $out[(string)$field] = is_array($meta) ? ($meta['name'] ?? (string)$field) : (string)$field;
        }
        return $out;
    }

    /**
     * 取字段类型('enum'/'int'/'float'/'string'/'datetime'/'date'/'time'/'json')。
     */
    public static function fieldType(string $field): string
    {
        $fields = self::getFields();
        return $fields[$field]['type'] ?? 'string';
    }

    /**
     * 反查:取某字段某值的中文 label,仅枚举字段有效,其它返回 ''。
     */
    public static function enumLabel(string $field, int|string $value): string
    {
        $fields = self::getFields();
        $meta = $fields[$field] ?? null;
        if (!is_array($meta) || ($meta['type'] ?? '') !== 'enum') {
            return '';
        }
        $map = $meta['value'] ?? [];
        return is_array($map) ? ($map[$value] ?? '') : '';
    }

    /**
     * 把所有 type='enum' 字段打成 `[field => [{value, label}, ...]]`,给前端下拉用。
     *
     * @return array<string, array<int, array{value: int|string, label: string}>>
     */
    public static function enumLabels(): array
    {
        $out = [];
        foreach (self::getFields() as $field => $meta) {
            if (!is_array($meta) || ($meta['type'] ?? '') !== 'enum') {
                continue;
            }
            $map = $meta['value'] ?? [];
            if (!is_array($map)) {
                continue;
            }
            $items = [];
            foreach ($map as $v => $l) {
                $items[] = ['value' => $v, 'label' => $l];
            }
            $out[(string)$field] = $items;
        }
        return $out;
    }

}
