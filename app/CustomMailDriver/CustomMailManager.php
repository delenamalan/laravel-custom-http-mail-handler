<?php

namespace App\CustomMailDriver;

use Illuminate\Mail\MailManager;
use App\CustomMailDriver\CustomTransport;


class CustomMailManager extends MailManager
{
    protected function createCustomTransport()
    {
        $config = $this->app['config']->get('services.custom_mail', []);

        return new CustomTransport(
            $this->guzzle($config), $config['key'], $config['url']
        );
    }
}
