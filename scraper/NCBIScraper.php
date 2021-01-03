<?php

use Loggers\Daemon as Log;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @package {No Package}
 * @version 0.0.1
 */

class NCBIScraper extends ScraperFoundation
{
  /**
   * @var string
   */
  private string $proxy = "";

  /**
   * @var bool
   */
  private bool $useProxy = true;

  /**
   * @return void
   */
  public function execute()
  {
    Log::addHandler(STDOUT);

    $infoQuery  = "INSERT IGNORE INTO `seqdata` (`ncbi_id`, `locus`, `definition`, `accession`, `version`, `keywords`, `sources`, `references`) VALUES (:ncbi_id, :locus, :definition, :accession, :version, :keywords, :sources, :references);";
    $fastaQuery = "INSERT IGNORE INTO fasta (seqdata_id, data) VALUES (:seqdata_id, :data);";

    $c = count(parent::TOR_PROXIES);
    $i = 0;
    $this->proxy = parent::TOR_PROXIES[$i % $c];

    $pdo     = DB::pdo();
    $stInfo  = $pdo->prepare($infoQuery);
    $stFasta = $pdo->prepare($fastaQuery);
    $listId  = $this->search("covid19");

    foreach ($listId as $ncbiId) {

      try {
        Log::log(1, "Scraping {$ncbiId}...");
        $info  = $this->getInfo($ncbiId);
        $fasta = $this->getFasta($ncbiId);

        $pdo->beginTransaction();

        $stInfo->execute($info);
        $stFasta->execute([
          "seqdata_id" => $pdo->lastInsertId(),
          "data" => $fasta
        ]);

        $pdo->commit();

        Log::log(1, "Insert OK {$ncbiId}");
      } catch (\PDOException $e) {
        $pdo->rollback();
        throw $e;
      }
      $i++;
    }
  }

  /**
   * @param int $id
   * @return string
   */
  public function getFasta(int $id): string
  {
    if ($this->useProxy) {
      $opt = [
        CURLOPT_PROXY => $this->proxy,
        CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5
      ];
    } else {
      $opt = [];
    }

    return $this->curl("https://www.ncbi.nlm.nih.gov/sviewer/viewer.fcgi?id={$id}&db=nuccore&report=fasta&extrafeat=null&conwithfeat=on&hide-cdd=on&retmode=html&withmarkup=on&tool=portal&log\$=seqview&maxdownloadsize=1000000", $opt)["out"];
  }

  /**
   * @param int $id
   * @return array
   */
  public function getInfo(int $id): array
  {
    if ($this->useProxy) {
      $opt = [
        CURLOPT_PROXY => $this->proxy,
        CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5
      ];
    } else {
      $opt = [];
    }

    $ret = [
      "ncbi_id"      => $id,
      "locus"        => NULL,
      "definition"   => NULL,
      "accession"    => NULL,
      "version"      => NULL,
      "keywords"     => NULL,
      "sources"      => NULL,
      "references"   => NULL,
    ];
    $out = $this->curl("https://www.ncbi.nlm.nih.gov/sviewer/viewer.fcgi?id={$id}&db=nuccore&report=genbank&conwithfeat=on&withparts=on&hide-cdd=on&retmode=json&withmarkup=on&tool=portal&log\$=seqview&maxdownloadsize=1000000", $opt)["out"];

    if (preg_match("/LOCUS.+?(.+?)(?:\\n|$|<)/", $out, $m)) {
      do {
        $m[1] = str_replace("  ", " ", $m[1], $n);
      } while ($n > 0);
      $ret["locus"] = trim(strip_tags($m[1]));
    }

    if (preg_match("/DEFINITION.+?(.+?)(?:\\n|$|<)/", $out, $m)) {
      do {
        $m[1] = str_replace("  ", " ", $m[1], $n);
      } while ($n > 0);
      $ret["definition"] = trim(strip_tags($m[1]));
    }


    if (preg_match("/ACCESSION.+?(.+?)(?:\\n|$|<)/", $out, $m)) {
      do {
        $m[1] = str_replace("  ", " ", $m[1], $n);
      } while ($n > 0);
      $ret["accession"] = trim(strip_tags($m[1]));
    }


    if (preg_match("/VERSION.+?(.+?)(?:\\n|$|<)/", $out, $m)) {
      do {
        $m[1] = str_replace("  ", " ", $m[1], $n);
      } while ($n > 0);
      $ret["version"] = trim(strip_tags($m[1]));
    }

    if (preg_match("/KEYWORDS.+?(.+?)(?:\\n|$|<)/", $out, $m)) {
      do {
        $m[1] = str_replace("  ", " ", $m[1], $n);
      } while ($n > 0);
      $ret["keywords"] = json_encode(explode(" ", trim(strip_tags($m[1]))));
    }


    if (preg_match("/(SOURCE.+?)\\n[A-Z]/s", $out, $m)) {
      $ret["sources"] = trim(strip_tags($m[1]));
    }

    if (preg_match_all("/(REFERENCE.+?)\\n(<|[A-Z])/s", $out, $m)) {
      $ret["references"] = $m[1][0];
    }

    return $ret;
  }

