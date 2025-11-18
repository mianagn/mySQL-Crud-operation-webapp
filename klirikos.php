<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM KLHRIKOS WHERE kodikos_klirikou = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        $message = "Ο κληρικός διαγράφηκε επιτυχώς!";
        $messageType = "success";
    } else {
        $message = "Σφάλμα κατά τη διαγραφή: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kodikos_klirikou = strtoupper(trim($_POST['kodikos_klirikou']));
    $onomateponymo = $_POST['onomateponymo'];
    $vathmos = $_POST['vathmos'];
    $ilikia = !empty($_POST['ilikia']) ? intval($_POST['ilikia']) : null;
    $kodikos_naou = intval($_POST['kodikos_naou']);
    
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $sql = "UPDATE KLHRIKOS SET onomateponymo=?, vathmos=?, ilikia=?, kodikos_naou=? WHERE kodikos_klirikou=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiis", $onomateponymo, $vathmos, $ilikia, $kodikos_naou, $kodikos_klirikou);
    } else {
        $sql = "INSERT INTO KLHRIKOS (kodikos_klirikou, onomateponymo, vathmos, ilikia, kodikos_naou) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $kodikos_klirikou, $onomateponymo, $vathmos, $ilikia, $kodikos_naou);
    }
    
    if ($stmt->execute()) {
        $message = isset($_POST['action']) && $_POST['action'] == 'update' 
            ? "Ο κληρικός ενημερώθηκε επιτυχώς!" 
            : "Ο κληρικός προστέθηκε επιτυχώς!";
        $messageType = "success";
    } else {
        $message = "Σφάλμα: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

$editData = null;
if (isset($_GET['edit']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM KLHRIKOS WHERE kodikos_klirikou = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

$sql = "SELECT k.*, n.onoma as onoma_naou 
        FROM KLHRIKOS k 
        JOIN NAOS n ON k.kodikos_naou = n.kodikos_naou 
        ORDER BY k.kodikos_klirikou";
$result = $conn->query($sql);

$naoi = $conn->query("SELECT kodikos_naou, onoma FROM NAOS ORDER BY onoma");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Κληρικών</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>
    <div class="container">
        <header>
            <h1>Διαχείριση Κληρικών</h1>
            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">← Επιστροφή</a>
        </header>

        <div class="content-section">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="section-header">
                <h2>Κατάλογος Κληρικών</h2>
                <button class="btn btn-primary" onclick="showModal('addModal')">
                    + Προσθήκη Κληρικού
                </button>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Κωδικός</th>
                            <th>Ονοματεπώνυμο</th>
                            <th>Βαθμός</th>
                            <th>Ηλικία</th>
                            <th>Ναός</th>
                            <th>Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['kodikos_klirikou']); ?></td>
                                <td><?php echo htmlspecialchars($row['onomateponymo']); ?></td>
                                <td><?php echo htmlspecialchars($row['vathmos']); ?></td>
                                <td><?php echo $row['ilikia'] ? htmlspecialchars($row['ilikia']) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($row['onoma_naou']); ?></td>
                                <td>
                                    <a href="?edit=1&id=<?php echo urlencode($row['kodikos_klirikou']); ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.9em;">Επεξεργασία</a>
                                    <a href="?delete=1&id=<?php echo urlencode($row['kodikos_klirikou']); ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 10px; font-size: 0.9em;"
                                       onclick="return confirmDelete('Είστε σίγουροι ότι θέλετε να διαγράψετε τον κληρικό <?php echo htmlspecialchars($row['onomateponymo']); ?>;');">
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
                    <p>Δεν υπάρχουν καταχωρημένοι κληρικοί.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="modal" style="<?php echo $editData ? 'display: block;' : ''; ?>">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><?php echo $editData ? 'Επεξεργασία Κληρικού' : 'Προσθήκη Κληρικού'; ?></h2>
            <form method="POST" action="" id="klirikosForm" onsubmit="return validateForm('klirikosForm');">
                <?php if ($editData): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="kodikos_klirikou" value="<?php echo htmlspecialchars($editData['kodikos_klirikou']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="kodikos_klirikou">Κωδικός Κληρικού (5 χαρακτήρες) *</label>
                    <input type="text" id="kodikos_klirikou" name="kodikos_klirikou" 
                           value="<?php echo $editData ? htmlspecialchars($editData['kodikos_klirikou']) : ''; ?>" 
                           maxlength="5" minlength="5" required 
                           pattern="[A-Z0-9]{5}" 
                           title="5 χαρακτήρες (A-Z, 0-9)"
                           <?php echo $editData ? 'readonly' : ''; ?>>
                    <small style="color: #7f8c8d;">Π.χ. KL001</small>
                </div>

                <div class="form-group">
                    <label for="onomateponymo">Ονοματεπώνυμο *</label>
                    <input type="text" id="onomateponymo" name="onomateponymo" 
                           value="<?php echo $editData ? htmlspecialchars($editData['onomateponymo']) : ''; ?>" 
                           maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="vathmos">Βαθμός *</label>
                    <select id="vathmos" name="vathmos" required>
                        <option value="">-- Επιλέξτε Βαθμό --</option>
                        <option value="Iereas" <?php echo ($editData && $editData['vathmos'] == 'Iereas') ? 'selected' : ''; ?>>Ιερέας</option>
                        <option value="Diakonos" <?php echo ($editData && $editData['vathmos'] == 'Diakonos') ? 'selected' : ''; ?>>Διάκονος</option>
                        <option value="Presviteros" <?php echo ($editData && $editData['vathmos'] == 'Presviteros') ? 'selected' : ''; ?>>Πρεσβύτερος</option>
                        <option value="Episkopos" <?php echo ($editData && $editData['vathmos'] == 'Episkopos') ? 'selected' : ''; ?>>Επίσκοπος</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ilikia">Ηλικία</label>
                    <input type="number" id="ilikia" name="ilikia" 
                           value="<?php echo $editData && $editData['ilikia'] ? htmlspecialchars($editData['ilikia']) : ''; ?>" 
                           min="18" max="100">
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
                    <button type="button" class="btn btn-danger" onclick="closeModal('addModal'); window.location.href='klirikos.php';">
                        Ακύρωση
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

