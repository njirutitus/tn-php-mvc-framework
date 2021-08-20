<?php


namespace tn\phpmvc\db;


use tn\phpmvc\Application;

class Database
{
    public \PDO $pdo;

    /**
     * Database constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $dsn = $config['dsn'] ?? '';
        $user = $config['user'] ?? '';
        $password = $config['password'] ?? '';
        $this->pdo = new \PDO($dsn,$user,$password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
    }

    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $files = scandir(Application::$ROOT_DIR.'/migrations');
        $toApplyMigrations = array_diff($files,$appliedMigrations);
        foreach ($toApplyMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }

            require_once Application::$ROOT_DIR.'/migrations/'.$migration;
            $classname = pathinfo($migration,PATHINFO_FILENAME);
            $instance = new $classname();
            $this->log("Applying migration $migration");
            $instance->up();
            $this->log("Applied migration $migration");
            $newMigrations[] = $migration;
        }

        if(!empty($newMigrations)) {
            $this->saveMigrations($newMigrations);
        } else {
            $this->log("All migrations are saved");
        }

    }

    public function  createMigrationsTable(){
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id SERIAL,
            migration TINYTEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=INNODB;");
    }

    private function getAppliedMigrations()
    {
        $statement = $this->pdo->prepare("SELECT migration from migrations");
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function saveMigrations(array $migrations)
    {
        $str = implode(",", array_map(fn($m) => "('$m')", $migrations));
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES $str ");
        $statement->execute();
    }

    protected function log($message)
    {
        echo '['.date('Y-m-d H:i:s').'] - '.$message.PHP_EOL;
    }

    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);

    }

    public function createSuperAdmin()
    {
        $user = new Application::$app->userClass;
        $user->firstname = readline('First Name: ');
        $user->lastname = readline("Last Name: ");
        $user->email = readline("Email: ");
        $user->password = readline('Password: ');
        $user->confirmPassword = readline('Confirm Password: ');
        $user->status = 1;
        $user->is_staff = 1;
        $user->is_super_admin = 1;
        if($user->validate() && $user->register()) {
            echo "Super Admin Created Successfully\n";
        }
        else {
            var_dump($user->errors);
            echo "Errors:\n";
            foreach ($user->errors as $k=>$v) {
                echo $user->labels()[$k].":\n";
                foreach ($v as $value)
                    echo "$value\n";
            }
        }
    }

}