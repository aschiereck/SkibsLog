<?php
// Deze variabelen zijn beschikbaar vanuit jachten.php: $jachtId, $db_connect
// Data voor dit tabblad ophalen
$result_motoren = $db_connect->query("SELECT * FROM Motoren WHERE SchipID = $jachtId");
$result_specs = $db_connect->query("SELECT * FROM TechnischeSpecificaties WHERE SchipID = $jachtId ORDER BY Categorie");
?>

<div class="main-card" style="margin-bottom: 2rem;">
    <div class="card-header-with-action">
       <h4>Motoren</h4>
       <?php if (has_role('user')): ?>
       <a href="motor_form.php?jacht_id=<?php echo $jachtId; ?>" class="card-action-icon" title="Motor toevoegen"><i class="fa-solid fa-plus"></i></a>
       <?php endif; ?>
   </div>
   <?php if ($result_motoren && $result_motoren->num_rows > 0): ?>
       <?php while($motor = $result_motoren->fetch_assoc()): ?>
           <div class="motor-card">
               <div class="motor-details">
                   <strong><?php echo htmlspecialchars($motor['Merk'] . ' ' . $motor['Type']); ?></strong>
                   <span><?php echo htmlspecialchars($motor['Vermogen']); ?> | <?php echo $motor['Draaiuren']; ?> uur</span>
               </div>
               <?php if (has_role('user')): ?>
               <a href="motor_form.php?id=<?php echo $motor['MotorID']; ?>" class="card-action-icon-small" title="Motor wijzigen"><i class="fa-solid fa-pencil"></i></a>
               <?php endif; ?>
           </div>
       <?php endwhile; ?>
   <?php else: ?>
        <p>Geen motoren geregistreerd voor dit jacht.</p>
   <?php endif; ?>
</div>

<div class="main-card">
    <div class="card-header-with-action">
       <h3>Technische Specificaties</h3>
       <?php if (has_role('user')): ?>
       <a href="techspec_form.php?jacht_id=<?php echo $jachtId; ?>" class="card-action-icon" title="Specificatie toevoegen"><i class="fa-solid fa-plus"></i></a>
       <?php endif; ?>
   </div>
    <div class="table-container-condensed">
       <table>
           <tbody>
           <?php if ($result_specs && $result_specs->num_rows > 0): ?>
               <?php while($spec = $result_specs->fetch_assoc()): ?>
                   <tr>
                       <td><strong><?php echo htmlspecialchars($spec['Categorie']); ?>:</strong> <?php echo htmlspecialchars($spec['Omschrijving']); ?></td>
                       <td><?php echo htmlspecialchars($spec['MerkType']); ?></td>
                       <?php if(has_role('user')): ?>
                       <td class="actions"><a href="techspec_form.php?id=<?php echo $spec['SpecID']; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a></td>
                       <?php endif; ?>
                   </tr>
               <?php endwhile; ?>
            <?php else: ?>
                <tr><td>Geen technische specificaties gevonden.</td></tr>
           <?php endif; ?>
           </tbody>
       </table>
   </div>
</div>
