<?php

namespace chanyu\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mailer extends Config implements Contract
{
    protected $mail = null;

    /**
     * Mailer constructor.
     * @param array $option
     */
    public function __construct($option = [])
    {
        $this->config['debug'] = SMTP::DEBUG_OFF;                   //Enable verbose debug output
        $this->config['type'] = 'smtp';                             //Send using SMTP
        $this->config['secure'] = PHPMailer::ENCRYPTION_SMTPS;      //Enable implicit TLS encryption

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
