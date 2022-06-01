<?php

require_once __DIR__ . '/../../autoload.php';

use chanyu\Tools\StringTool;

var_dump(StringTool::authcode('123456', 'ENCODE'));

var_dump(StringTool::get_check_idcard('330127199208046511'));