
<div id="contenu">
	<select name="select" id="selectVisiteur"
		onChange="javascript:location.href = this.value;">
		
		<?php
		$totalForfait = 0;
		if (! isset ( $_REQUEST ['visiteur'] )) {
			echo '<option value=0> { Sélectionner un visiteur } </option>';
		} else {
			echo '<option value=0>' . $leVisiteur ['nom'] . ' ' . $leVisiteur ['prenom'] . '</option>';
		}
		foreach ( $lesVisiteurs as $unVisiteur ) {
			if (! isset ( $_REQUEST ['visiteur'] ) || $unVisiteur ['id'] != $leVisiteur ['id']) {
				echo '
			<option value=index.php?uc=comptable&action=listeFraisComptable&visiteur=' . $unVisiteur ['id'] . '>' . $unVisiteur ['nom'] . ' ' . $unVisiteur ['prenom'] . '</option>';
			}
		}
		
		if (isset ( $_REQUEST ['visiteur'] )) {
			?>
	</select>

	<?php
			if (! empty ( $lesFraisForfait ) || ! empty ( $lesFraisHorsForfait )) {
				
				?>
				<a
		href="index.php?uc=comptable&action=validerFicheFrais&mois=<?php echo $moisA?>&visiteur=<?php echo $_REQUEST["visiteur"]?>"
		onclick="return confirm('Voulez-vous vraiment valider cette fiche defrais?');">Valider
		cette fiche de frais ?</a>
				<?php
			}
			if (! empty ( $lesFraisForfait )) {
				
				?>
				
<form method="POST"
		action="index.php?uc=comptable&action=majfraisforfait&visiteur=<?php echo $leVisiteur['id']?>">
		<div class="corpsForm">

			<p>Descriptif des éléments en Forfait du mois de <?php echo $libelleMois?></p>
			<fieldset>
				<legend>Eléments forfaitisés </legend>
			<?php
				foreach ( $lesFraisForfait as $unFrais ) {
					$idFrais = $unFrais ['idfrais'];
					$libelle = $unFrais ['libelle'];
					$quantite = $unFrais ['quantite'];
					$totalForfait = $totalForfait + $unFrais ['quantite'] * $unFrais ['montant'];
					?>
					<p>
					<label for="idFrais"><?php echo $libelle ?></label> <input
						type="text" id="idFrais" name="lesFrais[<?php echo $idFrais?>]"
						size="10" maxlength="5" value="<?php echo $quantite?>"> <?php echo $quantite * $unFrais['montant']?>€
					</p>
					
			
			<?php
				}
				?>			
				<p>
				<?php echo "Total : ".$totalForfait." €"?>
				</p>





			</fieldset>
		</div>
		<div class="piedForm">
			<p>
				<input id="ok" type="submit" value="Valider" size="20" /> <input
					id="annuler" type="reset" value="Effacer" size="20" />
			</p>
		</div>

	</form><?php
			}
			if (! empty ( $lesFraisHorsForfait )) {
				?>
	<table class="listeLegere">
		<caption>Descriptif des éléments Hors Forfait du mois de <?php echo $libelleMois ?></caption>
		<tr>
			<th class="date">Date</th>
			<th class="libelle">Libellé</th>
			<th class="montant">Montant</th>
			<th class="action">Refuser</th>
			<th class="action">Reporter</th>
		</tr>
          
    <?php
				foreach ( $lesFraisHorsForfait as $unFraisHorsForfait ) {
					if (substr ( $unFraisHorsForfait ['libelle'], 0, 9 ) != "REFUSE : ") {
						$libelle = $unFraisHorsForfait ['libelle'];
						$date = $unFraisHorsForfait ['date'];
						$montant = $unFraisHorsForfait ['montant'];
						$id = $unFraisHorsForfait ['id'];
						?>		
            <tr>

			<td> <?php echo $date ?></td>
			<td><?php echo $libelle ?></td>
			<td><?php echo $montant ?></td>
			<td><a
				href="index.php?uc=comptable&action=refuserFrais&idFrais=<?php echo $id ?>&mois=<?php echo $moisA ?>&visiteur=<?php echo $_REQUEST['visiteur'] ?>"
				onclick="return confirm('Voulez-vous vraiment refuser ce frais?');">Refuser</a></td>
			<td><a
				href="index.php?uc=comptable&action=reporterFrais&idFrais=<?php echo $id ?>&visiteur=<?php echo $_REQUEST['visiteur'] ?>"
				onclick="return confirm('Voulez-vous vraiment repporter ce frais?');">Reporter</a></td>
		</tr>
	<?php
					}
				}
			}
		}
		?>	  
                                          
    </table>

</div>