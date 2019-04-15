<?php

namespace svsoft\yii\items\exceptions;

use Throwable;

class ItemNotFoundException extends \LogicException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        if (!$message)
            $message = 'Item not found';

        parent::__construct($message, $code, $previous);
    }

}