<?php 
//CONNEXION PHP AVEC BASE DE DONNEES
$DB_Server = "localhost:3306";
$DB_User = "root";
$DB_Password = '';
$DB_Name = "CentreFormation";
$database = mysqli_connect($DB_Server, $DB_User, $DB_Password, $DB_Name);

$personneBase = "CREATE TABLE IF NOT EXISTS Personne (idPersonne INT,Nom VARCHAR(20),Prenom VARCHAR(20),tele VARCHAR(12),PRIMARY KEY(idPersonne))";
$profBase = "CREATE TABLE IF NOT EXISTS Prof( IdProf INT,NomProf VARCHAR(20),PrenomProf VARCHAR(20),TelProf VARCHAR(12),NomMatiere VARCHAR(20),PRIMARY KEY (IdProf),FOREIGN KEY (NomMatiere) REFERENCES Matiere (NomMatiere))";
$grpBase = "CREATE TABLE IF NOT EXISTS Groupe (IdGrp INT,jour VARCHAR(20),Temps TIME, Niveau  VARCHAR(10),NumSalle VARCHAR(2),NomMatiere VARCHAR(20),IdProf INT,
PRIMARY KEY (IdGrp),
FOREIGN KEY (Niveau) REFERENCES NiveauScolaire(Niveau),
FOREIGN KEY (Numsalle) REFERENCES Salle(NumSalle),
FOREIGN KEY (NomMatiere) REFERENCES Matiere(NomMatiere),
FOREIGN KEY (IdProf) REFERENCES Prof(IdProf))";


$matiereBase = "CREATE TABLE IF NOT EXISTS Matiere (NomMatiere VARCHAR(20),Prix FLOAT, PRIMARY KEY (NomMatiere))";

$niveauBase = "CREATE TABLE IF NOT EXISTS NiveauScolaire (Niveau  VARCHAR(10),PRIMARY KEY (Niveau))";

$suivieBase = "CREATE TABLE IF NOT EXISTS Suivie (IdPersonne INT,NomFormation VARCHAR(20),FOREIGN KEY(IdPersonne) REFERENCES Personne(idPersonne),FOREIGN KEY(NomFormation) REFERENCES Formation(NomFormation))";

$appartenanceBase = "CREATE TABLE IF NOT EXISTS Appartenance (IdPersonne INT,IdGrp INT,NbrAbscence INT,NbrSeance INT,DateDebut DATE,FOREIGN KEY(IdPersonne) REFERENCES Personne(idPersonne))";

$formationBase = "CREATE TABLE IF NOT EXISTS Formation (NomFormation VARCHAR(20),PRIMARY KEY (NomFormation))";


$salleBase = "CREATE TABLE IF NOT EXISTS Salle (NumSalle VARCHAR(2),PRIMARY KEY (NumSalle))";
//QUERY PERMET L'EXEXUTION DU COMMANDE DANS LA BASE DE DONNEES
mysqli_query($database, $niveauBase);
mysqli_query($database, $salleBase);
mysqli_query($database, $matiereBase);
mysqli_query($database, $formationBase);
mysqli_query($database, $personneBase);
mysqli_query($database, $suivieBase);
mysqli_query($database, $profBase);
mysqli_query($database, $grpBase);
mysqli_query($database, $appartenanceBase);

//CETTE FONCTION RETOURNE 1 SI LA PERSONNE EXISTE DANS LA BASE DE DONNEE SINON 0 
function searchPerson($database,$nom,$prenom,$telephone){
    $countPerson = "SELECT COUNT(*) AS count_personne FROM Personne WHERE (nom = '{$nom}' AND prenom = '{$prenom}' AND tele = '{$telephone}') OR (nom = '{$prenom}' AND prenom = '{$nom}'AND tele = '{$telephone}')";                                                                                                      
   $number = mysqli_fetch_assoc(mysqli_query($database,$countPerson));
   return $number['count_personne'];
}

//VERIFICATION D'EXISTENCE D'UN ELEMENT DANS UNE LISTE
function findValueInList($list, $A) { 
   foreach($list as $Elem){
       if($Elem == $A){
           return true;
       }
   }
   return false;
}

//GENERE UN NOUVEAU ID POUR UN ETUDIANT , PROF OU GROUPE
function IdGenerator($database,$type){
   switch($type){
       case "personne":
           $sql = "SELECT idPersonne FROM Personne";
       case "prof":
           $sql = "SELECT IdProf FROM Prof";
       case "groupe":
           $sql = "SELECT IdGrp FROM Groupe";
       default:
           $sql = "SELECT idPersonne FROM Personne";
        // default pour eviter l'erreur  
   }
   $ListId = mysqli_query($database,$sql);
   $randomId = rand(100000,999999); 
   while(findValueInList($ListId,$randomId)){
       $randomId = rand(100000,999999);
   }
   return $randomId;
}

