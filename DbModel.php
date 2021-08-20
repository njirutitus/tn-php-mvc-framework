<?php


namespace tn\phpmvc;


use tn\phpmvc\db\Model;
use tn\phpmvc\Application;
use Ramsey\Uuid\Uuid;

abstract class DbModel extends Model
{
    abstract public static function tableName(): string;

    abstract  public function attributes(): array;

    abstract public static function primaryKey(): string;

    public function getToken() : string
    {
        return Uuid::uuid4()->toString();
    }

    public function save() {
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $params = array_map(fn($attr) => ":$attr",$attributes);
        $cols = array_map(fn($attr) => "`$attr`",$attributes);
        $statement = self::prepare("INSERT INTO $tableName (".implode(',',$cols).") 
        VALUES(".implode(',',$params).")");
        foreach ($attributes as $attribute) {
            $statement->bindParam($attribute,$this->{$attribute});
        }
        $statement->execute();
        return true;

    }

    public function update($where) {
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $updatedAttributes = [];
        foreach ($attributes as $attribute) {
            if(is_array($this->{$attribute}) && array_key_exists('tmp_name', $this->{$attribute}) && !$this->{$attribute}['tmp_name']) continue;
            if($this->{$attribute}) {
                array_push($updatedAttributes,$attribute);
            }
        }
        $attributes = $updatedAttributes;
        $params = array_map(fn($attr) => "`$attr`=:$attr",$attributes);

        $filter_attr = array_keys($where);
        $sql = implode("AND ", array_map ( fn($attr) => "$attr = :$attr",$filter_attr));

        $statement = self::prepare("UPDATE $tableName SET ".implode(',',$params).
            " WHERE $sql");
        foreach ($attributes as $attribute) {
            $statement->bindParam($attribute,$this->{$attribute});
        }

        foreach ( $where as $key => $item) {
            $statement->bindValue(":$key",$item);
        }

        $statement->execute();
        return true;

    }

    public static function findOne($where)
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode("AND ", array_map ( fn($attr) => "$attr = :$attr",$attributes));
        $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ( $where as $key => $item) {
           $statement->bindValue(":$key",$item);
        }

        $statement->execute();

        return $statement->fetchObject(static::class);
    }

    public static function deleteOne($where)
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode("AND ", array_map ( fn($attr) => "$attr = :$attr",$attributes));
        $statement = self::prepare("DELETE FROM $tableName WHERE $sql");
        foreach ( $where as $key => $item) {
            $statement->bindValue(":$key",$item);
        }

        $result = $statement->execute();

        return  $result;
    }

    public static function findMany($where=[],$orderBy = '')
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode("AND ", array_map ( fn($attr) => "$attr = :$attr",$attributes));
        if(isset($orderBy))
            $statement = self::prepare("SELECT * FROM $tableName WHERE $sql ORDER BY $orderBy");
        else
            $statement = self::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ( $where as $key => $item) {
            $statement->bindValue(":$key",$item);
        }

        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_CLASS, static::class);
    }

    public static function findAll()
    {
        $tableName = static::tableName();
        $statement = self::prepare("SELECT * FROM $tableName");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_CLASS, static::class);
    }

    public static function prepare($sql)
    {
        return Application::$app->db->pdo->prepare($sql);
    }

    public function count(): int
    {
        return 0;
    }

}