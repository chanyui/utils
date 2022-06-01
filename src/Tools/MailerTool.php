<?php

namespace chanyu\Tools;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 邮件工具类
class MailerTool
{
    public static $config;

    public function __construct($config = [])
    {
        if (!$config) {
            
        }

        self::$config = $config;

        //邮件发送配置
        self::$config = [
            'mail_auth'     => true,
            'mail_server'   => 'smtp.mxhichina.com',
            'mail_port'     => '465',
            'mail_user'     => '',
            'mail_password' => '',
            'mail_from'     => '',
            'site_name'     => '邮件服务',
        ];
    }

    // 发送邮件
    public static function send($tomail, $subject, $body)
    {
        $mail = new PHPMailer(true);

        try {
            // 邮箱配置
            $mail->isSMTP();                                        // Set mailer to use SMTP
            $mail->SMTPDebug  = false;                              // 改为2可以开启调试
            $mail->CharSet    = 'utf-8';
            $mail->Host       = self::$config['mail_server'];       // Specify main and backup SMTP servers
            $mail->SMTPAuth   = self::$config['mail_auth'];         // Enable SMTP authentication
            $mail->Username   = self::$config['mail_user'];         // SMTP username
            $mail->Password   = self::$config['mail_password'];     // SMTP password
            $mail->SMTPSecure = 'ssl';                              // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = self::$config['mail_port'];         // TCP port to connect to

            // 设置发送者信息
            $mail->SetFrom(self::$config['mail_from'], self::$config['site_name']);

            // 设置接收者信息
            if (is_array($tomail)) {
                $tomail = array_filter($tomail);
                foreach ($tomail as $addressv) {
                    $mail->AddAddress($addressv);
                }
            } else {
                $mail->AddAddress($tomail);
            }

            // 发送邮件
            $mail->IsHTML(true); // 以HTML发送
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            trace([
                'title'   => '[Mail] 发送成功',
                'tomail'  => $tomail,
                'subject' => $subject,
                'body'    => $body
            ]);
            return true;
        } catch (Exception $e) {
            trace([
                'title'     => '[Mail] 发送失败',
                'ErrorInfo' => $mail->ErrorInfo,
                'error'     => $e->getMessage(),
                'tomail'    => $tomail,
                'subject'   => $subject,
                'body'      => $body
            ]);
        }
    }


    /**
     * swift插件发送邮件
     *
     * @author yc
     * @param $tomail
     * @param $subject
     * @param $body
     * @param array $config
     * @param string $filePath
     * @return bool
     */
    public static function sendSwift($tomail, $subject, $body, $config = [], $filePath = '')
    {
        //需要在 php.ini 里面配置
        //openssl.cafile = /usr/local/openssl/cacert.pem
        //openssl.capath = /usr/local/openssl/certs

        //vendor('SwiftMailer.swift_required');

        // 创建Transport对象，设置邮件服务器和端口号，并设置用户名和密码以供验证
        $transport = Swift_SmtpTransport::newInstance($config['mail_host'], $config['mail_port'], 'ssl')
            ->setUsername($config['mail_user'])
            ->setPassword($config['mail_pwd']);

        // 创建mailer对象
        $mailer = Swift_Mailer::newInstance($transport);
        $mailer->protocol = $config['mail_type'];

        // 创建message对象
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($config['mail_user'] => $config['send_name']))
            ->setTo(array($tomail))
            ->setContentType('text/html')
            ->setBody($body);

        if ($filePath) {
            // 创建attachment对象，content-type这个参数可以省略
            $attachment = Swift_Attachment::fromPath($filePath)
                ->setFilename(basename($filePath));

            // 添加附件
            $message->attach($attachment);
        }

        // 用关联数组设置收件人地址，可以设置多个收件人
        /*$message->setTo(array('to@qq.com' => 'toName'));*/

        // 用关联数组设置发件人地址，可以设置多个发件人
        /*$message->setFrom(array(
            'from@163.com' => 'fromName',
        ));*/

        // 添加抄送人
        /*$message->setCc(array(
            'Cc@qq.com' => 'Cc'
        ));*/

        // 添加密送人
        /*$message->setBcc(array(
            'Bcc@qq.com' => 'Bcc'
        ));*/

        try {
            if ($mailer->send($message)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo 'There was a problem communicating with SMTP: ' . $e->getMessage();
        }
    }
}
