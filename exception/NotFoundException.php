<?php


namespace tn\phpmvc\exception;


class NotFoundException extends \Exception
{
    protected $message = 'Not found';
    protected $code = 404;
}