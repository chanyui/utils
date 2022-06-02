<?php

require_once __DIR__ . '/../../autoload.php';

use chanyu\utils\Tools\CurlTool;

var_dump(CurlTool::gcurl('https://www.baidu.com'));