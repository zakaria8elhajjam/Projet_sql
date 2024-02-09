<?php
include("header.php");
include("Data.php");
?>
<!DOCTYPE html>
<head>
    <link href="cssfile.css" rel = "stylesheet">
    <meta charset="UTF-8">
    <title>Refus</title>
</head>
<body>
    <h1 class="refus">ECHOUE</h1>
    <?php
    if($_GET['refus']=='test'){
        echo "<h3 class='msg'>Le test est déjà fait </h3>";
    }elseif($_GET['refus']=='soutien'){
        echo "<h3 class='msg'>L'étudiant(e) est déjà inscrit dans cette matiere</h3>";
    }
    elseif($_GET['refus']=='Formation'){
        echo "<h3 class='msg'>L'étudiant(e) est déjà inscrit dans cette formation</h3>";
    }
    elseif($_GET['refus']=='langue'){
        echo "<h3 class='msg'>L'étudiant(e) est déjà inscrit dans cette langue</h3>";
    }
    elseif($_GET['refus']=='groupe'){
        echo "<h3 class='msg'>L'étudiant(e) peut inscrire aux 12 groupes  au maximum </h3>";
    }
    elseif($_GET['refus']=='inscription'){
        echo "<h3 class='msg'>L'étudiant(e) ne peut pas s'incrire dans cette matiere pour ce niveau </h3>";
    }
    elseif($_GET['refus']=='nongroupe'){
        echo "<h3 class='msg'>Il n'y a pas de groupe dans cette matière pour ce niveau </h3>";
    }
    
?>
</body>