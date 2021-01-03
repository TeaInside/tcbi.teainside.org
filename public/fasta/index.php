<?php

require __DIR__."/../../autoload.php";

if (!isset($_GET["id"]) || !is_string($_GET["id"])) {
  header("Location: gsequence.php");
  exit;
}

$pdo = DB::pdo();
$st  = $pdo->prepare(
"SELECT a.*, b.data FROM seqdata AS a INNER JOIN fasta AS b ON a.id = b.seqdata_id
WHERE a.id = ?"
);
$st->execute([$_GET["id"]]);
$r = $st->fetch(PDO::FETCH_ASSOC);
if (!$r) {
  ?><html><script type="text/javascript">alert("Not Found!");window.location="gsequence.php";</script></html><?php
  exit;
}

?><!DOCTYPE html>
<html>
<head>
  <title><?php echo e($r["definition"]); ?></title>
<style type="text/css">
body {
  font-family: Arial;
  background-image: url(assets/bg.jpg);
  background-size: cover;
  background-attachment: fixed;
}
.fasta-cage {
  background-color: #e3f0f7;
  min-width: 300px;
  margin: auto;
  margin-top: 30px;
  margin-bottom: 300px;
  padding: 10px;
  text-align: center;
  word-wrap: break-word;
}
.gsback-cage {
  width: 300px;
  height: 10px;
  margin: auto;
  margin-top: 30px;
  text-align: center;
}
.table-data {
  padding: 25px;
  border: 1px solid #000;
  margin: auto;
  text-align: left;
}
td {
  vertical-align: top;
}
button {
  cursor: pointer;
}
</style>
</head>
<body>
  <div class="gsback-cage">
    <a href="/gsequence"><button>Back to GSequence</button></a>
  </div>
  <div class="fasta-cage">
    <h1>Data</h1>
    <table class="table-data">
      <thead></thead>
      <tbody>
        <tr><td>ID</td><td>:</td><td><?php echo e($r['id']); ?></td></tr>
        <tr><td>NCBI ID</td><td>:</td><td><?php echo e($r['ncbi_id']); ?></td></tr>
        <tr><td>Locus</td><td>:</td><td><?php echo e($r['locus']); ?></td></tr>
        <tr><td>Definition</td><td>:</td><td><?php echo e($r['definition']); ?></td></tr>
        <tr><td>Accession</td><td>:</td><td><?php echo e($r['accession']); ?></td></tr>
        <tr><td>Version</td><td>:</td><td><?php echo e($r['version']); ?></td></tr>
        <tr><td>Keywords</td><td>:</td><td><?php echo e($r['keywords']); ?></td></tr>
        <tr><td>Sources</td><td>:</td><td><pre><?php echo e($r['sources']); ?></pre></td></tr>
        <tr><td>References</td><td>:</td><td><pre><?php echo e($r['references']); ?></pre></td></tr>
        <tr><td>Fasta</td><td>:</td><td>
          <pre><?php echo trim($r["data"]); ?></pre>
        </td></tr>
      </tbody>
    </table>
  </div>
</body>
</html>