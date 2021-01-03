<?php

require __DIR__."/../../autoload.php";

?><!DOCTYPE html>
<html>
<head>
  <title>Data Genome Sequencing</title>
<style type="text/css">
body {
  font-family: Arial;
  background-image: url(/assets/bg.jpg);
  background-size: cover;
  background-attachment: fixed;
}
.main {
  border: 3px solid #000;
  border-radius: 5px;
  margin: auto;
  padding: 20px;
  text-align: center;
  background-color: #ffaa00;
  width: 1200px;
  margin-top: 100px;
}

.sidenav {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 1;
  top:0;
  left: 0;
  background-color: #111;
  overflow-x: hidden;
  transition: 0.5s;
  padding-top: 60px;
}

.sidenav a{
  padding: 8px 8px 8px 32px;
  text-decoration: none;
  font-size: 25px;
  color: #818181;
  display: block;
  transition: 0.3s;
}

.sidenav a:hover{
  color: #f1f1f1;
}

.sidenav .closebtn{
  position: absolute;
  top: 0;
  right: 25px;
  font-size: 36px;
  margin-left: 50px;
}

#main{
  position: left;
  transition: margin-left;
}

@media screen and (max-height: 450px){
  .sidenav {padding-top: 15px;}
  .sidenav a {font-size: 18px;}
}

button {
  cursor: pointer;
}
#tbl_dt {
  margin: auto;
  border-collapse: collapse;
}
#tbl_dt > thead > tr > th {
  padding: 5px;
}
#tbl_dt > tbody > tr > td {
  padding: 2px;
}
.ctn {
  min-width: 100px;
}
#covid19_tbl {
  margin-top: 10px;
  padding: 5px;
  padding-bottom: 40px;
  border: 1px solid #000;
  background-color: #f9eac0;
}
.search_bar {
  margin-bottom: 5px;
}
.tr-odd {
  background-color: #f9df90;
}
.tr-ev {
  background-color: #ffcf56;
}
.tr-odd:hover, .tr-ev:hover {
  background-color: #f4e19c
}

.vtr > td {
  padding-top: 1000px;
}

.page-num {
  color: blue;
  text-decoration: none;
  font-size: 30px;
}
.page-num:hover {
  text-decoration: underline;
  font-size: 30px;
}
.page-cage {
  margin-top: 10px;
  margin-bottom: 10px;
  border: 1px solid #000;
  padding-bottom: 20px;
}
</style>
</head>
<body>
  <div class="main">
    <a href="/"><button>Back to Index</button></a>
    <div id="covid19_tbl">
      <h2>Data Genome Sequencing</h2>
      <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <a href="/diff_gen_seq">Diff Gen Seq</a>
      </div>
      <div id="main">
        <span style="font-size:25px;cursor:pointer" onclick="openNav()">&#9776;Menu</span>
      </div>

      <script>
        function openNav(){
          document.getElementById("mySidenav").style.width="250px";
          document.getElementById("main").style.marginLeft="250px";
        }

        function closeNav(){
          document.getElementById("mySidenav").style.width="0";
          document.getElementById("main").style.marginLeft="0";
        }
        
      </script>
      <div class="search_bar">
       
        <!-- <select>
          <option>Semua Database</option>
          <option>Assembly</option>
          <option>Biocollections</option>
          <option>BioProject</option>
          <option>BioSample</option>
          <option>BioSystem</option>
          <option>Books</option>
          <option>ClinVar</option>
          <option>Conserved Domains</option>
          <option>dbGaP</option>
          <option>dbVar</option>
          <option>Gene</option>
          <option>Genome</option>
          <option>GEO DataSets</option>
          <option>GEO Profiles</option>
          <option>GTR</option>
          <option>HomoloGene</option>
          <option>Identical Protein Groups</option>
          <option>MedGen</option>
          <option>MeSH</option>
          <option>NCBI Web Site</option>
          <option>NLM Catalog</option>
          <option>Nucleotide</option>
          <option>OMIM</option>
          <option>PMC</option>
          <option>PopSet</option>
          <option>Protein</option>
          <option>Protein Clusters</option>
          <option>Protein Family Models</option>
          <option>PubChem BioAssay</option>
          <option>PubChem Compound</option>
          <option>PubChem Substance</option>
          <option>PubMed </option>
          <option>SNP</option>
          <option>Sparcle</option>
          <option>SRA</option>
          <option>Structure</option>
          <option>Taxonomy</option>
          <option>ToolKit</option>
          <option>ToolKitAll</option>
          <option>ToolKitBolkgh</option>
        </select> -->
