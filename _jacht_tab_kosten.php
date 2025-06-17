<?php
// ...
if (!has_role('superuser')) {
    echo "<div class='error-box'>U heeft geen rechten om deze informatie te bekijken.</div>";
    return;
}
// NIEUWE QUERY: Combineert twee tabellen
$result_kosten = $db_connect->query("
    (SELECT KostenID as id, Datum, Omschrijving, Bedrag, Type, 'KostenLog' as bron FROM KostenLog WHERE SchipID = $jachtId)
    UNION
    (SELECT OnderhoudsID as id, Datum, Omschrijving, Bedrag, TypeGebeurtenis as Type, 'Onderhoud' as bron FROM OnderhoudsLog WHERE SchipID = $jachtId AND StatusBetaling = 'Te Verrekenen' AND Bedrag IS NOT NULL)
    ORDER BY Datum DESC
");
?>
<div class="card-header-with-action">
   <h3>Te Verrekenen Kosten</h3>
   <a href="kosten_form.php?jacht_id=<?php echo $jachtId; ?>" class="action-button-header"><i class="fa-solid fa-plus"></i> Losse Kostenpost Toevoegen</a>
</div>
<div class="table-container-condensed">
   <table>
       <thead><tr><th>Datum</th><th>Bron</th><th>Omschrijving</th><th style="text-align:right;">Bedrag</th><th></th></tr></thead>
       <tbody>
       <?php 
           $totaalKosten = 0;
           if(isset($result_kosten) && $result_kosten->num_rows > 0):
           while($kost = $result_kosten->fetch_assoc()): 
           $totaalKosten += $kost['Bedrag'];
       ?>
           <tr>
               <td><?php echo date('d-m-Y', strtotime($kost['Datum'])); ?></td>
               <td><?php echo htmlspecialchars($kost['bron']); ?></td>
               <td><?php echo htmlspecialchars($kost['Omschrijving']); ?></td>
               <td style="text-align:right;">€ <?php echo number_format($kost['Bedrag'], 2, ',', '.'); ?></td>
               <td class="actions">
                    <?php if ($kost['bron'] == 'KostenLog'): ?>
                        <a href="kosten_form.php?id=<?php echo $kost['id']; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a>
                    <?php else: ?>
                         <a href="onderhoud_form.php?id=<?php echo $kost['id']; ?>" title="Wijzigen"><i class="fa-solid fa-pencil"></i></a>
                    <?php endif; ?>
               </td>
           </tr>
       <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Geen kosten gevonden.</td></tr>
       <?php endif; ?>
       </tbody>
       <tfoot>
           <tr>
               <td colspan="3" style="text-align:right; font-weight: bold;">Totaal te verrekenen</td>
               <td style="text-align:right; font-weight: bold;">€ <?php echo number_format($totaalKosten, 2, ',', '.'); ?></td>
               <td></td>
           </tr>
       </tfoot>
   </table>
</div>
