<?php


namespace tn\phpmvc;


class Response
{
    public function setStatusCode($code){
        http_response_code((int)$code);
    }

    public function redirect(string $url)
    {
        header('Location: '.$url);
    }

}