<?php


namespace tn\phpmvc;
use tn\phpmvc\DbModel;

abstract class UserModel extends DbModel
{
    abstract function getDisplayName(): string;
}