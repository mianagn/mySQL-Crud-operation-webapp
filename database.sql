-- ============================================
-- ΘΡΗΣΚΕΥΤΙΚΗ ΕΦΑΡΜΟΓΗ - SQL SCRIPT
-- ============================================
-- Αυτό το αρχείο περιέχει όλο τον κώδικα SQL
-- που απαιτείται για τη δημιουργία της βάσης
-- δεδομένων και την εισαγωγή των δεδομένων
-- ============================================

-- Δημιουργία Βάσης Δεδομένων με όνομα 'thriskeia'
CREATE DATABASE IF NOT EXISTS thriskeia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE thriskeia;

-- Απαραίτητο για καθαρή επανεκκίνηση (Διαγραφή πινάκων, views, procedures, triggers)
DROP TABLE IF EXISTS KATIXITIKO_PISTON;
DROP TABLE IF EXISTS DWREA;
DROP TABLE IF EXISTS BAPTISI;
DROP TABLE IF EXISTS EPIKENTROSEIS;
DROP TABLE IF EXISTS KLHRIKOS;
DROP TABLE IF EXISTS KATIXITIKO;
DROP TABLE IF EXISTS PISTOS;
DROP TABLE IF EXISTS NAOS;
DROP TABLE IF EXISTS ENORIA;

DROP VIEW IF EXISTS view_episkepsimotita_naon;
DROP PROCEDURE IF EXISTS prosthiki_neas_doreas;
DROP TRIGGER IF EXISTS trig_update_arithmos_naon_insert;
DROP TRIGGER IF EXISTS trig_update_arithmos_naon_delete;

-- ============================================
-- 1. ZITOUMENO 2: ΔΗΜΙΟΥΡΓΙΑ ΠΙΝΑΚΩΝ
-- ============================================

