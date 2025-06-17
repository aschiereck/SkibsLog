<?php
// Deze variabelen zijn beschikbaar vanuit jachten.php: $jachtId, $db_connect
$result_onderhoud = $db_connect->query("SELECT * FROM OnderhoudsLog WHERE SchipID = $jachtId ORDER BY Datum DESC");

// Definieer de return URL voor dit specifieke tabblad
$return_url = urlencode("jachten.php?id=" . $jachtId . "#onderhoud");
?>
<div class="card-header-with-action">
   <h3>Onderhoudslogboek</h3>
   <?php if (has_role('user')): ?>
   <!-- Link geeft nu de return_url mee -->
   <a href="onderhoud_form.php?jacht_id=<?php echo $jachtId; ?>&return_url=<?php echo $return_url; ?>" class="action-button-header"><i class="fa-solid fa-plus"></i> Log toevoegen</a>
   <?php endif; ?>
</div>
<div class="table-container-condensed">
   <table>
       <thead><tr><th>Datum</th><th>Type</th><th>Omschrijving</th><th style="text-align:right;">Kosten</th><th>Status Betaling</th><?php if(has_role('user')) echo "<th></th>"; ?></tr></thead>
       <tbody>
       <?php if ($result_onderhoud && $result_onderhoud->num_rows > 0): ?>
           <?php while($log = $result_onderhoud->fetch_assoc()): ?>
               <tr>
                   <td><?php echo date('d-m-Y', strtotime($log['Datum'])); ?></td>
                   <td><?php echo htmlspecialchars($log['TypeGebeurtenis']); ?></td>
                   <td><?php echo htmlspecialchars($log['Omschrijving']); ?></td>
                   <td style="text-align:right;"><?php echo !empty($log['Bedrag']) ? '€ ' . number_format($log['Bedrag'], 2, ',', '.') : '-'; ?></td>
                   <td><?php echo htmlspecialchars($log['StatusBetaling']); ?></td>
                   <?php if(has_role('user')): ?>
                   <td class="actions">
                       <!-- Link geeft nu de return_url mee -->
                       <a href="onderhoud_form.php?id=<?php echo $log['OnderhoudsID']; ?>&return_url=<?php echo $return_url; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a>
                   </td>
                   <?php endif; ?>
               </tr>
           <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Geen onderhoudslog gevonden.</td></tr>
       <?php endif; ?>
       </tbody>
   </table>
</div>
