<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ApiException extends Exception
{
    protected $statusCode;
    protected $errors;

    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return void
     */
    public function __construct(
        string $message = 'An error occurred',
        int $statusCode = Response::HTTP_BAD_REQUEST,
        $errors = null
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the errors.
     *
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }


}
