<?php


namespace tn\phpmvc;


abstract class UserModel extends DbModel
{
    abstract function getDisplayName(): string;

}