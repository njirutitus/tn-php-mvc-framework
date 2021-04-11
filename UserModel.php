<?php


namespace tn\phpmvc;
use tn\phpmvc\db\DbModel;


abstract class UserModel extends DbModel
{
    abstract function getDisplayName(): string;

}