<?php

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @package \Loggers
 * @version 0.0.1
 */

namespace Loggers {

class Daemon
{
  /**
   * @var array
   */
  private static $handlers = [];

  /**
   * @var int
   */
  private static int $logLevel = 5;

  /**
   * Constructor.
   */
  private function __construct()
  {
  }

  /**
   * @param resource $handle
   * @return void
   */
  public static function addHandler($handle): void
  {
    self::$handlers[] = $handle;
  }

  /**
   * @param int $level
   * @return void
   */
  public static function setLogLevel(int $level): void
  {
    self::$logLevel = $level;
  }

  /**
   * @param int    $level
   * @param string $format
   * @param mixed  $args
   * @return void
   */
  public static function log(int $level, string $format, ...$args): void
  {
    if ($level <= self::$logLevel) {
      $now = date("Y-m-d H:i:s");
      foreach (self::$handlers as $handle) {
        flock($handle, LOCK_SH);
        fwrite($handle,
               sprintf("[%s]: %s\n", $now, vsprintf($format, $args)));
        flock($handle, LOCK_UN);
      }
    }
  }
};

} /* namespace Loggers */
