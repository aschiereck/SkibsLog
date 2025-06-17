<?php
$pageTitle = 'Rapportages';
require 'header.php';

// --- DATA OPHALEN VOOR RAPPORTAGES ---

// 1. Verkoopprestaties dit jaar
$jaar_start = date('Y-01-01');
$query_sales_year = "
    SELECT 
        COUNT(SchipID) as aantal, 
        SUM(UiteindelijkeVerkoopprijs) as omzet 
    FROM Schepen 
    WHERE Status = 'Verkocht' AND DatumVerkocht >= '{$jaar_start}'
";
$sales_year = $db_connect->query($query_sales_year)->fetch_assoc();

// 2. Verkoopprestaties vorig jaar (voor vergelijking)
$vorig_jaar_start = date('Y-01-01', strtotime('-1 year'));
$vorig_jaar_eind = date('Y-12-31', strtotime('-1 year'));
$query_sales_last_year = "
    SELECT 
        COUNT(SchipID) as aantal, 
        SUM(UiteindelijkeVerkoopprijs) as omzet 
    FROM Schepen 
    WHERE Status = 'Verkocht' AND DatumVerkocht BETWEEN '{$vorig_jaar_start}' AND '{$vorig_jaar_eind}'
";
$sales_last_year = $db_connect->query($query_sales_last_year)->fetch_assoc();

// 3. Doorlooptijd en prijsverschil analyse van verkochte schepen
$query_listing_performance = "
    SELECT 
        NaamSchip, 
        DatumTeKoop, 
        DatumVerkocht, 
        Vraagprijs, 
        UiteindelijkeVerkoopprijs,
        DATEDIFF(DatumVerkocht, DatumTeKoop) as doorlooptijd_dagen
    FROM Schepen 
    WHERE Status = 'Verkocht' AND DatumTeKoop IS NOT NULL AND DatumVerkocht IS NOT NULL
    ORDER BY DatumVerkocht DESC
";
$result_listing_performance = $db_connect->query($query_listing_performance);

// 4. Populairste jachten (meeste bezichtigingen)
$query_popular_yachts = "
    SELECT 
        s.NaamSchip, 
        s.MerkWerf,
        COUNT(b.BezichtigingID) as aantal_bezichtigingen
    FROM Bezichtigingen b
    JOIN Schepen s ON b.SchipID = s.SchipID
    GROUP BY s.SchipID
    ORDER BY aantal_bezichtigingen DESC
    LIMIT 5
";
$result_popular_yachts = $db_connect->query($query_popular_yachts);

?>

<section class="content-page">
    <div class="page-header">
        <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
    </div>

    <div class="report-grid">
        <!-- Verkoopprestaties Kaart -->
        <div class="report-card">
            <h3><i class="fa-solid fa-euro-sign"></i> Verkoopprestaties (dit jaar)</h3>
            <div class="stat-group">
                <div class="stat-item">
                    <h4>Omzet</h4>
                    <span>€ <?php echo number_format($sales_year['omzet'] ?? 0, 0, ',', '.'); ?></span>
                </div>
                <div class="stat-item">
                    <h4>Aantal Verkopen</h4>
                    <span><?php echo $sales_year['aantal'] ?? 0; ?></span>
                </div>
            </div>
            <p class="comparison-text">Vorig jaar: € <?php echo number_format($sales_last_year['omzet'] ?? 0, 0, ',', '.'); ?> (<?php echo $sales_last_year['aantal'] ?? 0; ?> verkopen)</p>
        </div>

        <!-- Populairste Jachten Kaart -->
        <div class="report-card">
            <h3><i class="fa-solid fa-fire"></i> Populairste Jachten</h3>
            <?php if ($result_popular_yachts && $result_popular_yachts->num_rows > 0): ?>
                <ol class="popular-list">
                    <?php while($yacht = $result_popular_yachts->fetch_assoc()): ?>
                        <li>
                            <span><?php echo htmlspecialchars($yacht['NaamSchip']); ?></span>
                            <small><?php echo htmlspecialchars($yacht['MerkWerf']); ?></small>
                            <b><?php echo $yacht['aantal_bezichtigingen']; ?> bezichtigingen</b>
                        </li>
                    <?php endwhile; ?>
                </ol>
            <?php else: ?>
                <p>Nog niet voldoende data voor een top 5.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Doorlooptijd Analyse Tabel -->
    <div class="report-table-card">
        <h3><i class="fa-solid fa-chart-line"></i> Doorlooptijd & Prijs Analyse</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Jacht</th>
                        <th>Doorlooptijd (dagen)</th>
                        <th>Vraagprijs</th>
                        <th>Verkoopprijs</th>
                        <th>Verschil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_listing_performance && $result_listing_performance->num_rows > 0): ?>
                        <?php while($item = $result_listing_performance->fetch_assoc()): 
                            $prijsverschil = $item['Vraagprijs'] - $item['UiteindelijkeVerkoopprijs'];
                            $verschil_percentage = ($item['Vraagprijs'] > 0) ? ($prijsverschil / $item['Vraagprijs']) * 100 : 0;
                        ?>
                            <tr>
                                <td data-label="Jacht"><?php echo htmlspecialchars($item['NaamSchip']); ?></td>
                                <td data-label="Doorlooptijd"><?php echo $item['doorlooptijd_dagen']; ?> dagen</td>
                                <td data-label="Vraagprijs">€ <?php echo number_format($item['Vraagprijs'], 0, ',', '.'); ?></td>
                                <td data-label="Verkoopprijs">€ <?php echo number_format($item['UiteindelijkeVerkoopprijs'], 0, ',', '.'); ?></td>
                                <td data-label="Verschil" class="<?php echo ($prijsverschil > 0) ? 'negative' : 'positive'; ?>">
                                    € <?php echo number_format($prijsverschil, 0, ',', '.'); ?> (<?php echo round($verschil_percentage, 1); ?>%)
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Geen verkochte jachten met volledige data gevonden.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
