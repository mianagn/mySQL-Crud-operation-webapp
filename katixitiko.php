<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $conn->query("DELETE FROM KATIXITIKO_PISTON WHERE kodikos_katihitikou = $id");
    
    $sql = "DELETE FROM KATIXITIKO WHERE kodikos_katihitikou = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Το κατηχητικό διαγράφηκε επιτυχώς!";
        $messageType = "success";
    } else {
        $message = "Σφάλμα κατά τη διαγραφή: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kodikos_katihitikou = intval($_POST['kodikos_katihitikou']);
    $imera = $_POST['imera'];
    $ora = $_POST['ora'];
    $kodikos_naou = intval($_POST['kodikos_naou']);
    
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $sql = "UPDATE KATIXITIKO SET imera=?, ora=?, kodikos_naou=? WHERE kodikos_katihitikou=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $imera, $ora, $kodikos_naou, $kodikos_katihitikou);
    } else {
        $sql = "INSERT INTO KATIXITIKO (kodikos_katihitikou, imera, ora, kodikos_naou) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $kodikos_katihitikou, $imera, $ora, $kodikos_naou);
    }
    
    if ($stmt->execute()) {
        $message = isset($_POST['action']) && $_POST['action'] == 'update' 
            ? "Το κατηχητικό ενημερώθηκε επιτυχώς!" 
            : "Το κατηχητικό προστέθηκε επιτυχώς!";
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
    $sql = "SELECT * FROM KATIXITIKO WHERE kodikos_katihitikou = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

$sql = "SELECT k.*, n.onoma as onoma_naou, 
        (SELECT COUNT(*) FROM KATIXITIKO_PISTON kp WHERE kp.kodikos_katihitikou = k.kodikos_katihitikou) as posoi_pistoi
        FROM KATIXITIKO k 
        JOIN NAOS n ON k.kodikos_naou = n.kodikos_naou 
        ORDER BY k.kodikos_katihitikou";
$result = $conn->query($sql);

$naoi = $conn->query("SELECT kodikos_naou, onoma FROM NAOS ORDER BY onoma");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Κατηχητικού</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>
    <div class="container">
        <header>
            <h1>Διαχείριση Κατηχητικού</h1>
            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">← Επιστροφή</a>
        </header>

        <div class="content-section">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="section-header">
                <h2>Κατάλογος Κατηχητικού</h2>
                <button class="btn btn-primary" onclick="showModal('addModal')">
                    + Προσθήκη Κατηχητικού
                </button>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Κωδικός</th>
                            <th>Ημέρα</th>
                            <th>Ώρα</th>
                            <th>Ναός</th>
                            <th>Πιστοί</th>
                            <th>Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['kodikos_katihitikou']); ?></td>
                                <td><?php echo htmlspecialchars($row['imera']); ?></td>
                                <td><?php echo date('H:i', strtotime($row['ora'])); ?></td>
                                <td><?php echo htmlspecialchars($row['onoma_naou']); ?></td>
                                <td><?php echo htmlspecialchars($row['posoi_pistoi']); ?></td>
                                <td>
                                    <a href="?edit=1&id=<?php echo $row['kodikos_katihitikou']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.9em;">Επεξεργασία</a>
                                    <a href="?delete=1&id=<?php echo $row['kodikos_katihitikou']; ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 10px; font-size: 0.9em;"
                                       onclick="return confirmDelete('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το κατηχητικό;');">
                                        Διαγραφή
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">Κ</div>
                    <p>Δεν υπάρχουν καταχωρημένα κατηχητικά.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="modal" style="<?php echo $editData ? 'display: block;' : ''; ?>">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><?php echo $editData ? 'Επεξεργασία Κατηχητικού' : 'Προσθήκη Κατηχητικού'; ?></h2>
            <form method="POST" action="" id="katixitikoForm" onsubmit="return validateForm('katixitikoForm');">
                <?php if ($editData): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="kodikos_katihitikou" value="<?php echo htmlspecialchars($editData['kodikos_katihitikou']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="kodikos_katihitikou">Κωδικός Κατηχητικού *</label>
                    <input type="number" id="kodikos_katihitikou" name="kodikos_katihitikou" 
                           value="<?php echo $editData ? htmlspecialchars($editData['kodikos_katihitikou']) : ''; ?>" 
                           required <?php echo $editData ? 'readonly' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="imera">Ημέρα *</label>
                    <select id="imera" name="imera" required>
                        <option value="">-- Επιλέξτε Ημέρα --</option>
                        <option value="Savvato" <?php echo ($editData && $editData['imera'] == 'Savvato') ? 'selected' : ''; ?>>Σάββατο</option>
                        <option value="Kiraki" <?php echo ($editData && $editData['imera'] == 'Kiraki') ? 'selected' : ''; ?>>Κυριακή</option>
                        <option value="Kiriaki" <?php echo ($editData && $editData['imera'] == 'Kiriaki') ? 'selected' : ''; ?>>Κυριακή</option>
                        <option value="Deftera" <?php echo ($editData && $editData['imera'] == 'Deftera') ? 'selected' : ''; ?>>Δευτέρα</option>
                        <option value="Triti" <?php echo ($editData && $editData['imera'] == 'Triti') ? 'selected' : ''; ?>>Τρίτη</option>
                        <option value="Tetarti" <?php echo ($editData && $editData['imera'] == 'Tetarti') ? 'selected' : ''; ?>>Τετάρτη</option>
                        <option value="Pempti" <?php echo ($editData && $editData['imera'] == 'Pempti') ? 'selected' : ''; ?>>Πέμπτη</option>
                        <option value="Paraskevi" <?php echo ($editData && $editData['imera'] == 'Paraskevi') ? 'selected' : ''; ?>>Παρασκευή</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ora">Ώρα *</label>
                    <input type="time" id="ora" name="ora" 
                           value="<?php echo $editData ? substr($editData['ora'], 0, 5) : ''; ?>" 
                           required>
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
                    <button type="button" class="btn btn-danger" onclick="closeModal('addModal'); window.location.href='katixitiko.php';">
                        Ακύρωση
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

