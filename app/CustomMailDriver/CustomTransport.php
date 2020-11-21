<?php

namespace App\CustomMailDriver;

use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\ClientInterface;
use Swift_Mime_SimpleMessage;

class CustomTransport extends Transport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The API URL to which to POST emails.
     *
     * @var string
     */
    protected $url;

    /**
     * Create a new Custom transport instance.
     *
     * @param  \GuzzleHttp\ClientInterface  $client
     * @param  string|null  $url
     * @param  string  $key
     * @return void
     */
    public function __construct(ClientInterface $client,  $url, $key)
    {
        $this->key = $key;
        $this->client = $client;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $payload = $this->getPayload($message);

        $this->client->request('POST', $this->url, $payload);

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get the HTTP payload for sending the message.
     *
     * @param  \Swift_Mime_SimpleMessage  $message
     * @return array
     */
    protected function getPayload(Swift_Mime_SimpleMessage $message)
    {
        // Change this to the format your API accepts
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->key,
                'Accept'        => 'application/json',
            ],
            'json' => [
                'to' => $this->mapContactsToNameEmail($message->getTo()),
                'cc' => $this->mapContactsToNameEmail($message->getCc()),
                'bcc' => $this->mapContactsToNameEmail($message->getBcc()),
                'message' => $message->getBody(),
                'subject' => $message->getSubject(),
            ],
        ];
    }

    protected function mapContactsToNameEmail($contacts)
    {
        $formatted = [];
        if (empty($contacts)) {
            return [];
        }
        foreach ($contacts as $address => $display) {
            $formatted[] =  [
                'name' => $display,
                'email' => $address,
            ];
        }
        return $formatted;
    }

}
