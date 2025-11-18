<?php
require_once 'config.php';

$message = '';
$messageType = '';

if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM NAOS WHERE kodikos_naou = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Ο ναός διαγράφηκε επιτυχώς!";
        $messageType = "success";
    } else {
        $message = "Σφάλμα κατά τη διαγραφή: " . $conn->error;
        $messageType = "error";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kodikos_naou = intval($_POST['kodikos_naou']);
    $onoma = $_POST['onoma'];
    $xoritikotita = intval($_POST['xoritikotita']);
    $etos_kataskevis = !empty($_POST['etos_kataskevis']) ? intval($_POST['etos_kataskevis']) : null;
    $kodikos_enorias = intval($_POST['kodikos_enorias']);
    
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $sql = "UPDATE NAOS SET onoma=?, xoritikotita=?, etos_kataskevis=?, kodikos_enorias=? WHERE kodikos_naou=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiii", $onoma, $xoritikotita, $etos_kataskevis, $kodikos_enorias, $kodikos_naou);
    } else {
        $sql = "INSERT INTO NAOS (kodikos_naou, onoma, xoritikotita, etos_kataskevis, kodikos_enorias) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiii", $kodikos_naou, $onoma, $xoritikotita, $etos_kataskevis, $kodikos_enorias);
    }
    
    if ($stmt->execute()) {
        $message = isset($_POST['action']) && $_POST['action'] == 'update' 
            ? "Ο ναός ενημερώθηκε επιτυχώς!" 
            : "Ο ναός προστέθηκε επιτυχώς!";
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
    $sql = "SELECT * FROM NAOS WHERE kodikos_naou = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}

$sql = "SELECT n.*, e.onoma_enorias, e.poli 
        FROM NAOS n 
        JOIN ENORIA e ON n.kodikos_enorias = e.kodikos_enorias 
        ORDER BY n.kodikos_naou";
$result = $conn->query($sql);

$enories = $conn->query("SELECT kodikos_enorias, onoma_enorias FROM ENORIA ORDER BY onoma_enorias");
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Ναών</title>
    <link rel="stylesheet" href="css/style.css?v=3">
</head>
<body>
    <div class="container">
        <header>
            <h1>Διαχείριση Ναών</h1>
            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">← Επιστροφή</a>
        </header>

        <div class="content-section">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="section-header">
                <h2>Κατάλογος Ναών</h2>
                <button class="btn btn-primary" onclick="showModal('addModal')">
                    + Προσθήκη Ναού
                </button>
            </div>

            <?php if ($result && $result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Κωδικός</th>
                            <th>Όνομα</th>
                            <th>Χωρητικότητα</th>
                            <th>Έτος Κατασκευής</th>
                            <th>Ενορία</th>
                            <th>Πόλη</th>
                            <th>Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['kodikos_naou']); ?></td>
                                <td><?php echo htmlspecialchars($row['onoma']); ?></td>
                                <td><?php echo htmlspecialchars($row['xoritikotita']); ?></td>
                                <td><?php echo $row['etos_kataskevis'] ? htmlspecialchars($row['etos_kataskevis']) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($row['onoma_enorias']); ?></td>
                                <td><?php echo htmlspecialchars($row['poli']); ?></td>
                                <td>
                                    <a href="?edit=1&id=<?php echo $row['kodikos_naou']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.9em;">Επεξεργασία</a>
                                    <a href="?delete=1&id=<?php echo $row['kodikos_naou']; ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 5px 10px; font-size: 0.9em;"
                                       onclick="return confirmDelete('Είστε σίγουροι ότι θέλετε να διαγράψετε τον ναό <?php echo htmlspecialchars($row['onoma']); ?>;');">
                                        Διαγραφή
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">Ν</div>
                    <p>Δεν υπάρχουν καταχωρημένοι ναοί.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="addModal" class="modal" style="<?php echo $editData ? 'display: block;' : ''; ?>">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2><?php echo $editData ? 'Επεξεργασία Ναού' : 'Προσθήκη Ναού'; ?></h2>
            <form method="POST" action="" id="naosForm" onsubmit="return validateForm('naosForm');">
                <?php if ($editData): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="kodikos_naou" value="<?php echo htmlspecialchars($editData['kodikos_naou']); ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="kodikos_naou">Κωδικός Ναού *</label>
                    <input type="number" id="kodikos_naou" name="kodikos_naou" 
                           value="<?php echo $editData ? htmlspecialchars($editData['kodikos_naou']) : ''; ?>" 
                           required <?php echo $editData ? 'readonly' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="onoma">Όνομα *</label>
                    <input type="text" id="onoma" name="onoma" 
                           value="<?php echo $editData ? htmlspecialchars($editData['onoma']) : ''; ?>" 
                           maxlength="40" required>
                </div>

                <div class="form-group">
                    <label for="xoritikotita">Χωρητικότητα *</label>
                    <input type="number" id="xoritikotita" name="xoritikotita" 
                           value="<?php echo $editData ? htmlspecialchars($editData['xoritikotita']) : ''; ?>" 
                           min="1" required>
                </div>

                <div class="form-group">
                    <label for="etos_kataskevis">Έτος Κατασκευής</label>
                    <input type="number" id="etos_kataskevis" name="etos_kataskevis" 
                           value="<?php echo $editData && $editData['etos_kataskevis'] ? htmlspecialchars($editData['etos_kataskevis']) : ''; ?>" 
                           min="1000" max="2100">
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
                    <button type="button" class="btn btn-danger" onclick="closeModal('addModal'); window.location.href='naos.php';">
                        Ακύρωση
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>

