<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class EmailAlreadyUsedException extends CustomUserMessageAuthenticationException
{
    public function __construct(
        string $message = 'This email has already another account created with another service.',
        array $messageData = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $messageData, $code, $previous);
    }
}
