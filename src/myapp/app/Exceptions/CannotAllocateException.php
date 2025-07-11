<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CannotAllocateException extends HttpException
{
    /**
     * @param string $message   A human-readable error message
     * @param int    $statusCode  HTTP status code (default 400 Bad Request)
     */
    public function __construct(
        string $message = 'Cannot allocate parking spot for this vehicle type.',
        int $statusCode = 400
    ) {
        parent::__construct($statusCode, $message);
    }
}
