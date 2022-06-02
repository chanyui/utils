<?php

namespace chanyu\Mail;

interface Contract
{
    public function send($tomail, $payload, $reply = [], $cc = [], $attachment = []);
}