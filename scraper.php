<?php

require __DIR__."/autoload.php";

if (!isset($argv[1])) {
  echo "argv[1] required!\n";
  exit(0);
}

$sc = new NCBIScraper;
$sc->execute($argv[1]);
