<?php


namespace tn\phpmvc\middlewares;


abstract class BaseMiddleware
{
    abstract public function execute();
}