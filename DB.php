<?php
/*
class DB {

      private static $dbh = null;

      private static function connect() {

        try {
            self::$dbh = new PDO('mysql:dbname='.DB_NAME.';host=localhost', USER, PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
            self::$dbh->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        } catch (PDOException $e) {
            self::close_db();
            throw new Exception($e->getMessage());
        }
        return self::$dbh;
      }
      public static function close_db() {
          self::$dbh = null;
      }
      public static function prepare($queryString) {
          try {
                $db_handle = self::connect();
                $dbh = $db_handle->prepare($queryString);
                return $dbh;
          } catch(PDOException $e) {
                throw new Exception($e->getMessage());
                self::close_db();
          }
      }
      public static function execute($stmt, $parametri = null) {
          try {
              $stmt->execute($parametri);
          } catch(PDOException $e) {
              throw new Exception($e->getMessage());
              self::close_db();
          }
      }
      public static function getAll($stmt, $parametri = null, $fetch = PDO::FETCH_ASSOC) {
          try {
              self::execute($stmt, $parametri);
              $result = $stmt->fetchAll($fetch);
          } catch(PDOException $e) {
              throw new Exception($e->getMessage());
              self::close_db();
          }
          return $result;
      }
      public static function LastId()
      {
        try {
            self::connect()->lastInsertId();
        }
        catch(PDOException $e)
        {
            throw new Exception($e->getMessage());
        }

      }
      public static function getFirst($stmt,$parametri = null, $fetch = PDO::FETCH_ASSOC) {
          try {
              self::execute($stmt, $parametri);
              $result = $stmt->fetch($fetch);
             
          } catch(PDOException $e) {
              throw new Exception($e->getMessage());
              self::close_db();
          }
          return $result;
      }
}
*/

class DB {
    public $dbh;   // handle of the db connexion
    private static $dsn  = "sqlite:trips.sqll"; //'mysql:host=127.0.0.1;dbname=mydb';
    private static $user = 'myuser';
    private static $pass = 'mypassword';


    public function __construct () {
      $this->dbh = new PDO(self::$dsn,self::$user,self::$pass);
    }

    public static function getInstance(){
        if(!isset(self::$instance)){
            $object= __CLASS__;
            self::$instance=new $object;
        }
        return self::$instance;
    }

    // others global functions
    
    public static function get_userid( $ui ) {
      $stmt = $this->dbh->prepare('select userid from users where userid = :userid');
      $stmt->execute( array('userid' => $ui) );
      $result = $stmt->fetchAll();
      return $result;
    }
}
/*
$sql = "select login, email from users where id = :id";

try {
    $core = Core::getInstance();
    $stmt = $core->dbh->prepare($sql);
    $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $o = $stmt->fetch(PDO::FETCH_OBJ);
        // blablabla....
        */
class Foo /*extends PDO*/ {
    public $dbh;

    public function __construct() {
        $dbh = new PDO(/*...*/);
    }

    public function bar() {
        $this->dbh->prepare('SELECT * FROM table');
        return $this->dbh->execute();
    }
}

class DbConnection extends PDO { 
   // eigen functies 
} 
class DbSingleton { 
    private static $_db = null; 
    public static function getInstance() { 
        if (is_null(self::_db)) { 
            self::_db = new DbConnection(); 
        } 
        return self::_db; 
    } 
} 


class MyPDODB extends PDO
{
    // Prevent unconfigured PDO instances!
    private function __construct($config)
    {
        $dsn = sprintf('mysql:dbname=%s;host=%s;port=%d;', $config['database'], $config['hostname'], $config['port']);
        parent::__construct($dsn, $config['username'], $config['password']);        
    }

    public static function loadDB($config_in = null)
    {
        static $last_config = null;

        if (!is_null($config_in))
        {
            self::validateConfig($config_in);
            $config = $config_in;
            if (!isset($config['isTemp']) || $config['isTemp'] !== true)
            {
                $last_config = $config;
            }
        }
        else
        {
            if (!is_null($last_config)) { $config = $last_config; }
            else throw new MyDBException("No config provided");
        }

        return new MyPDODB($config);
    }
}
$db = MyPDODB::loadDB();
$db->prepare($sql);
$db->execute();
?>