<?php

namespace App\Service;

use App\Model\Core\Message;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ExternalValidatorService
{
    /**
     * @var Message
     */
    protected $message;
    /**
     * @var Client
     */
    protected $client;

    private static function url()
    {
        return config('app.external_validator_url');
    }

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->message = new Message();
        $this->client = new Client();
    }

    /**
     * @param array $data
     * @return Message
     * @SuppressWarnings("unused")
     */
    public function validate(array $data): Message
    {
        try {
            $response = $this->client->get(
                self::url()
            );

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents());

            if ($statusCode != 200 || $body->message != 'Autorizado') {
                return $this->message->error(trans('system.messages.unauthorized_transaction'), null, '');
            }
        } catch (GuzzleException $e) {
            return $this->message->error($e->getMessage(), null, $e->getTrace());
        } catch (Exception $e) {
            return $this->message->error($e->getMessage(), null, '');
        }

        return $this->message->success(trans('system.messages.authorized_transaction'), null);
    }
}