//RECHERCHE DE L'IDENTIFIANT D'UNE PERSONNE QUI EXIST DANS LE SYSTEME
function searchId($database,$nom, $prenom,$telephone){
    $findId="SELECT idPersonne FROM Personne WHERE (nom = '{$nom}' AND prenom = '{$prenom}' AND tele = '{$telephone}') OR (nom = '{$prenom}' AND prenom = '{$nom}'AND tele = '{$telephone}') ";
    $findIdResult = mysqli_query($database, $findId);
    if ($findIdResult) { //ETUDIANT EXISTE DANS LE SYSTEME 
        $found = mysqli_fetch_assoc($findIdResult);
     return $found['idPersonne'];
    }
}

//PERMET L'AJOUT DU PERONNE DANS UN GRP DANS LA MATIERE ET LE NIVEAU CHOISI
function searchgrp($database, $niveau, $matiere, $IdPersonne)
{  
    $test=1;
    $grp = "SELECT * FROM Groupe WHERE Niveau = '{$niveau}' AND NomMatiere = '{$matiere}' ";
    $Listgrp = mysqli_query($database, $grp);

    foreach ($Listgrp as $Elem) {
        $nbrperson = "SELECT COUNT(*) AS nbrperson FROM Appartenance WHERE IdGrp = {$Elem['IdGrp']}";
        $number = mysqli_fetch_assoc(mysqli_query($database, $nbrperson));
        if ($number['nbrperson'] < 8) {
            $sql = "SELECT IdGrp FROM Appartenance  WHERE IdPersonne='{$IdPersonne}'";
            $grpliste =mysqli_query($database, $sql);
            while ($grp= mysqli_fetch_assoc($grpliste)) {
                $sql = "SELECT `jour`,`Temps`FROM Groupe WHERE `IdGrp`='{$grp['IdGrp']}'";
                $emploie = mysqli_fetch_assoc(mysqli_query($database, $sql));
                if ($emploie['jour']==$Elem['jour'] && $emploie['Temps'] ==$Elem['Temps']) {//une fois trouver que le groupe dans le meme niveau et matiere choisi chauveauchant en temps avec les séances du groupe du personne ,passer au groupe suivant trouver dans le meme niveau et matiere
                    $test=0;
                    break;
                }
            }
            if($test==1){
                return $Elem['IdGrp'];
            }
        }
    }
    $idgrp = newgrp($database, $niveau, $matiere);
    emptysalle($database, $idgrp, $IdPersonne);
    return $idgrp;
}


//vérifie si la personne déjà inscrit dans cette matiere si oui la fonction retourne true
function searchmatiere($database,$idpersonne,$matiere,$niveau){
    $g="SELECT IdGrp FROM Groupe WHERE Niveau='{$niveau}' AND NomMatiere='{$matiere}'";
    $list=mysqli_query($database,$g);
    while($grp=mysqli_fetch_assoc($list)){
        $inscrit="SELECT COUNT(*) AS inscrit FROM Appartenance WHERE IdPersonne='{$idpersonne}' AND IdGrp='{$grp['IdGrp']}'";
        $nbr=mysqli_fetch_assoc(mysqli_query($database,$inscrit));
        if($nbr['inscrit']!=0){
            return true;
        }
    return false;    
    }
}
function newgrp($database,$niveau,$matiere){
   $prof="SELECT IdProf FROM Prof WHERE NomMatiere= '{$matiere}' ";
   $Listprof=mysqli_query($database,$prof);
   foreach($Listprof as $Elem){
       $nbrgrp= "SELECT COUNT(*) AS nbrgrp FROM Groupe  WHERE IdProf={$Elem['IdProf']}";
       $number = mysqli_fetch_assoc(mysqli_query($database,$nbrgrp));
       if( $number['nbrgrp']<3){
           $id=IdGenerator($database,'Groupe');
           $sql="INSERT INTO `Groupe`(`IdGrp` ,`Niveau` ,`NomMatiere`,`IdProf`) VALUES ($id ,'$niveau','$matiere','{$Elem['IdProf']}') ";
           mysqli_query($database,$sql);   
           return $id;
       }          
    } 
}
function emptysalle($database, $idGrp, $IdPersonne)
{
    $randomSalle = rand(1, 7);
    $randomJour = rand(0, 5);
    $randomTemps = rand(0, 1);
    $jour = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    $temp = ['17:00:00', '19:00:00'];

    while (true) {
        $sql = "SELECT NumSalle, jour, Temps FROM Groupe";
        $result = mysqli_query($database, $sql);
        while ($list = mysqli_fetch_assoc($result)) {//parcourir salles , jours , temps pour gérer le chauveauchement des salles
            $test = 1;
            $sql = "SELECT IdGrp FROM Appartenance  WHERE Idpersonne='{$IdPersonne}'";
            $grpliste = mysqli_query($database, $sql);
            while ($grp= mysqli_fetch_assoc($grpliste)) {//parcourir tous les groupes du personnes
                $sql = "SELECT `jour`,`Temps`FROM Groupe WHERE `IdGrp`='{$grp['IdGrp']}'";
                $emploie = mysqli_fetch_assoc(mysqli_query($database, $sql));
                if ($emploie['jour'] == $jour[$randomJour] && $emploie['Temps'] == $temp[$randomTemps]) {//une fois trouver que le jour , temps coincide avec celle généré par randome on passe au groupe suivante
                    $test = 0;
                    break;
                }
            }
            if ($test == 1 && ($list['NumSalle'] == $randomSalle && $list['jour'] == $jour[$randomJour] && $list['Temps'] == $temp[$randomTemps])) {//si l'horaire convenable pour la personne on teste s'elle est convenable pour la salle si non on sort du 2eme while est on génére noveau salles,temp,jour
                $test = 0;
                break;
            }
        }

        if ($test == 1) {
            $upS = "UPDATE `Groupe` SET `NumSalle`='{$randomSalle}' WHERE `IdGrp`='{$idGrp}'";
            mysqli_query($database, $upS);

            $upJ = "UPDATE `Groupe` SET `jour`='{$jour[$randomJour]}' WHERE `IdGrp`='{$idGrp}'";
            mysqli_query($database, $upJ);

            $upT = "UPDATE `Groupe` SET `Temps`='{$temp[$randomTemps]}' WHERE `IdGrp`='{$idGrp}'";
            mysqli_query($database, $upT);

            break;//affectation avec succès on sort du première boucle
        } else {//regénerer et refaire meme procédure
            $randomSalle = rand(1, 7);
            $randomJour = rand(0, 5);
            $randomTemps = rand(0, 1);
        }
    }
}

