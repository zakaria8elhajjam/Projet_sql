<?php
include("Data.php");
check($database);
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="cssfile.css">
</head>

<body>
  <?php include("header.php"); ?>
  <h1>LISTE D'ETUDIANTS</h1>
  <?php
  if (isset($_POST['show'])) {
    $sql = "SELECT `IdPersonne` FROM `Appartenance` WHERE `IdGrp` ='{$_POST['IdGrp']}'";
    $result = mysqli_query($database, $sql);
    echo "<div class='tableRow header tableHeader' style='width: 1540px;'>";
    echo "<div class='tableCell'>Nom</div>";
    echo "<div class='tableCell'>Prenom</div>";
    echo "<div class='tableCell'>Tele</div>";
    echo "</div>";
    $num = 0;
    while ($list = mysqli_fetch_assoc($result)){
      $num++;
      $css = $num % 2 + 1;
      $etudiant = "SELECT `Nom`, `Prenom`, `tele` FROM `Personne` WHERE `idPersonne`='{$list['IdPersonne']}'";
      $elem = mysqli_fetch_assoc(mysqli_query($database, $etudiant));
      echo "<div class='tableRow tableProf" . $css . "'>";
      echo "<div class='tableCell'>" . $elem['Nom'] . "</div>";
      echo "<div class='tableCell'>" . $elem['Prenom']  . "</div>";
      echo "<div class='tableCell'>" . $elem['tele'] . "</div>";
      echo "</div>";
    }
  }
  ?>
  </form>
</body>
</html>