<?php
namespace tn\phpmvc\middlewares;


use tn\phpmvc\Application;
use tn\phpmvc\exception\ForbiddenException;

class AdminMiddleware extends BaseMiddleware
{
    /**
     * AdminMiddleware constructor.
     * @param array $actions
     */
    public function __construct(array $actions = [])
    {
        $this->actions = $actions;
    }

    public function execute()
    {
        if (!Application::isStaff()) {
            if(empty($this->actions) || in_array(Application::$app->controller->action,$this->actions)) {
                throw new ForbiddenException();
            }
        }
    }
}