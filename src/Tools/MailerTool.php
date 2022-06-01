<?php

namespace chanyu\Tools;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// 邮件工具类
class MailerTool
{
    protected $mail = null;

    protected $config = [
        'debug'    => SMTP::DEBUG_OFF,                    //Enable verbose debug output
        'type'     => 'smtp',                             //Send using SMTP
        'auth'     => true,                               //Enable SMTP authentication
        'secure'   => PHPMailer::ENCRYPTION_SMTPS,        //Enable implicit TLS encryption
        'port'     => '465',                              //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        'host'     => '',                                 //Set the SMTP server to send through
        'username' => '',                                 //SMTP username
        'password' => '',                                 //SMTP password
        'from'     => '',                                 //send address
        'name'     => '',                                 //send name
    ];

    public function __construct($option = [])
    {
        if ($option && is_array($option)) {
            $this->config = array_merge($this->config, $option);
        }
        if (!$this->mail) {
            // Create an instance; passing `true` enables exceptions
            $this->mail = new PHPMailer(true);
        }
    }

    /**
     * 发送邮件
     *
     * @author yc
     * @param $tomail
     * @param $payload
     * @param array $reply
     * @param array $cc
     * @param array $attachment
     * @return mixed true-发送成功，其他失败
     * @throws Exception
     */
    public function send($tomail, $payload, $reply = [], $cc = [], $attachment = [])
    {
        // 验证参数
        $validate = $this->validateParam($tomail, $payload, $reply, $cc, $attachment);
        if ($validate !== true) {
            return $this->returnMail($validate);
        }

        //Create an instance; passing `true` enables exceptions
        // $mail = new PHPMailer(true);

        try {
            //Server settings
            $this->mail->SMTPDebug = $this->config['debug'];                              //Enable verbose debug output
            if ($this->config['type'] == 'smtp') {
                $this->mail->isSMTP();                                                    //Send using SMTP
            }

            $this->mail->Host = $this->config['host'];                                    //Set the SMTP server to send through
            $this->mail->SMTPAuth = $this->config['auth'];                                //Enable SMTP authentication
            $this->mail->Username = $this->config['username'];                            //SMTP username
            $this->mail->Password = $this->config['password'];                            //SMTP password
            $this->mail->SMTPSecure = $this->config['secure'];                            //Enable implicit TLS encryption
            $this->mail->Port = $this->config['port'];                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $this->mail->setFrom($this->config['from'], $this->config['name']);

            // to
            foreach ($tomail as $value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $name = isset($val['name']) ? $val['name'] : '';
                        $this->mail->addAddress($val['address'], $name);
                    }
                } else {
                    $name = isset($tomail['name']) ? $tomail['name'] : '';
                    $this->mail->addAddress($tomail['address'], $name);
                }
            }

            // reply
            if ($reply) {
                $rname = isset($reply['name']) ? $reply['name'] : '';
                $this->mail->addReplyTo($reply['address'], $rname);
            }

            // cc
            if ($cc) {
                foreach ($cc as $value) {
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $cname = isset($val['name']) ? $val['name'] : '';
                            $this->mail->addCC($val['address'], $cname);
                        }
                    } else {
                        $cname = isset($cc['name']) ? $cc['name'] : '';
                        $this->mail->addCC($cc['address'], $cname);
                    }
                }
            }

            // $this->mail->addBCC('bcc@example.com');

            // Attachments
            if ($attachment) {                                              //Add attachments Optional name
                foreach ($attachment as $value) {
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $aname = isset($val['name']) ? $val['name'] : '';
                            $this->mail->addAttachment($val['path'], $aname);
                        }
                    } else {
                        $aname = isset($attachment['name']) ? $attachment['name'] : '';
                        $this->mail->addAttachment($attachment['path'], $aname);
                    }
                }
            }

            // content
            if (isset($payload['type']) && $payload['type'] == 'html') {
                $this->mail->isHTML(true);                                  //Set email format to HTML
            }
            $this->mail->Subject = $payload['subject'];
            $this->mail->Body = $payload['body'];

            if (isset($payload['altBody']) && $payload['altBody']) {
                $this->mail->AltBody = $payload['altBody'];
            }

            $msg = $this->mail->send();
        } catch (Exception $e) {
            $msg = "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }

        return $msg;
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
    public function sendSwift($tomail, $subject, $body, $config = [], $filePath = '')
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

    /**
     * 验证参数
     *
     * @author yc
     * @param $tomail
     * @param $body
     * @param array $reply
     * @param array $cc
     * @param array $attachment
     * @return bool|string
     */
    private function validateParam($tomail, $body, $reply = [], $cc = [], $attachment = [])
    {
        $addressArr = ['address'];
        // 验证格式
        if (!is_array($tomail)) {
            return '邮件发送地址：{格式有误}';
        }
        foreach ($tomail as $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $keys = array_keys($val);
                    if (array_diff($addressArr, $keys)) {
                        return '邮件发送收件人有误：{收件人和收件地址不能为空}！';
                    }
                }
            } else {
                $keys = array_keys($tomail);
                if (array_diff($addressArr, $keys)) {
                    return '邮件发送收件人有误：{收件人和收件地址不能为空}';
                }
            }
        }
        // 判断内容是否正确
        $bkeys = array_keys($body);
        if (array_diff(['subject', 'body'], $bkeys)) {
            return '邮件发送内容有误：{标题和内容不能为空}';
        }

        // 回复
        if ($reply) {
            if (array_diff($addressArr, array_keys($reply))) {
                return '邮件回复人有误：{回复人和回复地址不能为空}';
            }
        }
        // 抄送
        if ($cc && is_array($cc)) {
            $caddress = ['address'];
            foreach ($cc as $value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $keys = array_keys($val);
                        if (array_diff($caddress, $keys)) {
                            return '抄送人地址有误：{抄送人地址不能为空}!';
                        }
                    }
                } else {
                    $keys = array_keys($cc);
                    if (array_diff($caddress, $keys)) {
                        return '抄送人地址有误：{抄送人地址不能为空}';
                    }
                }
            }
        }

        // 附件
        if ($attachment && is_array($attachment)) {
            $aaddress = ['path'];
            foreach ($attachment as $value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $keys = array_keys($val);
                        if (array_diff($aaddress, $keys)) {
                            return '附件路径有误：{附件路径不能为空}!';
                        }
                    }
                } else {
                    $keys = array_keys($attachment);
                    if (array_diff($aaddress, $keys)) {
                        return '附件路径有误：{附件路径不能为空}';
                    }
                }
            }
        }
        return true;
    }

    /**
     * 返回结果
     * @param $msg
     * @return mixed
     * @throws Exception
     */
    private function returnMail($msg)
    {
        if ($this->config['debug'] > 0) {
            throw new Exception($msg);
        } else {
            return $msg;
        }
    }
}
