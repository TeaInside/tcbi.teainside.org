<?php

use Loggers\Daemon as Log;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @package {No Package}
 * @version 0.0.1
 */

class CurlError extends Exception
{
};

abstract class ScraperFoundation
{
  public const TOR_PROXIES = [
    "[2400:6180:0:d1::6a4:a001]:64500",
    "[2400:6180:0:d1::6a4:a001]:64501",
    "[2400:6180:0:d1::6a4:a001]:64502",
    "[2400:6180:0:d1::6a4:a001]:64503",
    "[2400:6180:0:d1::6a4:a001]:64504",
    "[2400:6180:0:d1::6a4:a001]:64505",
    "[2400:6180:0:d1::6a4:a001]:64506",
    "[2400:6180:0:d1::6a4:a001]:64507",
    "[2400:6180:0:d1::6a4:a001]:64508",
    "[2400:6180:0:d1::6a4:a001]:64509",
    "[2400:6180:0:d1::6a4:a001]:64510",
    "[2400:6180:0:d1::6a4:a001]:64511",
    "[2400:6180:0:d1::6a4:a001]:64512",
    "[2400:6180:0:d1::6a4:a001]:64513",
    "[2400:6180:0:d1::6a4:a001]:64514",
    "[2400:6180:0:d1::6a4:a001]:64515",
    "[2400:6180:0:d1::6a4:a001]:64516",
    "[2400:6180:0:d1::6a4:a001]:64517",
    "[2400:6180:0:d1::6a4:a001]:64518",
    "[2400:6180:0:d1::6a4:a001]:64519",
    "[2400:6180:0:d1::6a4:a001]:64520",
    "[2400:6180:0:d1::6a4:a001]:64521",
    "[2400:6180:0:d1::6a4:a001]:64522",
    "[2400:6180:0:d1::6a4:a001]:64523",
    "[2400:6180:0:d1::6a4:a001]:64524",
    "[2400:6180:0:d1::6a4:a001]:64525",
    "[2400:6180:0:d1::6a4:a001]:64526",
    "[2400:6180:0:d1::6a4:a001]:64527",
    "[2400:6180:0:d1::6a4:a001]:64528",
    "[2400:6180:0:d1::6a4:a001]:64529"
  ];

  /**
   * @var int
   */
  protected int $curlTryCount = 3;

  /**
   * @param string $url
   * @param array  $opt
   * @return array
   * @throws \CurlError
   */
  public function curl(string $url, array $opt = []): array
  {
    $tryCount = 0;

  start_curl:
    $ch   = curl_init($url);
    $optf = [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => 120,
      CURLOPT_CONNECTTIMEOUT => 120,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_USERAGENT      => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/37.0.2062.94 Chrome/37.0.2062.94 Safari/537.36",
    ];

    foreach ($opt as $k => $v) {
      $optf[$k] = $v;
    }

    curl_setopt_array($ch, $optf);

    $out = curl_exec($ch);
    if (false === $out) {
      $err = curl_error($ch);
      $ern = curl_errno($ch);
      curl_close($ch);

      $tryCount++;

      $errStr = "[tryCount={$tryCount}] Curl error ({$ern}): {$err}";
      Log::log(2, "Error: %s", $errStr);

      if ($tryCount < $this->curlTryCount) {
        goto start_curl;
      }

      throw new CurlError($errStr);
    }

    $info = curl_getinfo($ch);
    curl_close($ch);

    return [
      "out"  => $out,
      "info" => $info
    ];
  }
};
