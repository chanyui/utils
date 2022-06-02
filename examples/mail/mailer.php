<?php

require_once __DIR__ . '/../../autoload.php';

use chanyu\Mail\Mailer;

$mail = new Mailer(['host' => '', 'username' => '', 'password' => '', 'from' => '', 'name' => '系统邮件']);
var_dump($mail->send(['address' => ''], ['subject' => '测试', 'type' => 'html', 'body' => '<b>测试</b>']));