<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Θρησκευτική Εφαρμογή - Αρχική Σελίδα</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>
    <div class="container">
        <header>
            <h1>Εφαρμογή διαχείρισης της βάσης δεδομένων Thriskeia</h1>
            <p class="subtitle">Προθήκη, διαγραφή, επεξεργασία και προβολή δεδομένων</p>
        </header>

        <nav class="main-nav">
            <div class="nav-grid">
                <a href="naos.php" class="nav-card">
                    <div class="nav-icon">Ν</div>
                    <h3>Ναοί</h3>
                    <p>Διαχείριση ναών</p>
                </a>
                
                <a href="pistos.php" class="nav-card">
                    <div class="nav-icon">Π</div>
                    <h3>Πιστοί</h3>
                    <p>Διαχείριση πιστών</p>
                </a>
                
                <a href="dwrea.php" class="nav-card">
                    <div class="nav-icon">Δ</div>
                    <h3>Δωρεές</h3>
                    <p>Διαχείριση δωρεών</p>
                </a>
                
                <a href="baptisi.php" class="nav-card">
                    <div class="nav-icon">Β</div>
                    <h3>Βαπτίσεις</h3>
                    <p>Διαχείριση βαπτίσεων</p>
                </a>
                
                <a href="klirikos.php" class="nav-card">
                    <div class="nav-icon">Κ</div>
                    <h3>Κληρικοί</h3>
                    <p>Διαχείριση κληρικών</p>
                </a>
                
                <a href="katixitiko.php" class="nav-card">
                    <div class="nav-icon">Κ</div>
                    <h3>Κατηχητικό</h3>
                    <p>Διαχείριση κατηχητικού</p>
                </a>
            </div>
        </nav>

        <section class="stats-section">
            <h2>Στατιστικά</h2>
            <div class="stats-grid">
                <?php
                $stats = [];
                
                $result = $conn->query("SELECT COUNT(*) as count FROM NAOS");
                $stats['naoi'] = $result->fetch_assoc()['count'];
                
                $result = $conn->query("SELECT COUNT(*) as count FROM KLHRIKOS");
                $stats['klirikoi'] = $result->fetch_assoc()['count'];
                
                $result = $conn->query("SELECT COUNT(*) as count FROM PISTOS");
                $stats['pistoi'] = $result->fetch_assoc()['count'];
                
                $result = $conn->query("SELECT COUNT(*) as count, SUM(poso) as total FROM DWREA");
                $row = $result->fetch_assoc();
                $stats['dwrees'] = $row['count'];
                $stats['synolo_dwreon'] = $row['total'] ?? 0;
                ?>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['naoi']; ?></div>
                    <div class="stat-label">Ναοί</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['klirikoi']; ?></div>
                    <div class="stat-label">Κληρικοί</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pistoi']; ?></div>
                    <div class="stat-label">Πιστοί</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['dwrees']; ?></div>
                    <div class="stat-label">Δωρεές</div>
                </div>
                
                <div class="stat-card highlight">
                    <div class="stat-number">€<?php echo number_format($stats['synolo_dwreon'], 2); ?></div>
                    <div class="stat-label">Συνολικό Ποσό Δωρεών</div>
                </div>
            </div>
        </section>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>