//permet de verifie si la personne est deja fait le test 
function searchTest($database,$nom, $prenom,$telephone){
    $idPersonne=searchId($database,$nom, $prenom,$telephone);
    $test="SELECT COUNT(*) AS test FROM Suivie WHERE IdPersonne='{$idPersonne}' AND NomFormation='test'";
    $number= mysqli_fetch_assoc(mysqli_query($database,$test));
return $number['test'];
}

//permet de modifier la date de debut du personne dans un groupe en tenant compte le nombre des personnes dans le groupe
//si le nombre des personnes dans le groupe est moins de 4 cette fonction ne sera pas modifié
function datedebut($database,$idGrp,$IdPersonne){
    $nbrperson = "SELECT COUNT(*) AS nbrperson FROM Appartenance WHERE IdGrp = '{$idGrp}'";//nbr des personnes dans grp 
    $number = mysqli_fetch_assoc(mysqli_query($database, $nbrperson));
    if ($number['nbrperson'] ==4) {
        $personne="SELECT IdPersonne FROM Appartenance WHERE IdGrp='{$idGrp}' ";
        $idlist=mysqli_query($database,$personne);
        $sql="SELECT `jour` FROM `Groupe`  where `IdGrp`='{$idGrp}'";
        $jour=mysqli_fetch_assoc(mysqli_query($database,$sql));
        $NextDate = date('Y-m-d',strtotime('next '.$jour['jour']));
        while($id=mysqli_fetch_assoc($idlist)){
           $up="UPDATE `Appartenance` SET `DateDebut`='{$NextDate}' WHERE `IdPersonne`='{$id['IdPersonne']}' AND IdGrp='{$idGrp}'";
           mysqli_query($database,$up);
    }
}
    else if($number['nbrperson']>4){
        $sql="SELECT `jour` FROM `Groupe`  where `IdGrp`='{$idGrp}'";
        $jour=mysqli_fetch_assoc(mysqli_query($database,$sql));
        $NextDate = date('Y-m-d',strtotime('next '.$jour['jour']));
        $update="UPDATE `Appartenance` SET `DateDebut`='{$NextDate}' WHERE `IdPersonne`='{$IdPersonne}' AND IdGrp='{$idGrp}'";
        mysqli_query($database,$update);
}
}

//permet de comparer la date avec la date d'aujourd'hui 
function compareDate($date){
    $todayDate = new DateTime();
    $Date = new DateTime($date);
    if ($Date < $todayDate) {
        return False;
    } else {
        return True;
    }
}

//permet de calculer la date de fin en se basant sur la date de début 
function DateFin($DateDebut, $nbrSeance){
    $numberOfDays=($nbrSeance-1)*7;
    return date('Y-m-d', strtotime($DateDebut . ' +' . $numberOfDays . ' days'));
}

//permet de supprimer tous les peronnes du groupe si la date de fin est supérieur à la date d'aujourd'hui +7
function check($database){
    $idGrp="SELECT * FROM `Appartenance`";
    $result=mysqli_query($database,$idGrp);
    while ($IdGrp= mysqli_fetch_assoc($result)) {
        $delai=DateFin($IdGrp['DateDebut'],($IdGrp['NbrSeance']) + 1);
        if(!compareDate($delai)){
            $delete="DELETE FROM `Appartenance` WHERE `IdGrp`='{$IdGrp['IdGrp']}' ";
            mysqli_query($database,$delete);
        }
    }
}
function max_inscription($database,$IdPersonne){
    $countPerson = "SELECT COUNT(*) AS count_personne FROM Appartenance WHERE IdPersonne='{$IdPersonne}' ";//nbr du groupe dont la personne inscrit
    $number =mysqli_fetch_assoc(mysqli_query($database, $countPerson));
    if($number['count_personne']==12){
        return false; //ne peut pas etre ajouter dans un nv grp
    }
    else{
        return true; 
    }
}




?>