  /**
   * @param string $term
   * @return array
   */
  public function search(string $term): array
  {
    if ($this->useProxy) {
      $opt = [
        CURLOPT_PROXY => $this->proxy,
        CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5
      ];
    } else {
      $opt = [];
    }

    $term = urlencode($term);
    $out  = $this->curl("https://www.ncbi.nlm.nih.gov/nuccore/?term={$term}", $opt)["out"];

    // file_put_contents("data.txt", $out);
    // $out = file_get_contents("data.txt");

    if (preg_match_all("/<dt>GI: <\/dt><dd>(\d+)/", $out, $m)) {
      return $m[1];
    }

    return [];
  }

  private const QUERY_STRING = "term=\$term_data&EntrezSystem2.PEntrez.Nuccore.Sequence_PageController.PreviousPageName=results&EntrezSystem2.PEntrez.Nuccore.Sequence_Facets.FacetsUrlFrag=filters%3D&EntrezSystem2.PEntrez.Nuccore.Sequence_Facets.FacetSubmitted=false&EntrezSystem2.PEntrez.Nuccore.Sequence_Facets.BMFacets=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.sPresentation=docsum&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.sPageSize=20&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.sSort=none&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.FFormat=docsum&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.FSort=&coll_start=21&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.CSFormat=fasta_cds_na&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.GFFormat=gene_fasta&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.Db=nuccore&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.QueryKey=3&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.CurrFilter=all&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.ResultCount=81821&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.ViewerParams=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.FileFormat=docsum&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.LastPresentation=docsum&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.Presentation=docsum&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.PageSize=20&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.LastPageSize=20&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.Sort=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.LastSort=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.FileSort=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.Format=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.LastFormat=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.PrevPageSize=20&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.PrevPresentation=docsum&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.PrevSort=&CollectionStartIndex=1&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_ResultsController.ResultCount=81821&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_ResultsController.RunLastQuery=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_ResultsController.AccnsFromResult=&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Entrez_Pager.cPage=2&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Entrez_Pager.CurrPage=1&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Entrez_Pager.cPage=2&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.sPresentation2=docsum&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.sPageSize2=20&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.sSort2=none&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.TopSendTo=genefeat&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.FFormat2=docsum&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.FSort2=&coll_start2=21&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.CSFormat2=fasta_cds_na&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_DisplayBar.GFFormat2=gene_fasta&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_MultiItemSupl.Taxport.TxView=list&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_MultiItemSupl.Taxport.TxListSize=5&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_MultiItemSupl.RelatedDataLinks.rdDatabase=rddbto&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Sequence_MultiItemSupl.RelatedDataLinks.DbName=nuccore&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Discovery_SearchDetails.SearchDetailsTerm=Severe%5BAll+Fields%5D+AND+acute%5BAll+Fields%5D+AND+respiratory%5BAll+Fields%5D&EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.HistoryDisplay.Cmd=PageChanged&EntrezSystem2.PEntrez.DbConnector.Db=nuccore&EntrezSystem2.PEntrez.DbConnector.LastDb=nuccore&EntrezSystem2.PEntrez.DbConnector.Term=\$term_data&EntrezSystem2.PEntrez.DbConnector.LastTabCmd=&EntrezSystem2.PEntrez.DbConnector.LastQueryKey=3&EntrezSystem2.PEntrez.DbConnector.IdsFromResult=&EntrezSystem2.PEntrez.DbConnector.LastIdsFromResult=&EntrezSystem2.PEntrez.DbConnector.LinkName=&EntrezSystem2.PEntrez.DbConnector.LinkReadableName=&EntrezSystem2.PEntrez.DbConnector.LinkSrcDb=&EntrezSystem2.PEntrez.DbConnector.Cmd=PageChanged&EntrezSystem2.PEntrez.DbConnector.TabCmd=&EntrezSystem2.PEntrez.DbConnector.QueryKey=&p%24a=EntrezSystem2.PEntrez.Nuccore.Sequence_ResultsPanel.Entrez_Pager.Page&p%24l=EntrezSystem2&p%24st=nuccore";
};
