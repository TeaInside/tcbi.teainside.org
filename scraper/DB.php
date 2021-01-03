<?php

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @package {No Package}
 * @version 0.0.1
 */
class DB
{
  /**
   * @var \PDO
   */
  private ?PDO $pdo = NULL;

  /**
   * @var \DB
   */
  private static ?DB $self = NULL;

  /**
   * Constructor.
   */
  private function __construct()
  {
    $pdo = new PDO(...PDO_PARAM);
    $pdo->exec("SET NAMES utf8mb4");
    $this->pdo = $pdo;
  }

  /**
   * @return \DB
   */
  public static function getInstance(): \DB
  {
    return self::$self ? self::$self : (self::$self = new self);
  }

  /**
   * @return \PDO
   */
  public static function pdo(): \PDO
  {
    return self::getInstance()->pdo;
  }

  /**
   * @return \PDO
   */
  public static function rePdo(): \PDO
  {
    self::$self = new self;
    return self::getInstance()->pdo;
  }
};
