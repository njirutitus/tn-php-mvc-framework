<?php


namespace tn\phpmvc\db;

use tn\phpmvc\Application;
use tn\phpmvc\utils\Filesystem;


abstract class Model
{
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';
    public const RULE_UNIQUE = 'unique';
    public const RULE_EXISTS = 'exists';
    public const RULE_NUMBER = 'number';
    public const RULE_VALID_FILE_TYPE = 'filetype';
    public const RULE_MAX_FILE_SIZE = 'maxsize';
    public const RULE_UPLOADED = 'upload';

    public function loadData($data){
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)){
                $this->{$key} = $value;
            }
        }

    }

    abstract  public  function rules():array;
    public function labels(): array
    {
        return [];
    }

    public function getLabel($attribute)
    {
        return $this->labels()[$attribute] ?? $attribute;
    }
    public array $errors = [];

    public function validate(){
        foreach($this->rules() as $attribute => $rules) {
            $value = $this->{$attribute};
            foreach ($rules as $rule) {
                $ruleName = $rule;
                if (!is_string($ruleName)) {
                    $ruleName = $rule[0];
                }

                if ($ruleName === self::RULE_REQUIRED && is_array($value) && array_key_exists('tmp_name', $value) && !$value['tmp_name']) {
                    $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                }
                if($ruleName === self::RULE_REQUIRED && !$value){
                    $this->addErrorForRule($attribute,self::RULE_REQUIRED);
                }
                if ($ruleName === self::RULE_EMAIL && !filter_var($value,FILTER_VALIDATE_EMAIL)) {
                    $this->addErrorForRule($attribute,self::RULE_EMAIL);
                }
                if ($ruleName === self::RULE_NUMBER && !filter_var($value,FILTER_VALIDATE_FLOAT)) {
                    $this->addErrorForRule($attribute,self::RULE_NUMBER);
                }
                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addErrorForRule($attribute,self::RULE_MIN,$rule);
                }
                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addErrorForRule($attribute,self::RULE_MAX,$rule);
                }
                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                    $rule['match'] = $this->getLabel($rule['match']);
                    $this->addErrorForRule($attribute,self::RULE_MATCH,$rule);
                }

                if ($ruleName === self::RULE_MAX_FILE_SIZE) {
                    $file = new Filesystem();
                    $max_size = $rule['max_size'] ?? false;
                    if($max_size) {
                        if ($file->size($value['tmp_name']) > $max_size) {
                            $error['max_size'] = $max_size / 1000000;
                            $this->addErrorForRule($attribute, self::RULE_MAX_FILE_SIZE, $error);
                        }
                    }

                }

                if ($ruleName === self::RULE_VALID_FILE_TYPE) {
                    $file = new Filesystem();
                    $options = $rule['types'] ?? false;
                    if($options && $value['tmp_name']) {
                        if (!in_array($file->mimeType($value['tmp_name']), $options)) {
                            $this->addErrorForRule($attribute, self::RULE_INVALID_FILE_TYPE, $rule);

                        }
                    }
                }

                if ($ruleName === self::RULE_UPLOADED) {
                    $file = new Filesystem();
                    if(array_key_exists('tmp_name', $value) && $value['tmp_name']) {
                        $file->setMaxSize($rule['max_size'] ?? 0);
                        $file->allowedTypes($rule['types'] ?? []);
                        $options = $rule['types'] ?? false;
                        $path = $file->upload($value);
                        if (!$path) {
                            $this->addErrorForRule($attribute, self::RULE_UPLOADED);
                        }
                        $this->{$attribute} = $path;
                    }
                }

                if ($ruleName === self::RULE_UNIQUE ) {
                    $className = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttr = :attr");
                    $statement->bindValue(":attr",$value);
                    $statement->execute();

                    $record = $statement->fetchObject();

                    if ($record) {
                        $this->addErrorForRule($attribute,self::RULE_UNIQUE,['field' => $this->getLabel($attribute)]);
                    }

                }
                if ($ruleName === self::RULE_EXISTS ) {
                    $className = $rule['class'];
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    $tableName = $className::tableName();
                    $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttr = :attr");
                    $statement->bindValue(":attr",$value);
                    $statement->execute();

                    $record = $statement->fetchObject();

                    if (!$record) {
                        $this->addErrorForRule($attribute,self::RULE_EXISTS,['field' => $this->getLabel($attribute)]);
                    }

                }

            }

        }

        return empty($this->errors);
    }

    private  function addErrorForRule(string $attribute, string $rule, $params = [])
    {
        $message = $this->errorMessages()[$rule] ?? '';
        foreach ($params as $key=>$value) {
            if(is_array($value)) $value = implode(",",$value);
            $message = str_replace("{{$key}}",$value,$message);
        }
        $this->errors[$attribute][] = $message;
    }
    public  function addError(string $attribute, string $message)
    {
        $this->errors[$attribute][] = $message;
    }
    public function errorMessages()
    {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL => 'This field must be a valid email address',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MAX => 'Max length of this field must be {max}',
            self::RULE_MATCH => 'This field must be the same as {match}',
            self::RULE_UNIQUE => 'Record with this {field} already exists',
            self::RULE_NUMBER => 'This field must be a number',
            self::RULE_MAX_FILE_SIZE => 'The size for this file must not be greater than {max_size} MB',
            self::RULE_VALID_FILE_TYPE => 'Only the following file types are allowed: {types}',
            self::RULE_UPLOADED => 'The file couldn\'t be uploaded',
            self::RULE_EXISTS => 'No record with this {field}'
        ];
    }

    public function hasError($attribute)
    {
        return $this->errors[$attribute] ?? false;
    }

    public function getFirstError($attribute)
    {
        return $this->errors[$attribute][0] ?? false;
    }
}