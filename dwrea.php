<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM DWREA WHERE kodikos_doreas = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Η δωρεά διαγράφηκε επιτυχώς!";
        $messageType = "success";
    } else {
        $message = "Σφάλμα κατά τη διαγραφή: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kodikos_doreas = !empty($_POST['kodikos_doreas']) ? intval($_POST['kodikos_doreas']) : null;
    $imerominia = $_POST['imerominia'];
    $poso = floatval($_POST['poso']);
    $kodikos_naou = intval($_POST['kodikos_naou']);
    
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $sql = "UPDATE DWREA SET imerominia=?, poso=?, kodikos_naou=? WHERE kodikos_doreas=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sddi", $imerominia, $poso, $kodikos_naou, $kodikos_doreas);
    } else {
        $sql = "INSERT INTO DWREA (imerominia, poso, kodikos_naou) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdd", $imerominia, $poso, $kodikos_naou);
    }
    
    if ($stmt->execute()) {
        $message = isset($_POST['action']) && $_POST['action'] == 'update' 
            ? "Η δωρεά ενημερώθηκε επιτυχώς!" 
            : "Η δωρεά προστέθηκε επιτυχώς!";
        $messageType = "success";
    } else {
        $message = "Σφάλμα: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

$editData = null;
if (isset($_GET['edit']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM DWREA WHERE kodikos_doreas = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

$sql = "SELECT d.*, n.onoma as onoma_naou, e.onoma_enorias 
        FROM DWREA d 
        JOIN NAOS n ON d.kodikos_naou = n.kodikos_naou
        JOIN ENORIA e ON n.kodikos_enorias = e.kodikos_enorias
        ORDER BY d.imerominia DESC, d.kodikos_doreas DESC";
$result = $conn->query($sql);

$totalResult = $conn->query("SELECT SUM(poso) as total FROM DWREA");
$total = $totalResult->fetch_assoc()['total'] ?? 0;

$naoi = $conn->query("SELECT kodikos_naou, onoma FROM NAOS ORDER BY onoma");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Δωρεών</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>
    <div class="container">
        <header>
            <h1>Διαχείριση Δωρεών</h1>
            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">← Επιστροφή</a>
        </header>

        <div class="content-section">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="section-header">
                <h2>Κατάλογος Δωρεών</h2>
                <div>
                    <span style="font-size: 1.2em; font-weight: bold; color: var(--success-color); margin-right: 20px;">
                        Συνολικό Ποσό: €<?php echo number_format($total, 2); ?>
                    </span>
                    <button class="btn btn-primary" onclick="showModal('addModal')">
                        + Προσθήκη Δωρεάς
                    </button>
                </div>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Κωδικός</th>
                            <th>Ημερομηνία</th>
                            <th>Ποσό</th>
                            <th>Ναός</th>
                            <th>Ενορία</th>
                            <th>Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['kodikos_doreas']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['imerominia'])); ?></td>
                                <td style="font-weight: bold; color: var(--success-color);">
                                    €<?php echo number_format($row['poso'], 2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['onoma_naou']); ?></td>
                                <td><?php echo htmlspecialchars($row['onoma_enorias']); ?></td>
                                <td>
                                    <a href="?edit=1&id=<?php echo $row['kodikos_doreas']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.9em;">Επεξεργασία</a>
                                    <a href="?delete=1&id=<?php echo $row['kodikos_doreas']; ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 10px; font-size: 0.9em;"
                                       onclick="return confirmDelete('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή τη δωρεά;');">
                                        Διαγραφή
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">Δ</div>
                    <p>Δεν υπάρχουν καταχωρημένες δωρεές.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="modal" style="<?php echo $editData ? 'display: block;' : ''; ?>">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><?php echo $editData ? 'Επεξεργασία Δωρεάς' : 'Προσθήκη Δωρεάς'; ?></h2>
            <form method="POST" action="" id="dwreaForm" onsubmit="return validateForm('dwreaForm');">
                <?php if ($editData): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="kodikos_doreas" value="<?php echo htmlspecialchars($editData['kodikos_doreas']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="imerominia">Ημερομηνία *</label>
                    <input type="date" id="imerominia" name="imerominia" 
                           value="<?php echo $editData ? htmlspecialchars($editData['imerominia']) : date('Y-m-d'); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="poso">Ποσό (€) *</label>
                    <input type="number" id="poso" name="poso" 
                           value="<?php echo $editData ? htmlspecialchars($editData['poso']) : ''; ?>" 
                           step="0.01" min="0.01" required>
                </div>

                <div class="form-group">
                    <label for="kodikos_naou">Ναός *</label>
                    <select id="kodikos_naou" name="kodikos_naou" required>
                        <option value="">-- Επιλέξτε Ναό --</option>
                        <?php 
                        $naoi->data_seek(0);
                        while ($naos = $naoi->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $naos['kodikos_naou']; ?>" 
                                    <?php echo ($editData && $editData['kodikos_naou'] == $naos['kodikos_naou']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($naos['onoma']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <?php echo $editData ? 'Ενημέρωση' : 'Προσθήκη'; ?>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeModal('addModal'); window.location.href='dwrea.php';">
                        Ακύρωση
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

