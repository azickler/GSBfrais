    <!-- Division pour le sommaire -->
    <div id="menuGauche">
     <div id="infosUtil">
    
        <h2>
    
</h2>
    
      </div>  
        <ul id="menuList">
			<li>
				  Comptable :
				  <div id="nomC"><?php echo $_SESSION['prenom']."  ".$_SESSION['nom']  ?></div>
			</li>
           <li class="smenu">
              <a href="index.php?uc=comptable&action=listeFraisComptable" title="Saisie fiche de frais ">FDF non validées</a>
           </li>
           <li class="smenu">
              <a href="index.php?uc=comptable&action=moisComptable" title="Consultation de mes fiches de frais">Consultation fiches de frais</a>
           </li>
 	   <li class="smenu">
              <a href="index.php?uc=connexion&action=deconnexion" title="Se dÃ©connecter">DÃ©connexion</a>
           </li>
         </ul>
        
    </div>
    
