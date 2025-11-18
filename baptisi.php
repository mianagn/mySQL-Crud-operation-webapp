<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM BAPTISI WHERE kodikos_vaptisis = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Η βάπτιση διαγράφηκε επιτυχώς!";
        $messageType = "success";
    } else {
        $message = "Σφάλμα κατά τη διαγραφή: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kodikos_vaptisis = intval($_POST['kodikos_vaptisis']);
    $onoma_vaptizomenou = $_POST['onoma_vaptizomenou'];
    $imerominia_vaptisis = $_POST['imerominia_vaptisis'];
    $nonos = !empty($_POST['nonos']) ? $_POST['nonos'] : null;
    $kodikos_enorias = intval($_POST['kodikos_enorias']);
    
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $sql = "UPDATE BAPTISI SET onoma_vaptizomenou=?, imerominia_vaptisis=?, nonos=?, kodikos_enorias=? WHERE kodikos_vaptisis=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $onoma_vaptizomenou, $imerominia_vaptisis, $nonos, $kodikos_enorias, $kodikos_vaptisis);
    } else {
        $sql = "INSERT INTO BAPTISI (kodikos_vaptisis, onoma_vaptizomenou, imerominia_vaptisis, nonos, kodikos_enorias) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $kodikos_vaptisis, $onoma_vaptizomenou, $imerominia_vaptisis, $nonos, $kodikos_enorias);
    }
    
    if ($stmt->execute()) {
        $message = isset($_POST['action']) && $_POST['action'] == 'update' 
            ? "Η βάπτιση ενημερώθηκε επιτυχώς!" 
            : "Η βάπτιση προστέθηκε επιτυχώς!";
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
    $sql = "SELECT * FROM BAPTISI WHERE kodikos_vaptisis = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

$sql = "SELECT b.*, e.onoma_enorias, e.poli 
        FROM BAPTISI b 
        JOIN ENORIA e ON b.kodikos_enorias = e.kodikos_enorias 
        ORDER BY b.imerominia_vaptisis DESC, b.kodikos_vaptisis DESC";
$result = $conn->query($sql);

$enories = $conn->query("SELECT kodikos_enorias, onoma_enorias FROM ENORIA ORDER BY onoma_enorias");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Βαπτίσεων</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>
    <div class="container">
        <header>
            <h1>Διαχείριση Βαπτίσεων</h1>
            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">← Επιστροφή</a>
        </header>

        <div class="content-section">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="section-header">
                <h2>Κατάλογος Βαπτίσεων</h2>
                <button class="btn btn-primary" onclick="showModal('addModal')">
                    + Προσθήκη Βάπτισης
                </button>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Κωδικός</th>
                            <th>Όνομα Βαπτιζόμενου</th>
                            <th>Ημερομηνία Βάπτισης</th>
                            <th>Νονός</th>
                            <th>Ενορία</th>
                            <th>Πόλη</th>
                            <th>Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['kodikos_vaptisis']); ?></td>
                                <td><?php echo htmlspecialchars($row['onoma_vaptizomenou']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['imerominia_vaptisis'])); ?></td>
                                <td><?php echo $row['nonos'] ? htmlspecialchars($row['nonos']) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($row['onoma_enorias']); ?></td>
                                <td><?php echo htmlspecialchars($row['poli']); ?></td>
                                <td>
                                    <a href="?edit=1&id=<?php echo $row['kodikos_vaptisis']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.9em;">Επεξεργασία</a>
                                    <a href="?delete=1&id=<?php echo $row['kodikos_vaptisis']; ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 10px; font-size: 0.9em;"
                                       onclick="return confirmDelete('Είστε σίγουροι ότι θέλετε να διαγράψετε αυτή τη βάπτιση;');">
                                        Διαγραφή
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">Β</div>
                    <p>Δεν υπάρχουν καταχωρημένες βαπτίσεις.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="modal" style="<?php echo $editData ? 'display: block;' : ''; ?>">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><?php echo $editData ? 'Επεξεργασία Βάπτισης' : 'Προσθήκη Βάπτισης'; ?></h2>
            <form method="POST" action="" id="baptisiForm" onsubmit="return validateForm('baptisiForm');">
                <?php if ($editData): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="kodikos_vaptisis" value="<?php echo htmlspecialchars($editData['kodikos_vaptisis']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="kodikos_vaptisis">Κωδικός Βάπτισης *</label>
                    <input type="number" id="kodikos_vaptisis" name="kodikos_vaptisis" 
                           value="<?php echo $editData ? htmlspecialchars($editData['kodikos_vaptisis']) : ''; ?>" 
                           required <?php echo $editData ? 'readonly' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="onoma_vaptizomenou">Όνομα Βαπτιζόμενου *</label>
                    <input type="text" id="onoma_vaptizomenou" name="onoma_vaptizomenou" 
                           value="<?php echo $editData ? htmlspecialchars($editData['onoma_vaptizomenou']) : ''; ?>" 
                           maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="imerominia_vaptisis">Ημερομηνία Βάπτισης *</label>
                    <input type="date" id="imerominia_vaptisis" name="imerominia_vaptisis" 
                           value="<?php echo $editData ? htmlspecialchars($editData['imerominia_vaptisis']) : date('Y-m-d'); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="nonos">Νονός</label>
                    <input type="text" id="nonos" name="nonos" 
                           value="<?php echo $editData && $editData['nonos'] ? htmlspecialchars($editData['nonos']) : ''; ?>" 
                           maxlength="50">
                </div>

                <div class="form-group">
                    <label for="kodikos_enorias">Ενορία *</label>
                    <select id="kodikos_enorias" name="kodikos_enorias" required>
                        <option value="">-- Επιλέξτε Ενορία --</option>
                        <?php 
                        $enories->data_seek(0);
                        while ($enoria = $enories->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $enoria['kodikos_enorias']; ?>" 
                                    <?php echo ($editData && $editData['kodikos_enorias'] == $enoria['kodikos_enorias']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($enoria['onoma_enorias']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <?php echo $editData ? 'Ενημέρωση' : 'Προσθήκη'; ?>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeModal('addModal'); window.location.href='baptisi.php';">
                        Ακύρωση
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

