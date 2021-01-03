<?php

if (isset($_POST["input1"], $_POST["input2"])
    && is_string($_POST["input1"]) && is_string($_POST["input2"])) {
  similar_text($_POST["input1"], $_POST["input2"], $sim);
  $post = true;
} else {
  $post = false;
}

?><!DOCTYPE html>
<html>
<head>
  <title>Diif Gen Seq</title>
  <style type="text/css">
    body {
      font-family: Arial;
      background-image: url(assets/bg.jpg);
      background-size: cover;
      background-attachment: fixed;
    }
    .input-cage {
      padding: 10px;
      width: 1100px;
      margin: auto;
      background-color: #beff89;
      text-align: center;
      border: 2px solid #000;
      margin-top: 40px;
      padding-bottom: 30px;
      margin-bottom: 20px;
    }
    .input-data {
      min-width: 100px;
      margin: auto;
      display: inline-block;
    }
    .gsback-cage {
      width: 300px;
      height: 10px;
      margin: auto;
      margin-top: 30px;
      text-align: center;
    }
    button {
      cursor: pointer;
    }
    .btn-cage {
      margin-top: 20px;
    }
    <?php if ($post): ?>
    .result {
      padding: 10px;
      width: 400px;
      margin: auto;
      background-color: #beff89;
      text-align: center;
      border: 2px solid #000;
      margin-top: 40px;
      padding-bottom: 30px;
      margin-bottom: 20px;
    }
    .diff-table {
      border-collapse: collapse;
      margin: auto;
    }
    .diff-table td {
      padding: 10px;
      padding-right: 20px;
      padding-left: 20px;
    }
    <?php endif; ?>
  </style>
</head>
<body>
  <div class="gsback-cage">
    <a href="gsequence.php"><button>Back to GSequence</button></a>
  </div>
  <?php if ($post): ?>
    <div class="result">
    <h2>Diff Gen Seq</h2>
    <table class="diff-table" border="1">
      <tbody>
      <tr><td><b>Similar Seq</b></td><td><?php echo $sim; ?></td></tr>
      <tr><td><b>Diff Eq</b></td><td><?php echo 100 - $sim; ?></td></tr>
      </tbody>
      <tr><td colspan="2"><a href="?"><button>Rediff</button></a></td></tr>
    </table>
    </div>
  <?php else: ?>
    <div class="input-cage">
    <h2>Diff Gen Seq</h2>
    <form action="?action=1" method="post">
      <div class="input-data">
        <h3>Input Data 1</h3>
        <textarea name="input1" style="resize:none;width:530px;height:419px;" required="1"></textarea>
      </div>
      <div class="input-data">
        <h3>Input Data 2</h3>
        <textarea name="input2" style="resize:none;width:530px;height:419px;" required="1"></textarea>
      </div>
      <div class="btn-cage">
        <button>Blast</button>
      </div>
    </form>
    </div>
  <?php endif; ?>
</body>
</html>