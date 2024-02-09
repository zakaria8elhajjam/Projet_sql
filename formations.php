<?php include("header.php");
include("Data.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="cssfile.css" rel = "stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<?php
 check($database);
 if (!empty($_POST['Nom']) && !empty($_POST['Prenom']) && !empty($_POST['telephone']) && !empty($_POST['Niveau']) && !empty($_POST['matiere'])) {
    if ((searchPerson($database, $_POST['Nom'], $_POST['Prenom'], $_POST['telephone'])) != 0) {
        //la personne existe dans data base
        $idPersonne = searchId($database, $_POST['Nom'], $_POST['Prenom'], $_POST['telephone']);
        if(max_inscription($database,$idPersonne)){
        if ($_POST['isTest'] == "test" && searchtest($database, $_POST['Nom'], $_POST['Prenom'], $_POST['telephone']) == 0) {
            if (searchmatiere($database, $idPersonne, $_POST['matiere'], $_POST['Niveau']) == false) {
                //INSCRIRE AU TEST 
                $idGroupe = searchgrp($database, $_POST['Niveau'], $_POST['matiere'],$idPersonne);
                $insertAppartenance = "INSERT INTO `Appartenance` (`IdPersonne`, `IdGrp`, `NbrAbscence`, `NbrSeance`) VALUES ('{$idPersonne}', '{$idGroupe}', '0', '1')";
                mysqli_query($database, $insertAppartenance);
                datedebut($database, $idGroupe, $idPersonne);
                $insert = "INSERT INTO `Suivie`(`IdPersonne`,`NomFormation`)VALUES($idPersonne,'{$_POST['isTest']}')";
                mysqli_query($database, $insert);
                header("Location: Recu.php?nom=" . $_POST['Nom'] . "&prenom=" . $_POST['Prenom'] . "&telephone=" . $_POST['telephone'] . "&matiere=" . $_POST['matiere'] . "&niveau=" . $_POST['Niveau'] . "&nbrseance=" .'1' . "&istest=" . $_POST['isTest']);
                exit();
            } else {
                header("Location:refus.php?refus=Formation");
                exit();
            }
        } else if ($_POST['isTest'] == "test" && searchtest($database, $_POST['Nom'], $_POST['Prenom'], $_POST['telephone']) != 0) {
            //REFUSER LA DEMANDE DE FAIRE UN TEST,L'ETUDIANT DEJA FAIT UN TEST
            header("Location:refus.php?refus=test");
            exit();
        }
        if ($_POST['isTest'] == "Formation") { //on ne s'interesse pas si l'etudiant a deja inscrit dans la mm matiere puisque cette inscri sera dans un nv grp 
            if (searchmatiere($database, $idPersonne, $_POST['matiere'], $_POST['Niveau']) == false) {
                $idGroupe = searchgrp($database, $_POST['Niveau'], $_POST['matiere'],$idPersonne);
                $insertAppartenance = "INSERT INTO `Appartenance` (`IdPersonne`, `IdGrp`, `NbrAbscence`, `NbrSeance`) VALUES ('{$idPersonne}', '{$idGroupe}', '0', '{$_POST['NbrSeance']}')";
                mysqli_query($database, $insertAppartenance);
                datedebut($database, $idGroupe, $idPersonne);
                $sql = "SELECT COUNT(`NomFormation`) AS counte FROM `Suivie` WHERE `IdPersonne`=$idPersonne AND NomFormation='{$_POST['isTest']}'"; // tous les formation dont l'etudiant est inscrit
                $formations = mysqli_fetch_assoc(mysqli_query($database, $sql));
                if ($formations['counte'] == 0) {
                    $insert = "INSERT INTO `Suivie`(`IdPersonne`,`NomFormation`)VALUES( $idPersonne,'{$_POST['isTest']}')";
                    mysqli_query($database, $insert);
                }
                header("Location: Recu.php?nom=" . $_POST['Nom'] . "&prenom=" . $_POST['Prenom'] . "&telephone=" . $_POST['telephone'] . "&matiere=" . $_POST['matiere'] . "&niveau=" . $_POST['Niveau'] . "&nbrseance=" . $_POST['NbrSeance'] . "&istest=" . $_POST['isTest']);
                exit();
            } else {
                header("Location:refus.php?refus=Formation");
                exit();
            }
        }
        else {
            header("Location:refus.php?refus=groupe");
            exit();
        }
        }
    } else { //etudiant n'existe pas dans le systeme
        $nbrseance=$_POST['NbrSeance'];
        $newid = IdGenerator($database, 'personne');
        $insertPersonne = "INSERT INTO `Personne`(`idPersonne`,`Nom`,`Prenom`,`tele`) VALUES ('{$newid}','{$_POST['Nom']}','{$_POST['Prenom']}','{$_POST['telephone']}') ";
        mysqli_query($database, $insertPersonne);
        $insert = "INSERT INTO `Suivie`(`IdPersonne`,`NomFormation`)VALUES($newid,'{$_POST['isTest']}')";
        mysqli_query($database, $insert);
        $idGroupe = searchgrp($database, $_POST['Niveau'], $_POST['matiere'],$idPersonne);
        if($_POST['isTest']=="test"){
            $nbrseance=1;
        }
        $insertAppartenance = "INSERT INTO `Appartenance` (`IdPersonne`, `IdGrp`, `NbrAbscence`, `NbrSeance`) VALUES ('{$newid}', '{$idGroupe}', '0', '{$nbrseance}')";
        mysqli_query($database, $insertAppartenance);
        datedebut($database, $idGroupe, $newid);
        header("Location: Recu.php?nom=" . $_POST['Nom'] . "&prenom=" . $_POST['Prenom'] . "&telephone=" . $_POST['telephone'] . "&matiere=" . $_POST['matiere'] . "&niveau=" . $_POST['Niveau'] . "&nbrseance=" .  $nbrseance . "&istest=" . $_POST['isTest']);
        exit();
    }
}
?>
<body>
<h1>INSCRIPTION AU FORMATION</h1>
    <form  class='form' method="post" >
        <input type="text" name="Nom" placeholder=" nom" required>
        <input type="text" name="Prenom" placeholder=" prenom" required><br>
        <input type="text" name="telephone" placeholder="téléphone" required>
        <select name="matiere" >
        <option value="formations" selected disabled>formations</option>
           <option value="Design">Design graphique</option>
           <option value="Programmation Web">Programmation Web</option>
            <option value="Bureautique">Bureautique</option>
            <option value="Framework JAVA">Framework JAVA</option>
    </select></br>
        <select name="Niveau" class="select-niveau" >
            <option value="niveau" selected disabled > Niveau</option>
            <option value="debutant">débutant</option>
            <option value="avance">avancé</option>
        </select><br>
        <input type="number" name="NbrSeance" placeholder="nombre de séances" required min="8">
        <input type="radio" name="isTest" value="test" ><label>Test</label>
        <input type="radio" name="isTest" value="Formation"><label>Formation</label>
        <button type="submit">valider</button>
       
    </form>
</body>
</html>
