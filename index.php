<?php
require ('include/pdf.php');
require_once ("include/fct.inc.php");
require_once ("include/class.pdogsb.inc.php");
include ("vues/v_entete.php");
session_start ();
$pdo = PdoGsb::getPdoGsb ();
$estConnecte = estConnecte ();
if (! isset ( $_REQUEST ['uc'] ) && ! $estConnecte) {
	$_REQUEST ['uc'] = 'connexion';
} elseif (! isset ( $_REQUEST ['uc'] )) {
	if ($_SESSION ['idVisiteur'] == 'comptable')
		$_REQUEST ['uc'] = 'comptable';
	else 
		$_REQUEST ['uc'] = 'gererFrais';
}
$uc = $_REQUEST ['uc'];
switch ($uc) {
	case 'connexion' :
		{
			include ("controleurs/c_connexion.php");
			break;
		}
	case 'gererFrais' :
		{
			include ("controleurs/c_gererFrais.php");
			break;
		}
	case 'etatFrais' :
		{
			include ("controleurs/c_etatFrais.php");
			break;
		}
	case 'comptable' :
		{
			include ("controleurs/c_comptable.php");
			break;
		}
}
include ("vues/v_pied.php");
?>

