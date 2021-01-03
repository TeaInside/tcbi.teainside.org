<?php

require __DIR__."/config.php";

/**
 * @param string $class
 * @return void
 */
function projectInternalMainAutoload($class)
{
  $class = BASE_PATH."/scraper/".str_replace("\\", "/", $class).".php";
  if (file_exists($class)) {
    require $class;
  }
}

spl_autoload_register("projectInternalMainAutoload");

$composerAutoload = __DIR__."/vendor/autoload.php";
file_exists($composerAutoload) and require $composerAutoload;


$sc = new NCBIScraper;
$sc->execute();
