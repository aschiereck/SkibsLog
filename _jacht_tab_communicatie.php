<?php
// Deze variabelen zijn beschikbaar vanuit jachten.php: $jachtId, $db_connect

// --- NIEUWE, KRACHTIGE QUERY MET UNION ---
// Dit combineert data uit 3 tabellen tot één chronologische tijdlijn voor dit specifieke jacht.
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
            c.KlantID as klant_id,
            k.Voornaam as klant_voornaam,
            k.Achternaam as klant_achternaam,
            c.MedewerkerNaam as medewerker,
            NULL as bedrag,
            NULL as status
        FROM CommunicatieLog c
        JOIN Klanten k ON c.KlantID = k.KlantID
        WHERE c.GerelateerdSchipID = ?
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
            b.KlantID,
            k.Voornaam,
            k.Achternaam,
            b.Begeleider,
            NULL,
            b.Status
        FROM Bezichtigingen b
        JOIN Klanten k ON b.KlantID = k.KlantID
        WHERE b.SchipID = ?
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
            bl.KlantID,
            k.Voornaam,
            k.Achternaam,
            NULL, -- Biedingen hebben geen medewerker-veld
            bl.BodBedrag,
            bl.Status
        FROM BiedingenLog bl
        JOIN Klanten k ON bl.KlantID = k.KlantID
        WHERE bl.SchipID = ?
    )
    ORDER BY datum DESC
");
$stmt->bind_param("iii", $jachtId, $jachtId, $jachtId);
$stmt->execute();
$result_comm = $stmt->get_result();

?>
<div class="card-header-with-action">
   <h3>Communicatietijdlijn</h3>
   <?php if (has_role('user')): ?>
   <a href="communicatie_form.php?jacht_id=<?php echo $jachtId; ?>" class="action-button-header"><i class="fa-solid fa-plus"></i> Handmatige Log Toevoegen</a>
   <?php endif; ?>
</div>
<div class="table-container-condensed">
   <table>
       <thead>
           <tr>
               <th>Datum & Tijd</th>
               <th>Type</th>
               <th>Details</th>
               <th>Betreft Klant</th>
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
                        <?php if($log['klant_id']): ?>
                            <a href="klanten.php?id=<?php echo $log['klant_id']; ?>"><?php echo htmlspecialchars($log['klant_voornaam'] . ' ' . $log['klant_achternaam']); ?></a>
                        <?php endif; ?>
                   </td>
                   <td><?php echo htmlspecialchars($log['medewerker']); ?></td>
               </tr>
           <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Geen communicatie gevonden voor dit jacht.</td></tr>
       <?php endif; ?>
       </tbody>
   </table>
</div>
