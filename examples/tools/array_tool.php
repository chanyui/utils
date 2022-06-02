<?php

require_once __DIR__ . '/../../autoload.php';

use chanyu\utils\Tools\ArrayTool;

$array = [
    ['id' => 1, 'name' => '测试1'],
    ['id' => 2, 'name' => '测试2'],
    ['id' => 3, 'name' => '测试3'],
];

var_dump(ArrayTool::get_array_by_rows($array, 'id', true));

var_dump(ArrayTool::get_field_string($array, 'name', '|'));