<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use App\CustomMailDriver\CustomTransport;
use Swift_Message;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class CustomMailDriverTest extends TestCase
{
    protected function createEmail()
    {
        $message = new Swift_Message('Test subject', '<body>Message body</body>');
        $message->setTo('to@example.com');
        $message->setCc('cc@example.com');
        $message->setBcc('bcc@example.com');
        $message->setFrom('from@example.com');
        return $message;
    }

    protected function mockGuzzleClient()
    {
        $this->mock_handler = new MockHandler([
            new Response(200, []),
        ]);

        $this->client = new Client(['handler' => $this->mock_handler]);
    }

    public function testSendMail()
    {
        $this->mockGuzzleClient();

        $message = $this->createEmail();

        $test_url = 'https://localhost:8000/email';

        $transport = new CustomTransport(
            $this->client,
            $test_url,
            'secret-key'
        );

        $transport->send($message);

        $last_request = $this->mock_handler->getLastRequest();
        $body = (string) $last_request->getBody();
        $data = json_decode($body, true);

        $expected = [
            'to' => [
                [
                    'name' => null,
                    'email' => 'to@example.com',
                ]
            ],
            'cc' => [
                [
                    'name' => NULL,
                    'email' => 'cc@example.com',
                ]
            ],
            'bcc' => [
                [
                    'name' => NULL,
                    'email' => 'bcc@example.com',
                ]
            ],
            'message' => '<body>Message body</body>',
            'subject' => 'Test subject',
        ];

        // Test that the data was sent to the correct URL
        $this->assertEquals($test_url, (string)$last_request->getUri());

        // Test the correct data was sent to the API
        $this->assertEquals($expected, $data);

        // Test that the authorization key was sent in the headers
        $this->assertEquals('Bearer secret-key', $last_request->getHeaderLine('Authorization'));
    }
}
