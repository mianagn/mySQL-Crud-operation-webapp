<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $conn->query("DELETE FROM EPIKENTROSEIS WHERE kodikos_pistou = $id");
    $conn->query("DELETE FROM KATIXITIKO_PISTON WHERE kodikos_pistou = $id");
    
    $sql = "DELETE FROM PISTOS WHERE kodikos_pistou = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Ο πιστός διαγράφηκε επιτυχώς!";
        $messageType = "success";
    } else {
        $message = "Σφάλμα κατά τη διαγραφή: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kodikos_pistou = intval($_POST['kodikos_pistou']);
    $onomateponymo = $_POST['onomateponymo'];
    $poli_katoikias = $_POST['poli_katoikias'];
    $ilikia = !empty($_POST['ilikia']) ? intval($_POST['ilikia']) : null;
    
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $sql = "UPDATE PISTOS SET onomateponymo=?, poli_katoikias=?, ilikia=? WHERE kodikos_pistou=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $onomateponymo, $poli_katoikias, $ilikia, $kodikos_pistou);
    } else {
        $sql = "INSERT INTO PISTOS (kodikos_pistou, onomateponymo, poli_katoikias, ilikia) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $kodikos_pistou, $onomateponymo, $poli_katoikias, $ilikia);
    }
    
    if ($stmt->execute()) {
        $message = isset($_POST['action']) && $_POST['action'] == 'update' 
            ? "Ο πιστός ενημερώθηκε επιτυχώς!" 
            : "Ο πιστός προστέθηκε επιτυχώς!";
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
    $sql = "SELECT * FROM PISTOS WHERE kodikos_pistou = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

$sql = "SELECT * FROM PISTOS ORDER BY kodikos_pistou";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Πιστών</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>
    <div class="container">
        <header>
            <h1>Διαχείριση Πιστών</h1>
            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">← Επιστροφή</a>
        </header>

        <div class="content-section">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="section-header">
                <h2>Κατάλογος Πιστών</h2>
                <button class="btn btn-primary" onclick="showModal('addModal')">
                    + Προσθήκη Πιστού
                </button>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Κωδικός</th>
                            <th>Ονοματεπώνυμο</th>
                            <th>Πόλη Κατοικίας</th>
                            <th>Ηλικία</th>
                            <th>Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['kodikos_pistou']); ?></td>
                                <td><?php echo htmlspecialchars($row['onomateponymo']); ?></td>
                                <td><?php echo htmlspecialchars($row['poli_katoikias']); ?></td>
                                <td><?php echo $row['ilikia'] ? htmlspecialchars($row['ilikia']) : '-'; ?></td>
                                <td>
                                    <a href="?edit=1&id=<?php echo $row['kodikos_pistou']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.9em;">Επεξεργασία</a>
                                    <a href="?delete=1&id=<?php echo $row['kodikos_pistou']; ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 10px; font-size: 0.9em;"
                                       onclick="return confirmDelete('Είστε σίγουροι ότι θέλετε να διαγράψετε τον πιστό <?php echo htmlspecialchars($row['onomateponymo']); ?>;');">
                                        Διαγραφή
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">Π</div>
                    <p>Δεν υπάρχουν καταχωρημένοι πιστοί.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="modal" style="<?php echo $editData ? 'display: block;' : ''; ?>">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><?php echo $editData ? 'Επεξεργασία Πιστού' : 'Προσθήκη Πιστού'; ?></h2>
            <form method="POST" action="" id="pistosForm" onsubmit="return validateForm('pistosForm');">
                <?php if ($editData): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="kodikos_pistou" value="<?php echo htmlspecialchars($editData['kodikos_pistou']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="kodikos_pistou">Κωδικός Πιστού *</label>
                    <input type="number" id="kodikos_pistou" name="kodikos_pistou" 
                           value="<?php echo $editData ? htmlspecialchars($editData['kodikos_pistou']) : ''; ?>" 
                           required <?php echo $editData ? 'readonly' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="onomateponymo">Ονοματεπώνυμο *</label>
                    <input type="text" id="onomateponymo" name="onomateponymo" 
                           value="<?php echo $editData ? htmlspecialchars($editData['onomateponymo']) : ''; ?>" 
                           maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="poli_katoikias">Πόλη Κατοικίας *</label>
                    <input type="text" id="poli_katoikias" name="poli_katoikias" 
                           value="<?php echo $editData ? htmlspecialchars($editData['poli_katoikias']) : ''; ?>" 
                           maxlength="40" required>
                </div>

                <div class="form-group">
                    <label for="ilikia">Ηλικία</label>
                    <input type="number" id="ilikia" name="ilikia" 
                           value="<?php echo $editData && $editData['ilikia'] ? htmlspecialchars($editData['ilikia']) : ''; ?>" 
                           min="0" max="120">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <?php echo $editData ? 'Ενημέρωση' : 'Προσθήκη'; ?>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeModal('addModal'); window.location.href='pistos.php';">
                        Ακύρωση
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

