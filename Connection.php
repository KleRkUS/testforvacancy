<?php

class Connection 
{

  private $host = "localhost";
  private $db = "test";
  private $user = "mysql";
  private $pass = "mysql";
  private $charset = "utf8";
  private $pdo;

  public function __construct()
  {

    $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $this->pdo = new PDO($dsn, $this->user, $this->pass, $opt);

  }

  /**
   * Check Existence
   * 
   * Checks if origin string is in database
   * 
   * @param string Origin string
   * 
   * @return boolean
  **/ 

  public function checkExistence($string)
  {

    $stmt = $this->pdo->prepare('SELECT * FROM data WHERE origin LIKE ?');
    $stmt->bindParam(1, $string);
    $stmt->execute();
    $result = $stmt->fetch();

    if (!$result) {
      return false;
    } else {
      return true;
    }

  }

  /**
   * Add Array
   * 
   * Encode array with all replaces of origin string to JSON and add
   * it to Database with origin string associated with it
   * 
   * @param string Origin string
   * @param array Array with all replaces of origin string
   * 
   * @return boolean
  **/

  public function addArray($string, $array)
  {

    $json = json_encode($array, JSON_UNESCAPED_UNICODE);

    //return $json;

    $stmt = $this->pdo->prepare('INSERT INTO data (origin, text) VALUES (?, ?)');
    $stmt->execute(array($string, $json));

    return $result = $stmt->fetch();
  }

}