-- Πίνακας Ενοριών
CREATE TABLE ENORIA (
    kodikos_enorias INT PRIMARY KEY,
    onoma_enorias   VARCHAR(60) NOT NULL,
    poli            VARCHAR(40) NOT NULL,
    arithmos_naon   INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Πίνακας Ναών
CREATE TABLE NAOS (
    kodikos_naou     INT PRIMARY KEY,
    onoma            VARCHAR(40) NOT NULL,
    xoritikotita     INT NOT NULL,
    etos_kataskevis  INT,
    kodikos_enorias  INT NOT NULL,
    FOREIGN KEY (kodikos_enorias) REFERENCES ENORIA(kodikos_enorias) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Πίνακας Κληρικών
CREATE TABLE KLHRIKOS (
    kodikos_klirikou CHAR(5) PRIMARY KEY,
    onomateponymo    VARCHAR(50) NOT NULL,
    vathmos          VARCHAR(20) NOT NULL,
    ilikia           INT,
    kodikos_naou     INT NOT NULL,
    FOREIGN KEY (kodikos_naou) REFERENCES NAOS(kodikos_naou) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Πίνακας Πιστών
CREATE TABLE PISTOS (
    kodikos_pistou   INT PRIMARY KEY,
    onomateponymo    VARCHAR(50) NOT NULL,
    poli_katoikias   VARCHAR(40) NOT NULL,
    ilikia           INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Συσχέτιση Πιστός-Ναός (Ν:Μ)
CREATE TABLE EPIKENTROSEIS (
    kodikos_pistou   INT NOT NULL,
    kodikos_naou     INT NOT NULL,
    PRIMARY KEY (kodikos_pistou, kodikos_naou),
    FOREIGN KEY (kodikos_pistou) REFERENCES PISTOS(kodikos_pistou) ON DELETE CASCADE,
    FOREIGN KEY (kodikos_naou)   REFERENCES NAOS(kodikos_naou) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Πίνακας Δωρεών (Συσχέτιση Ναός-Δωρεά 1:Ν)
CREATE TABLE DWREA (
    kodikos_doreas   INT PRIMARY KEY AUTO_INCREMENT,
    imerominia       DATE NOT NULL,
    poso             DECIMAL(10,2) NOT NULL,
    kodikos_naou     INT NOT NULL,
    FOREIGN KEY (kodikos_naou) REFERENCES NAOS(kodikos_naou) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Πίνακας Βαπτίσεων (Συσχέτιση Ενορία-Βάπτιση 1:Ν)
CREATE TABLE BAPTISI (
    kodikos_vaptisis      INT PRIMARY KEY,
    onoma_vaptizomenou    VARCHAR(50) NOT NULL,
    imerominia_vaptisis   DATE NOT NULL,
    nonos                 VARCHAR(50),
    kodikos_enorias       INT NOT NULL,
    FOREIGN KEY (kodikos_enorias) REFERENCES ENORIA(kodikos_enorias) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Πίνακας Κατηχητικού (Συσχέτιση Ναός-Κατηχητικό 1:Ν)
CREATE TABLE KATIXITIKO (
    kodikos_katihitikou INT PRIMARY KEY,
    imera               VARCHAR(10) NOT NULL,
    ora                 TIME NOT NULL,
    kodikos_naou        INT NOT NULL,
    FOREIGN KEY (kodikos_naou) REFERENCES NAOS(kodikos_naou) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Πίνακας Συσχέτισης Κατηχητικού-Πιστού (Ν:Μ)
CREATE TABLE KATIXITIKO_PISTON (
    kodikos_pistou      INT NOT NULL,
    kodikos_katihitikou INT NOT NULL,
    PRIMARY KEY (kodikos_pistou, kodikos_katihitikou),
    FOREIGN KEY (kodikos_pistou)      REFERENCES PISTOS(kodikos_pistou) ON DELETE CASCADE,
    FOREIGN KEY (kodikos_katihitikou) REFERENCES KATIXITIKO(kodikos_katihitikou) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. ZITOUMENO 3: ΕΙΣΑΓΩΓΗ ΔΕΔΟΜΕΝΩΝ
-- ============================================

INSERT INTO ENORIA (kodikos_enorias, onoma_enorias, poli, arithmos_naon) VALUES
(1, 'Αγίου Δημητρίου', 'Θεσσαλονίκη', 3),
(2, 'Κοιμήσεως Θεοτόκου', 'Αθήνα', 2),
(3, 'Αγίας Τριάδος', 'Πάτρα', 1),
(4, 'Αγίου Νικολάου', 'Βόλος', 1),
(5, 'Παναγίας Φανερωμένης', 'Χανιά', 1);

INSERT INTO NAOS (kodikos_naou, onoma, xoritikotita, etos_kataskevis, kodikos_enorias) VALUES
(101, 'Agios Petros', 500, 1925, 1),
(102, 'Agia Eleni', 300, 1931, 1),
(103,  'Agia Sevasti', 400, 1949, 1),
(201, 'Mitropoli Athinon', 1200, 1862, 2),
(202, 'Agios Eleftherios', 200, 1400, 2),
(301, 'Agios Andreas', 2500, 1974, 3),
(401, 'Agios Nikolaos', 600, 1920, 4),
(501, 'Panagia Pentagiotissa', 350, 1028, 5);

INSERT INTO KLHRIKOS (kodikos_klirikou, onomateponymo, vathmos, ilikia, kodikos_naou) VALUES
('KL001', 'Evangelos Bairamis', 'Iereas', 46, 101),
('KL002', 'Ioannis Tsimperidis', 'Diakonos', 77,  101),
('KL003', 'Petros Konelis', 'Episkopos', 58, 102),
('KL004', 'Sevasti karagiozoglou', 'Iereas', 67, 201),
('KL005', 'Xristodoulos Pseutidis', 'Presviteros', 44, 201),
('KL006', 'Aggelos Tsavdaridos', 'Presviteros', 55, 301),
('KL007', 'Georgios Tsiggouneos', 'Diakonos', 45, 401);

INSERT INTO PISTOS (kodikos_pistou, onomateponymo, poli_katoikias, ilikia) VALUES
(1001, 'Pantelis Mporovilas', 'Ptolemaida', 39),
(1002, 'Vasileios Mantzoufas', 'Veroia', 28),
(1003, 'Michalis Muriel', 'Kozani', 24),
(1004, 'Apostolis Prichtis', 'Athina', 19),
(1005, 'Ioannis Iatridis', 'Patra', 16),
(1006, 'Kostantinos Ligomilitos', 'Volos', 52),
(1007, 'Ioanna Nomikou', 'Chania', 14),
(1008, 'Kirillos Megaloxaros', 'Thessaloniki', 12);

INSERT INTO EPIKENTROSEIS (kodikos_pistou, kodikos_naou) VALUES
(1001, 101), (1001, 102), (1001, 103),
(1002, 101), (1002, 102),
(1003, 201), (1003, 202),
(1004, 201),
(1005, 301),
(1006, 401),
(1007, 501);

INSERT INTO DWREA (kodikos_doreas, imerominia, poso, kodikos_naou) VALUES
(1, '2025-01-05', 150.00, 101),
(2, '2025-01-12', 85.50, 102),
(3, '2025-02-01', 500.00, 103),
(4, '2025-01-10', 200.00, 201),
(5, '2025-01-15', 120.00, 202),
(6, '2025-02-20', 300.00, 301),
(7, '2025-03-01', 50.00, 401);

INSERT INTO BAPTISI (kodikos_vaptisis, onoma_vaptizomenou, imerominia_vaptisis, nonos, kodikos_enorias) VALUES
(1, 'Alexandros Stergiadis', '2025-01-12', 'Stefanos Xaslaris', 1),
(2, 'Miltiadis Bozatzis', '2025-02-08', 'Konstantinos Xazemenos', 1),
(3, 'Nikolaos Gatos', '2025-01-25', 'Sotirios Valentinou', 2),
(4, 'Spiridon Meraklis', '2025-03-01', 'Maria Odigou', 3),
(5, 'Theofanis Klamateros', '2025-02-15', 'Panagiotis Psaromenos', 1);

INSERT INTO KATIXITIKO (kodikos_katihitikou, imera, ora, kodikos_naou) VALUES
(1, 'Savvato', '17:00:00', 101),
(2, 'Savvato', '18:30:00', 101),
(3, 'Kiraki', '11:00:00', 201),
(4, 'Kiriaki', '12:30:00', 301),
(5, 'Savvato', '10:00:00', 401);

INSERT INTO KATIXITIKO_PISTON (kodikos_pistou, kodikos_katihitikou) VALUES
(1008, 1),
(1008, 2),
(1004, 3),
(1005, 4),
(1007, 5);

-- ============================================
-- 3. ZITOUMENO 4: ΕΡΩΤΗΜΑΤΑ
-- ============================================

-- 4a. Βρίσκουμε όλους τους πιστούς που το επώνυμό τους αρχίζει από "Μ" ή περιέχει "idis". (LIKE)
SELECT kodikos_pistou, onomateponymo, poli_katoikias, ilikia
FROM PISTOS
WHERE onomateponymo LIKE 'Μ%' OR onomateponymo LIKE '%idis%'
ORDER BY onomateponymo;

-- 4b. Βρίσκουμε όλες τις δωρεές πάνω από 150€, ταξινομημένες από τη μεγαλύτερη προς τη μικρότερη. (Φιλτράρισμα & Ταξινόμηση)
SELECT
    d.kodikos_doreas,
    d.imerominia,
    d.poso,
    n.onoma AS onoma_naou,
    e.onoma_enorias
FROM DWREA d
JOIN NAOS n ON d.kodikos_naou = n.kodikos_naou
JOIN ENORIA e ON n.kodikos_enorias = e.kodikos_enorias
WHERE d.poso > 150
ORDER BY d.poso DESC, d.imerominia ASC;

-- 4c. Βρίσκουμε τους κληρικούς που είναι ανώτεροι ιερατικοί βαθμοί (όχι διάκονοι) AND κάτω από 60 ετών AND δεν υπηρετούν στους ναούς 101 Ή 201. (Λογικοί Τελεστές: OR, AND, NOT IN)
SELECT
    k.kodikos_klirikou,
    k.onomateponymo,
    k.vathmos,
    k.ilikia,
    n.onoma AS naos
FROM KLHRIKOS k
JOIN NAOS n ON k.kodikos_naou = n.kodikos_naou
WHERE (k.vathmos = 'Iereas'
    OR k.vathmos = 'Presviteros'
    OR k.vathmos = 'Episkopos')
  AND k.ilikia < 60
  AND (k.kodikos_naou NOT IN (101, 201))
ORDER BY k.ilikia ASC;

-- ============================================
-- 4. ZITOUMENO 5 & 6: ΣΥΝΑΘΡΟΙΣΤΙΚΕΣ & JOINS
-- ============================================

-- ZITOUMENO 5a. Πόσοι πιστοί επισκέφθηκαν τον κάθε ναό. (LEFT JOIN για να εμφανίζονται και οι ναοί με 0 επισκέψεις)
SELECT
    n.kodikos_naou,
    n.onoma AS naos,
    e.onoma_enorias,
    COUNT(ep.kodikos_pistou) AS posoi_pistoi_ton_exoun_episkefthei
FROM NAOS n
LEFT JOIN EPIKENTROSEIS ep ON n.kodikos_naou = ep.kodikos_naou
JOIN ENORIA e ON n.kodikos_enorias = e.kodikos_enorias
GROUP BY n.kodikos_naou, n.onoma, e.onoma_enorias
ORDER BY posoi_pistoi_ton_exoun_episkefthei DESC, n.onoma;

-- ZITOUMENO 5b. Ενορίες με συνολικό ποσό δωρεών >= 200€ (GROUP BY & HAVING)
SELECT
    e.kodikos_enorias,
    e.onoma_enorias,
    e.poli,
    COUNT(d.kodikos_doreas) AS plithos_doreon,
    SUM(d.poso) AS synoliko_poso_ewro
FROM ENORIA e
JOIN NAOS n ON e.kodikos_enorias = n.kodikos_enorias
JOIN DWREA d ON n.kodikos_naou = d.kodikos_naou
GROUP BY e.kodikos_enorias, e.onoma_enorias, e.poli
HAVING SUM(d.poso) >= 200
ORDER BY synoliko_poso_ewro DESC;

-- ZITOUMENO 6a. Συνολικό ποσό δωρεών ανά Ναό (INNER JOIN)
SELECT
    n.kodikos_naou,
    n.onoma AS naos,
    e.onoma_enorias,
    COUNT(d.kodikos_doreas) AS plithos_doreon,
    SUM(d.poso) AS synoliko_poso
FROM NAOS n
INNER JOIN DWREA d ON n.kodikos_naou = d.kodikos_naou
JOIN ENORIA e ON n.kodikos_enorias = e.kodikos_enorias
GROUP BY n.kodikos_naou, n.onoma, e.onoma_enorias
ORDER BY synoliko_poso DESC;

-- ZITOUMENO 6b. Πόσοι πιστοί επισκέφθηκαν τον κάθε ναό (LEFT JOIN)
SELECT
    n.kodikos_naou,
    n.onoma AS naos,
    e.onoma_enorias,
    COUNT(ep.kodikos_pistou) AS posoi_pistoi_episkeftikan
FROM NAOS n
LEFT JOIN EPIKENTROSEIS ep ON n.kodikos_naou = ep.kodikos_naou
JOIN ENORIA e ON n.kodikos_enorias = e.kodikos_enorias
GROUP BY n.kodikos_naou, n.onoma, e.onoma_enorias
ORDER BY posoi_pistoi_episkeftikan DESC, n.onoma;

-- ============================================
-- 5. ZITOUMENO 7: ΔΗΜΙΟΥΡΓΙΑ ΟΨΗΣ (VIEW)
-- ============================================

CREATE VIEW view_episkepsimotita_naon AS
SELECT
    e.kodikos_enorias,
    e.onoma_enorias AS enoria,
    e.poli,
    n.kodikos_naou,
    n.onoma AS naos,
    n.xoritikotita,
    n.etos_kataskevis,
    COUNT(ep.kodikos_pistou) AS posoi_pistoi_episkeftikan,
    ROUND(COUNT(ep.kodikos_pistou) * 100.0 / n.xoritikotita, 2) AS pososto_plhrotitas_xwritikotitas
FROM ENORIA e
JOIN NAOS n ON e.kodikos_enorias = n.kodikos_enorias
LEFT JOIN EPIKENTROSEIS ep ON n.kodikos_naou = ep.kodikos_naou
GROUP BY e.kodikos_enorias, e.onoma_enorias, e.poli,
         n.kodikos_naou, n.onoma, n.xoritikotita, n.etos_kataskevis
ORDER BY posoi_pistoi_episkeftikan DESC, n.onoma;

-- Ερώτημα για την εμφάνιση όλων των δεδομένων της Όψης
SELECT * FROM view_episkepsimotita_naon;

-- ============================================
-- 6. ZITOUMENO 8: ΔΗΜΙΟΥΡΓΙΑ ΔΙΑΔΙΚΑΣΙΑΣ (PROCEDURE)
-- ============================================

DELIMITER $$

CREATE PROCEDURE prosthiki_neas_doreas(
    IN p_imerominia      DATE,
    IN p_poso            DECIMAL(10,2),
    IN p_onoma_naou      VARCHAR(40)
)
BEGIN
    DECLARE v_kodikos_naou INT;

    -- Εύρεση του κωδικού ναού από το όνομα
    SELECT kodikos_naou INTO v_kodikos_naou
    FROM NAOS
    WHERE onoma = p_onoma_naou;

    IF v_kodikos_naou IS NULL THEN
        SELECT CONCAT('ΣΦΑΛΜΑ: Δεν βρέθηκε ναός με όνομα ', p_onoma_naou) AS minimata;
    ELSE
        INSERT INTO DWREA (imerominia, poso, kodikos_naou)
        VALUES (p_imerominia, p_poso, v_kodikos_naou);

        SELECT CONCAT('ΕΠΙΤΥΧΙΑ: Η δωρεά καταχωρήθηκε στον ναό ', p_onoma_naou) AS minimata;
    END IF;
END$$

DELIMITER ;

-- ΠΑΡΑΔΕΙΓΜΑ ΚΛΗΣΗΣ ΔΙΑΔΙΚΑΣΙΑΣ
-- (Αποσχολιάστε την παρακάτω γραμμή αν θέλετε να εκτελεστεί)
-- CALL prosthiki_neas_doreas('2025-04-15', 180.00, 'Agios Petros');

-- ============================================
-- 7. ZITOUMENO 9: ΔΗΜΙΟΥΡΓΙΑ ΕΝΑΥΣΜΑΤΩΝ (TRIGGERS)
-- ============================================

-- Trigger για INSERT στον NAOS: Αυξάνει τον arithmos_naon στην αντίστοιχη ENORIA
DELIMITER $$

CREATE TRIGGER trig_update_arithmos_naon_insert
AFTER INSERT ON NAOS
FOR EACH ROW
BEGIN
    UPDATE ENORIA
    SET arithmos_naon = arithmos_naon + 1
    WHERE kodikos_enorias = NEW.kodikos_enorias;
END$$

DELIMITER ;

-- Trigger για DELETE στον NAOS: Μειώνει τον arithmos_naon στην αντίστοιχη ENORIA
DELIMITER $$

CREATE TRIGGER trig_update_arithmos_naon_delete
AFTER DELETE ON NAOS
FOR EACH ROW
BEGIN
    UPDATE ENORIA
    SET arithmos_naon = arithmos_naon - 1
    WHERE kodikos_enorias = OLD.kodikos_enorias;
END$$

DELIMITER ;

-- ============================================
-- ΤΕΛΟΣ SQL SCRIPT
-- ============================================