<?php
$page  = isset($_GET["page"]) && is_string($_GET["page"]) ? (int)$_GET["page"] : 1;
$limit = 10;
//$limit = isset($_GET["limit"]) && is_numeric($_GET["limit"]) ? $_GET["limit"] : 10;

if ($page < 1) $page = 1;

$offset = $limit * ($page - 1);

if ($isSearch = isset($_GET["q"]) && is_string($_GET["q"])) {
  $_GET["q"] = trim($_GET["q"]);
  $isSearch  = $isSearch && ($_GET["q"] !== "");
}

if ($isSearch) {
  $q   = "%".strtolower($_GET["q"])."%";

  $pdo = DB::pdo();
  $exe = [$q, $q, $q, $q, $q, $q, $q];

  $st  = $pdo->prepare(
"SELECT COUNT(1) FROM seqdata
WHERE
locus LIKE ? OR
definition LIKE ? OR
accession LIKE ? OR
version LIKE ? OR
keywords LIKE ? OR
sources LIKE ? OR
`references` LIKE ?");
  $st->execute($exe);
  $numOfData = (int)$st->fetch(PDO::FETCH_NUM)[0];
  $numOfPage = (int)ceil($numOfData / $limit);

  $st  = $pdo->prepare(
"SELECT * FROM seqdata
WHERE
locus LIKE ? OR
definition LIKE ? OR
accession LIKE ? OR
version LIKE ? OR
keywords LIKE ? OR
sources LIKE ? OR
`references` LIKE ?
LIMIT {$limit} OFFSET {$offset}"
);
  $st->execute($exe);
  $q = "q=".urlencode($_GET["q"])."&";

} else {
  $pdo = DB::pdo();
  $st  = $pdo->prepare("SELECT COUNT(1) FROM seqdata");
  $st->execute();
  $numOfData = (int)$st->fetch(PDO::FETCH_NUM)[0];
  $numOfPage = (int)ceil($numOfData / $limit);
  $st = $pdo->prepare("SELECT * FROM seqdata LIMIT {$limit} OFFSET {$offset}");
  $st->execute();
  $_GET["q"] = "";
  $q = "";
}

?>
        <form method="get" action="?">
          Search Data:
          <input type="text" name="q" value="<?php echo e($_GET["q"]); ?>">
          <button>Cari</button>
        </form>
<?php ob_start(); ?>
        <div class="page-cage">
        <h3>Paginator</h3>
        <?php for ($i = 1; $i <= $numOfPage; $i++): ?>
          <?php if ($page === $i): ?>
            <a class="page-num" href="?<?php echo e($q); ?>page=<?php echo $i; ?>"><b><?php echo $i; ?></b></a>&nbsp;&nbsp;
          <?php else: ?>
            <a class="page-num" href="?<?php echo e($q); ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>&nbsp;&nbsp;
          <?php endif; ?>
        <?php endfor; ?>
        </div>
<?php echo $x = ob_get_clean(); ?>
        <table border="1" id="tbl_dt">
          <thead>
            <tr>
              <th>ID</th>
              <th>NCBI ID</th>
              <th>Locus</th>
              <th>Definition</th>
              <th>Accession</th>
              <th>Version</th>
              <th>Keywords</th>
              <th>Sources</th>
              <th>References</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $st->fetch(PDO::FETCH_ASSOC)): ?>
              <tr>
                <td><?php echo e($r["id"]); ?></td>
                <td><?php echo e($r["ncbi_id"]); ?></td>
                <td><?php echo e($r["locus"]); ?></td>
                <td><?php echo e($r["definition"]); ?></td>
                <td><?php echo e($r["accession"]); ?></td>
                <td><?php echo e($r["version"]); ?></td>
                <td><?php echo e($r["keywords"]); ?></td>
                <td><?php echo e($r["sources"]); ?></td>
                <td><?php echo e($r["references"]); ?></td>
                <td><a href="/fasta?id=<?php echo e($r["id"]); ?>">View</a></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
<?php echo $x; ?>
      </div>
    </div>
  </div>
</body>
</html>

