<?php
// Deze variabelen zijn beschikbaar vanuit klanten.php: $klantId, $db_connect

// --- NIEUWE, KRACHTIGE QUERY MET UNION ---
// Dit combineert data uit 3 tabellen tot één chronologische tijdlijn.
$stmt = $db_connect->prepare("
    (
        -- 1. Handmatige logs uit CommunicatieLog
        SELECT 
            c.LogID as id, 
            c.DatumTijd as datum, 
            c.Type as type, 
            c.Onderwerp as onderwerp, 
            c.Notities as notities,
            c.DocumentLink as link,
            c.GerelateerdSchipID as schip_id,
            s.NaamSchip as schip_naam,
            c.MedewerkerNaam as medewerker,
            NULL as bedrag,
            NULL as status
        FROM CommunicatieLog c
        LEFT JOIN Schepen s ON c.GerelateerdSchipID = s.SchipID
        WHERE c.KlantID = ?
    )
    UNION ALL
    (
        -- 2. Geplande en voltooide bezichtigingen
        SELECT 
            b.BezichtigingID, 
            CONCAT(b.Datum, ' ', b.Tijd), 
            'Bezichtiging', 
            'Bezichtiging afspraak',
            b.FeedbackKlant,
            NULL,
            b.SchipID,
            s.NaamSchip,
            b.Begeleider,
            NULL,
            b.Status
        FROM Bezichtigingen b
        JOIN Schepen s ON b.SchipID = s.SchipID
        WHERE b.KlantID = ?
    )
    UNION ALL
    (
        -- 3. Gemaakte biedingen
        SELECT 
            bl.BodID, 
            bl.DatumTijdBod, 
            'Bod',
            'Bod uitgebracht',
            bl.Voorwaarden,
            NULL,
            bl.SchipID,
            s.NaamSchip,
            NULL, -- Biedingen hebben geen medewerker-veld
            bl.BodBedrag,
            bl.Status
        FROM BiedingenLog bl
        JOIN Schepen s ON bl.SchipID = s.SchipID
        WHERE bl.KlantID = ?
    )
    ORDER BY datum DESC
");
$stmt->bind_param("iii", $klantId, $klantId, $klantId);
$stmt->execute();
$result_comm = $stmt->get_result();

?>
<div class="card-header-with-action">
   <h3>Communicatietijdlijn</h3>
   <?php if (has_role('user')): ?>
   <a href="communicatie_form.php?klant_id=<?php echo $klantId; ?>" class="action-button-header"><i class="fa-solid fa-plus"></i> Handmatige Log Toevoegen</a>
   <?php endif; ?>
</div>
<div class="table-container-condensed">
   <table>
       <thead>
           <tr>
               <th>Datum & Tijd</th>
               <th>Type</th>
               <th>Details</th>
               <th>Betreft</th>
               <th>Medewerker</th>
           </tr>
       </thead>
       <tbody>
       <?php if ($result_comm && $result_comm->num_rows > 0): ?>
           <?php while($log = $result_comm->fetch_assoc()): ?>
               <tr>
                   <td><?php echo date('d-m-Y H:i', strtotime($log['datum'])); ?></td>
                   <td>
                        <!-- Icoontje per type voor snelle herkenning -->
                        <?php if ($log['type'] == 'Bezichtiging'): ?><i class="fa-solid fa-eye"></i><?php endif; ?>
                        <?php if ($log['type'] == 'Bod'): ?><i class="fa-solid fa-gavel"></i><?php endif; ?>
                        <?php if (strpos($log['type'], 'Telefoon') !== false): ?><i class="fa-solid fa-phone"></i><?php endif; ?>
                        <?php if ($log['type'] == 'E-mail'): ?><i class="fa-solid fa-envelope"></i><?php endif; ?>
                        <?php if ($log['type'] == 'Opdracht'): ?><i class="fa-solid fa-person-digging"></i><?php endif; ?>
                        <?php echo htmlspecialchars($log['type']); ?>
                   </td>
                   <td>
                       <strong><?php echo htmlspecialchars($log['onderwerp']); ?></strong>
                       <?php if($log['type'] == 'Bod'): ?>
                            <p class="bod-bedrag-small">€ <?php echo number_format($log['bedrag'], 0, ',', '.'); ?> (Status: <?php echo htmlspecialchars($log['status']); ?>)</p>
                       <?php endif; ?>
                       <p class="log-notes"><?php echo htmlspecialchars($log['notities']); ?></p>
                       <?php if(!empty($log['link'])): ?>
                            <a href="<?php echo htmlspecialchars($log['link']); ?>" target="_blank" class="document-link"><i class="fa-solid fa-file-arrow-down"></i> Document bekijken</a>
                       <?php endif; ?>
                   </td>
                   <td>
                        <?php if($log['schip_id']): ?>
                            <a href="jachten.php?id=<?php echo $log['schip_id']; ?>"><?php echo htmlspecialchars($log['schip_naam']); ?></a>
                        <?php endif; ?>
                   </td>
                   <td><?php echo htmlspecialchars($log['medewerker']); ?></td>
               </tr>
           <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Geen communicatie gevonden.</td></tr>
       <?php endif; ?>
       </tbody>
   </table>
</div>
