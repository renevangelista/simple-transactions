<?php

namespace App\Model\Core;

/**
 * Class Message
 * @package App\Model\Core
 */
class Message
{
    /**
     * @var
     */
    private $type;
    /**
     * @var
     */
    private $message;
    /**
     * @var
     */
    private $data;
    /**
     * @var
     */
    private $errors;

    public const MESSAGE_SUCCESS = "success";
    public const MESSAGE_ERROR = "danger";
    public const MESSAGE_WARNING = "warning";

    /**
     * @param string $message
     * @param $model
     * @return $this
     */
    public function success(string $message, $model): Message
    {
        $this->type = self::MESSAGE_SUCCESS;
        $this->message = $this->filter($message);
        $this->data = $model;
        $this->errors = null;

        return $this;
    }

    /**
     * @param string $message
     * @param $model
     * @param $errors
     * @return $this
     */
    public function error(string $message, $model, $errors): Message
    {
        $this->type = self::MESSAGE_ERROR;
        $this->message = $this->filter($message);
        $this->data = $model;
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param string $message
     * @param $model
     * @param $errors
     * @return $this
     */
    public function warning(string $message, $model, $errors): Message
    {
        $this->type = self::MESSAGE_WARNING;
        $this->message = $this->filter($message);
        $this->data = $model;
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->type == self::MESSAGE_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->type == self::MESSAGE_ERROR;
    }

    /**
     * @return bool
     */
    public function isWarning(): bool
    {
        return $this->type == self::MESSAGE_WARNING;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getFlash(): array
    {
        return ['type' => $this->type, 'message' => $this->message];
    }

    /**
     * @param string $message
     * @return string
     */
    private function filter(string $message): string
    {
        return filter_var($message, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
