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
     * @param  string  $key
     * @param  string|null  $url
     * @return void
     */
    public function __construct(ClientInterface $client, $key, $url)
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

        $to = $this->getTo($message);

        $payload = $this->payload($message, $to);

        $response = $this->client->request(
            'POST',
            $this->url,
            $payload
        );

        $this->sendPerformed($message);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get the HTTP payload for sending the message.
     *
     * @param  \Swift_Mime_SimpleMessage  $message
     * @param  string  $to
     * @return array
     */
    protected function payload(Swift_Mime_SimpleMessage $message, $to)
    {
        // Change this to the format your API accepts
        return [
            'auth' => [
                'api',
                $this->key,
            ],
            'json' => [
                [
                    'name' => 'to',
                    'contents' => $to,
                ],
                [
                    'name' => 'message',
                    'contents' => $message->toString(),
                    'filename' => 'message.mime',
                ],
            ],
        ];
    }

    /**
     * Get the "to" payload field for the API request.
     *
     * @param  \Swift_Mime_SimpleMessage  $message
     * @return string
     */
    protected function getTo(Swift_Mime_SimpleMessage $message)
    {
        return collect($this->allContacts($message))->map(function ($display, $address) {
            return $display ? $display." <{$address}>" : $address;
        })->values()->implode(',');
    }

    /**
     * Get all of the contacts for the message.
     * 
     * Collects contacts from 'to', 'cc' and 'bcc'.
     *
     * @param  \Swift_Mime_SimpleMessage  $message
     * @return array
     */
    protected function allContacts(Swift_Mime_SimpleMessage $message)
    {
        return array_merge(
            (array) $message->getTo(),
            (array) $message->getCc(),
            (array) $message->getBcc()
        );
    }

}
