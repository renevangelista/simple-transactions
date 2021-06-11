<?php

namespace App\Service;

use App\Model\Core\Message;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->message = new Message();
        $this->client = new Client();
    }

    private static function url(): string
    {
        return 'http://o4d9z.mocklab.io/notify';
    }

    public function notify(): Message
    {
        try {
            $response = $this->client->get(
                self::url()
            );

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents());

            if ($statusCode != 200 || $body->message != 'Success') {
                return $this->message->error(trans('system.messages.unauthorized_transaction'), null, '');
            }
        } catch (GuzzleException $e) {
            return $this->message->error($e->getMessage(), null, $e->getTrace());
        } catch (Exception $e) {
            return $this->message->error($e->getMessage(), null, '');
        }

        Log::info(trans('system.messages.notification_sent_successfully'));
        return $this->message->success(trans('system.messages.authorized_transaction'), null);
    }
}
