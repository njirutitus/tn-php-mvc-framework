<?php


namespace app\core\exception;


class NotFoundException extends \Exception
{
    protected $message = 'Not found';
    protected $code = 404;
}