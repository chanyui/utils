<?php

namespace chanyu\Tools;

class DbTool
{
    /**
     * 生成批量更新的Sql语句
     *
     * @param $tableName
     * @param $key
     * @param array $updateArr
     * @param string $prefix
     * @return string
     */
    public static function getBatchUpdateSql($tableName, $key, array $updateArr, $prefix = '')
    {
        $sql = 'UPDATE `' . $prefix . $tableName . '` SET ';
        $setKeyArr = array_keys($updateArr[0]);
        foreach ($setKeyArr as $v) {
            if ($v !== $key) {
                $sql .= '`' . $v . '` = CASE `' . $key . '` ';
                foreach ($updateArr as $vv) {
                    $sql .= 'WHEN ' . $vv[$key] . ' THEN \'' . $vv[$v] . '\' ';
                }
                $sql .= 'END,';
            }
        }
        $sql = trim($sql, ',');
        $sql .= ' WHERE `' . $key . '` IN (\'' . join("','", ArrayTool::get_rows_by_array($updateArr, $key)) . '\')';
        return $sql;
    }
}