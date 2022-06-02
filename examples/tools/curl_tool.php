<?php

require_once __DIR__ . '/../../autoload.php';

use Chanyu\Utils\Tools\CurlTool;

var_dump(CurlTool::gcurl('https://www.baidu.com'));