<?php
/** 
 * Classe d'accès aux données. 
 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 
 * @package default
 * @author Cheri Bibi
 * @version    1.0 
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */
class PdoGsb {
	private static $serveur = 'mysql:host=localhost';
	private static $bdd = 'dbname=gsb_frais';
	private static $user = 'root';
	private static $mdp = '';
	private static $monPdo;
	private static $monPdoGsb = null;
	/**
	 * Constructeur privé, crée l'instance de PDO qui sera sollicit�e
	 * pour toutes les m�thodes de la classe
	 */
	private function __construct() {
		PdoGsb::$monPdo = new PDO ( PdoGsb::$serveur . ';' . PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp );
		PdoGsb::$monPdo->query ( "SET CHARACTER SET utf8" );
	}
	public function _destruct() {
		PdoGsb::$monPdo = null;
	}
	/**
	 * Fonction statique qui crée l'unique instance de la classe
	 *
	 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
	 *
	 * @return l'unique objet de la classe PdoGsb
	 */
	public static function getPdoGsb() {
		if (PdoGsb::$monPdoGsb == null) {
			PdoGsb::$monPdoGsb = new PdoGsb ();
		}
		return PdoGsb::$monPdoGsb;
	}
	/**
	 * Retourne les informations d'un visiteur
	 *
	 * @param
	 *        	$login
	 * @param
	 *        	$mdp
	 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif
	 *        
	 */
	public function getInfosVisiteur($login, $mdp) {
		$res = PdoGsb::$monPdo->prepare ( "select visiteur.id as id, visiteur.nom as nom, visiteur.prenom as prenom from visiteur 
		where visiteur.login=:login and visiteur.mdp=:mdp" );
		$res->execute ( array (
				'login' => $login,
				'mdp' => $mdp 
		) );
		$ligne = $res->fetch ();
		return $ligne;
	}
	
	/**
	 * Retourne les informations d'un visiteur
	 *
	 * @param
	 *        	$login
	 * @param
	 *        	$mdp
	 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif
	 *        
	 */
	public function getInfosVisiteurComptable($id) {
		$res = PdoGsb::$monPdo->prepare ( "select visiteur.id as id, visiteur.nom as nom, visiteur.prenom as prenom from visiteur
		where visiteur.id=:id" );
		$res->execute ( array (
				'id' => $id 
		) );
		$ligne = $res->fetch ();
		return $ligne;
	}
	
	/**
	 * Retourne les informations d'un comptable
	 *
	 * @param
	 *        	$login
	 * @param
	 *        	$mdp
	 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif
	 */
	public function getInfosComptable($login, $mdp) {
		$res = PdoGsb::$monPdo->prepare ( "select id,login, comptable.mdp as id,nom FROM comptable where login=:login and mdp=:mdp" );
		$res->execute ( array (
				'login' => $login,
				'mdp' => $mdp 
		) );
		$ligne = $res->fetch ();
		return $ligne;
	}
	
	/**
	 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
	 * concernées par les deux arguments
	 *
	 * La boucle foreach ne peut être utilisée ici car on procède
	 * à une modification de la structure itérée - transformation du champ date-
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif
	 *        
	 */
	public function getLesFraisHorsForfait($idVisiteur, $mois) {
		$req = "select date,montant,lignefraishorsforfait.libelle as libelle,lignefraishorsforfait.id as id
		from lignefraishorsforfait 
		where lignefraishorsforfait.idvisiteur ='$idVisiteur' 
		and lignefraishorsforfait.mois = '$mois' ";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesLignes = $res->fetchAll ();
		$nbLignes = count ( $lesLignes );
		for($i = 0; $i < $nbLignes; $i ++) {
			$date = $lesLignes [$i] ['date'];
			$lesLignes [$i] ['date'] = dateAnglaisVersFrancais ( $date );
		}
		return $lesLignes;
	}
	
	/**
	 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
	 *
	 * La boucle foreach ne peut être utilisée ici car on procède
	 * à une modification de la structure itérée - transformation du champ date-
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif
	 *        
	 */
	public function getLesFraisHorsForfaitMois($mois) {
		$req = "select * from lignefraishorsforfait where month(date) ='$mois'";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesLignes = $res->fetchAll ();
		return $lesLignes;
	}
	
	/**
	 * Retourne sous forme d'un tableau associatif toutes les fiches de frais d'un mois à l'état validée
	 *
	 * @param
	 *        	$mois
	 *        
	 * @return tous les champs des fiches de frais sous la forme d'un tableau associatif
	 *        
	 */
	public function getFicheFrais($mois) {
		$req = "select * from fichefrais INNER JOIN visiteur ON idVisiteur=visiteur.id where mois='$mois' AND idEtat='VA'";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesLignes = $res->fetchAll ();
		return $lesLignes;
	}
	
	/**
	 * Retourne sous forme d'un tableau associatif toutes les fiches de frais d'un mois à l'état mise en paiement
	 *
	 * @param
	 *        	$mois
	 *        
	 * @return tous les champs des fiches de frais sous la forme d'un tableau associatif
	 *        
	 */
	public function getFicheFraisPaye($mois) {
		$req = "select * from fichefrais INNER JOIN visiteur ON idVisiteur=visiteur.id where mois='$mois' AND idEtat='MP'";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesLignes = $res->fetchAll ();
		return $lesLignes;
	}
	
	/**
	 * Retourne le nombre de justificatif d'un visiteur pour un mois donné
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return le nombre entier de justificatifs
	 *        
	 */
	public function getNbjustificatifs($idVisiteur, $mois) {
		$req = "select fichefrais.nbjustificatifs as nb from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query ( $req );
		$laLigne = $res->fetch ();
		return $laLigne ['nb'];
	}
	
	/**
	 * Met à jour la valeur de la fiche de frais d'un certain visiteur à un mois précis
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 *        
	 */
	public function miseEnPaiement($idVisiteur, $mois) {
		$date = date ( 'Y-m-d' );
		$req = "UPDATE fichefrais SET idEtat='MP', dateModif='$date' where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query ( $req );
	}
	
	/**
	 * Met à jour la valeur de la fiche de frais d'un certain visiteur à un mois précis
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 *        
	 */
	public function validerRemboursement($idVisiteur, $mois) {
		$date = date ( 'Y-m-d' );
		$req = "UPDATE fichefrais SET idEtat='RB', dateModif='$date' where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query ( $req );
	}
	
	/**
	 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
	 * concern�es par les deux arguments
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif
	 *        
	 */
	public function getLesFraisForfait($idVisiteur, $mois) {
		$req = "select fraisforfait.id as idfrais,  fraisforfait.montant as montant, fraisforfait.libelle as libelle, 
		lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait 
		on fraisforfait.id = lignefraisforfait.idfraisforfait
		where lignefraisforfait.idvisiteur ='$idVisiteur' and lignefraisforfait.mois='$mois' 
		order by lignefraisforfait.idfraisforfait";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesLignes = $res->fetchAll ();
		return $lesLignes;
	}
	/**
	 * Retourne tous les id de la table FraisForfait
	 *
	 * @return un tableau associatif
	 *        
	 */
	public function getLesIdFrais() {
		$req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesLignes = $res->fetchAll ();
		return $lesLignes;
	}
	
	/**
	 * Retourne l'id max de la table lignefraishorsforfait
	 *
	 * @return un int
	 */
	public function getMaxIdHorsForfait() {
		$req = "select MAX(lignefraishorsforfait.id) from lignefraishorsforfait";
		$res = PdoGsb::$monPdo->query ( $req );
		$idMax = $res->fetch ();
		return $idMax;
	}
	
	/**
	 * Met à jour la table ligneFraisForfait
	 *
	 * Met à jour la table ligneFraisForfait pour un visiteur et
	 * un mois donné en enregistrant les nouveaux montants
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @param $lesFrais tableau
	 *        	associatif de cl� idFrais et de valeur la quantit� pour ce frais
	 * @return un tableau associatif
	 *        
	 */
	public function majFraisForfait($idVisiteur, $mois, $lesFrais) {
		$lesCles = array_keys ( $lesFrais );
		foreach ( $lesCles as $unIdFrais ) {
			$qte = $lesFrais [$unIdFrais];
			$req = "update lignefraisforfait set lignefraisforfait.quantite = $qte
			where lignefraisforfait.idvisiteur = '$idVisiteur' and lignefraisforfait.mois = '$mois'
			and lignefraisforfait.idfraisforfait = '$unIdFrais'";
			PdoGsb::$monPdo->exec ( $req );
		}
	}
	
	/**
	 * Met à jour la table ligneFraisHorsForfait
	 *
	 * Met à jour la table ligneFraisForfait pour un visiteur et
	 * un mois donné en enregistrant le nouveau libelle
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return un boolean
	 *        
	 */
	public function refuserFrais($idVisiteur, $mois, $idFrais) {
		$req = "SELECT libelle FROM ligneFraisHorsForfait WHERE ligneFraisHorsForfait.id='$idFrais'";
		$res = PdoGsb::$monPdo->query ( $req );
		$ligne = $res->fetch ();
		$libelle = 'REFUSE : ' . $ligne ['libelle'];
		var_dump ( $libelle );
		$req = "UPDATE ligneFraisHorsForfait set ligneFraisHorsForfait.libelle='$libelle' WHERE lignefraishorsforfait.id='$idFrais'";
		PdoGsb::$monPdo->exec ( $req );
		return PdoGsb::$monPdo->exec ( $req );
	}
	
	/**
	 * Met à jour la table ligneFraisHorsForfait
	 *
	 * Met à jour la table ligneFraisForfait pour un visiteur et
	 * un mois donné en enregistrant le nouveau libelle et créer une fiche de frais et des lignes de frais si il n'en existe pas pour le mois suivant
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return un tableau associatif
	 *        
	 */
	public function reporterFrais($idFrais, $idVisiteur) {
		$req = "SELECT mois FROM ligneFraisHorsForfait WHERE ligneFraisHorsForfait.id='$idFrais'";
		$res = PdoGsb::$monPdo->query ( $req );
		$ligne = $res->fetch ();
		$mois = substr ( $ligne ['mois'], 4 );
		$annee = substr ( $ligne ['mois'], 0, 4 );
		if ($mois == 12) {
			$annee = $annee + 1;
			$mois = '01';
		} elseif ($mois < 9) {
			$mois = '0' . ($mois + 1);
		} else {
			$mois ++;
		}
		$valeurValide = $annee . $mois;
		$req = "SELECT * FROM fichefrais WHERE idVisiteur='$idVisiteur' AND mois='$valeurValide'";
		
		$res = PdoGsb::$monPdo->query ( $req );
		if (! $res->fetch ()) {
			$req = "INSERT INTO fichefrais (idVisiteur,mois,idEtat) VALUES ('$idVisiteur','$valeurValide','CR')";
			$res = PdoGsb::$monPdo->query ( $req );
			$lesIdFrais = $this->getLesIdFrais ();
			foreach ( $lesIdFrais as $uneLigneIdFrais ) {
				$unIdFrais = $uneLigneIdFrais ['idfrais'];
				$req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
			values('$idVisiteur','$valeurValide','$unIdFrais',0)";
				var_dump($req);
				$res = PdoGsb::$monPdo->query ( $req );
			}
		}
		$req = "UPDATE lignefraishorsforfait set lignefraishorsforfait.mois='$valeurValide' WHERE lignefraishorsforfait.id='$idFrais'";
		var_dump ( $req );
		PdoGsb::$monPdo->exec ( $req );
		return PdoGsb::$monPdo->exec ( $req );
	}
	
	/**
	 * Met à jour la table ficheFrais
	 *
	 * Met à jour la table fiche frais en passant la fiche à l'état validé, et lui ajoute le montant valide du total de ses frais.
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return un tableau associatif
	 *        
	 */
	public function validerFrais($idVisiteur, $mois) {
		$req = "SELECT SUM(montant) AS somme FROM ligneFraisHorsForfait 
		WHERE ligneFraisHorsForfait.idVisiteur='$idVisiteur' AND mois='$mois' AND SUBSTR(libelle,1,9) !='REFUSE : '";
		$res = PdoGsb::$monPdo->query ( $req );
		$ligne = $res->fetch ();
		$montant = $ligne ['somme'];
		$req = "SELECT SUM(quantite*montant) AS somme 
				FROM lignefraisforfait 
				JOIN fraisforfait ON lignefraisforfait.idFraisForfait=fraisforfait.id 
				WHERE idVisiteur='$idVisiteur' AND mois='$mois'";
		$res = PdoGsb::$monPdo->query ( $req );
		$ligne = $res->fetch ();
		$montant = $montant + $ligne ['somme'];
		$date = date ( 'y-m-d' );
		$req = "UPDATE fichefrais set fichefrais.montantvalide='$montant', fichefrais.idEtat='VA', fichefrais.dateModif='$date' WHERE fichefrais.idVisiteur='$idVisiteur' AND fichefrais.mois='$mois'";
		PdoGsb::$monPdo->exec ( $req );
		return PdoGsb::$monPdo->exec ( $req );
	}
	
	/**
	 * met à jour le nombre de justificatifs de la table ficheFrais
	 * pour le mois et le visiteur concern�
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 *        	
	 */
	public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs) {
		$req = "update fichefrais set nbjustificatifs = $nbJustificatifs 
		where fichefrais.idvisiteur = '$idVisiteur' and fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec ( $req );
	}
	/**
	 * Teste si un visiteur poss�de une fiche de frais pour le mois passé en argument
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return vrai ou faux
	 *        
	 */
	public function estPremierFraisMois($idVisiteur, $mois) {
		$ok = false;
		$req = "select count(*) as nblignesfrais from fichefrais 
		where fichefrais.mois = '$mois' and fichefrais.idvisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query ( $req );
		$laLigne = $res->fetch ();
		if ($laLigne ['nblignesfrais'] == 0) {
			$ok = true;
		}
		return $ok;
	}
	/**
	 * Retourne le dernier mois en cours d'un visiteur
	 *
	 * @param
	 *        	$idVisiteur
	 * @return le mois sous la forme aaaamm
	 *        
	 */
	public function dernierMoisSaisi($idVisiteur) {
		$req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = '$idVisiteur'";
		$res = PdoGsb::$monPdo->query ( $req );
		$laLigne = $res->fetch ();
		$dernierMois = $laLigne ['dernierMois'];
		return $dernierMois;
	}
	
	/**
	 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
	 *
	 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
	 * avec un idEtat à 'CR' et cr�e les lignes de frais forfait de quantités nulles
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 *        	
	 */
	public function creeNouvellesLignesFrais($idVisiteur, $mois) {
		$dernierMois = $this->dernierMoisSaisi ( $idVisiteur );
		$laDerniereFiche = $this->getLesInfosFicheFrais ( $idVisiteur, $dernierMois );
		if ($laDerniereFiche ['idEtat'] == 'CR') {
			$this->majEtatFicheFrais ( $idVisiteur, $dernierMois, 'CL' );
		}
		$req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		values('$idVisiteur','$mois',0,0,now(),'CR')";
		PdoGsb::$monPdo->exec ( $req );
		$lesIdFrais = $this->getLesIdFrais ();
		foreach ( $lesIdFrais as $uneLigneIdFrais ) {
			$unIdFrais = $uneLigneIdFrais ['idfrais'];
			$req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
			values('$idVisiteur','$mois','$unIdFrais',0)";
			PdoGsb::$monPdo->exec ( $req );
		}
	}
	/**
	 * Crée un nouveau frais hors forfait pour un visiteur un mois donné
	 * à partir des informations fournies en paramètre
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @param $libelle :
	 *        	le libelle du frais
	 * @param $date :
	 *        	la date du frais au format français jj//mm/aaaa
	 * @param $montant :
	 *        	le montant
	 *        	
	 */
	public function creeNouveauFraisHorsForfait($id, $idVisiteur, $mois, $libelle, $date, $montant) {
		$dateFr = dateFrancaisVersAnglais ( $date );
		$req = "insert into lignefraishorsforfait 
		values('$id','$idVisiteur','$mois','$libelle','$dateFr','$montant')";
		PdoGsb::$monPdo->exec ( $req );
	}
	/**
	 * Supprime le frais hors forfait dont l'id est pass� en argument
	 *
	 * @param
	 *        	$idFrais
	 *        	
	 */
	public function supprimerFraisHorsForfait($idFrais) {
		$req = "delete from lignefraishorsforfait where lignefraishorsforfait.id =$idFrais ";
		PdoGsb::$monPdo->exec ( $req );
	}
	/**
	 * Retourne les mois pour lesquel un visiteur a une fiche de frais
	 *
	 * @param
	 *        	$idVisiteur
	 * @return un tableau associatif de cl� un mois -aaaamm- et de valeurs l'ann�e et le mois correspondant
	 *        
	 */
	public function getLesMoisDisponibles($idVisiteur) {
		$req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='$idVisiteur' order by fichefrais.mois desc ";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesMois = array ();
		$laLigne = $res->fetch ();
		while ( $laLigne != null ) {
			$mois = $laLigne ['mois'];
			$numAnnee = substr ( $mois, 0, 4 );
			$numMois = substr ( $mois, 4, 2 );
			$lesMois ["$mois"] = array (
					"mois" => "$mois",
					"numAnnee" => "$numAnnee",
					"numMois" => "$numMois" 
			);
			$laLigne = $res->fetch ();
		}
		return $lesMois;
	}
	
	/**
	 * Retourne les mois pour lesquels il y a des fdf d'entrées
	 *
	 * @return un tableau associatif de clÃ© un mois -aaaamm- et de valeurs l'annÃ©e et le mois correspondant
	 *        
	 */
	public function getMoisDisponibles() {
		$req = "select fichefrais.mois as mois from  fichefrais order by fichefrais.mois desc ";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesMois = array ();
		$laLigne = $res->fetch ();
		while ( $laLigne != null ) {
			$mois = $laLigne ['mois'];
			$numAnnee = substr ( $mois, 0, 4 );
			$numMois = substr ( $mois, 4, 2 );
			$lesMois ["$mois"] = array (
					"mois" => "$mois",
					"numAnnee" => "$numAnnee",
					"numMois" => "$numMois" 
			);
			$laLigne = $res->fetch ();
		}
		return $lesMois;
	}
	
	/**
	 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donn�
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'�tat
	 *        
	 */
	public function getLesInfosFicheFrais($idVisiteur, $mois) {
		$req = "select ficheFrais.idEtat as idEtat, ficheFrais.dateModif as dateModif, ficheFrais.nbJustificatifs as nbJustificatifs, 
			ficheFrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join Etat on ficheFrais.idEtat = Etat.id 
			where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query ( $req );
		$laLigne = $res->fetch ();
		return $laLigne;
	}
	/**
	 * Modifie l'état et la date de modification d'une fiche de frais
	 *
	 * Modifie le champ idEtat et met la date de modif à aujourd'hui
	 *
	 * @param
	 *        	$idVisiteur
	 * @param $mois sous
	 *        	la forme aaaamm
	 */
	public function majEtatFicheFrais($idVisiteur, $mois, $etat) {
		$req = "update ficheFrais set idEtat = '$etat', dateModif = now() 
		where fichefrais.idvisiteur ='$idVisiteur' and fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec ( $req );
	}
	
	/**
	 * Retourne la liste des visiteurs avec une fdf en cl
	 *
	 * @param $mois sous
	 *        	la forme mm
	 *        	
	 * @return les visiteurs sous forme de tableau associatif
	 */
	public function getListeFraisVisiteur($mois) {
		$req = "SELECT * FROM fichefrais f INNER JOIN visiteur v ON v.id=f.idVisiteur WHERE idEtat='CL' AND SUBSTR(mois,5)=$mois";
		$res = PdoGsb::$monPdo->query ( $req );
		$lesLignes = $res->fetchAll ();
		return $lesLignes;
	}
	
	/**
	 * Cloture les fiches à l'état CR avec date inférieure à celle entrée en param
	 *
	 * @param $date sous
	 *        	la forme yyyy-mm-dd
	 *        	
	 */
	public function cloturerFDF($date) {
		$req = "UPDATE fichefrais SET idEtat='CL' where fichefrais.idEtat= 'CR' AND fichefrais.datemodif<'$date'";
		$res = PdoGsb::$monPdo->query ( $req );
	}
}
?>
