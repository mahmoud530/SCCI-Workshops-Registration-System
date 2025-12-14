<?php
require 'config.php';

// =========================
// Total Participants
// =========================
$totalStmt = $pdo->query("SELECT COUNT(*) AS total FROM participants");
$totalParticipants = $totalStmt->fetch()['total'];

// =========================
// Total Participants Today
// =========================
$today = date("Y-m-d");
$todayStmt = $pdo->prepare("
    SELECT COUNT(*) AS total_today 
    FROM participants 
    WHERE DATE(registration_date) = ?
");
$todayStmt->execute([$today]);
$totalToday = $todayStmt->fetch()['total_today'];

// =========================
// Participants Per Workshop
// =========================
$workshopStatsStmt = $pdo->query("
    SELECT first_preference AS workshop, COUNT(*) AS count
    FROM participants 
    GROUP BY first_preference
");
$workshopStats = $workshopStatsStmt->fetchAll();

// =========================
// Most common university
// =========================
$topUniStmt = $pdo->query("
    SELECT university, COUNT(*) AS cnt 
    FROM participants 
    GROUP BY university 
    ORDER BY cnt DESC 
    LIMIT 1
");
$topUniversity = $topUniStmt->fetch();

// =========================
// Most common faculty
// =========================
$topFacultyStmt = $pdo->query("
    SELECT faculty, COUNT(*) AS cnt 
    FROM participants 
    GROUP BY faculty 
    ORDER BY cnt DESC 
    LIMIT 1
");
$topFaculty = $topFacultyStmt->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SCCI Dashboard</title>

    <style>
        /* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body */
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f3f6fb;
    color: #222;
    padding: 20px;
}

/* Headings */
h1 {
    color: #1a73e8;
    margin-bottom: 25px;
    text-align: center;
    font-weight: 700;
}

/* Cards Layout */
.stats-box {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
}

/* Small card */
.small-card {
    background: #ffffff;
    padding: 20px;
    border-radius: 14px;
    width: 280px;
    text-align: center;
    box-shadow: 0px 4px 15px rgba(0,0,0,0.08);
    transition: 0.2s ease;
}

.small-card:hover {
    transform: translateY(-4px);
    box-shadow: 0px 6px 25px rgba(0,0,0,0.12);
}

.small-card h2 {
    color: #333;
    margin-bottom: 10px;
}

.small-card p {
    font-size: 28px;
    font-weight: bold;
    color: #1a73e8;
}

/* Regular Card */
.card {
    background: #ffffff;
    padding: 20px;
    border-radius: 14px;
    margin: 25px 0;
    box-shadow: 0px 4px 15px rgba(0,0,0,0.06);
}

.card h3 {
    color: #1a73e8;
    margin-bottom: 12px;
}

/* Table Box */
.table-box {
    background: #ffffff;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0px 4px 15px rgba(0,0,0,0.06);
    margin-top: 25px;
}

.table-box h3 {
    color: #1a73e8;
    margin-bottom: 15px;
    text-align: center;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

th {
    background: #e8f1ff;
    font-weight: bold;
    color: #1a73e8;
}

tr:hover td {
    background: #f0f7ff;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-box {
        flex-direction: column;
        align-items: center;
    }

    .small-card {
        width: 90%;
    }

    table {
        font-size: 14px;
    }

    th, td {
        padding: 10px;
    }
}

    </style>
</head>
<body>

<h1>SCCI Website Statistics</h1>

<div class="stats-box">

    <div class="small-card">
        <h2>Total Participants</h2>
        <p style="font-size: 25px;"><?= $totalParticipants ?></p>
    </div>

    <div class="small-card">
        <h2>Participants Today</h2>
        <p style="font-size: 25px;"><?= $totalToday ?></p>
    </div>

    <div class="small-card">
        <h2>Workshops Count</h2>
        <p style="font-size: 25px;"><?= count($workshopStats) ?></p>
    </div>

</div>

<div class="card">
    <h3>Most Common University</h3>
    <p>
        <?= $topUniversity ? $topUniversity['university'] . " (" . $topUniversity['cnt'] . ")" : "No data yet." ?>
    </p>
</div>

<div class="card">
    <h3>Most Common Faculty</h3>
    <p>
        <?= $topFaculty ? $topFaculty['faculty'] . " (" . $topFaculty['cnt'] . ")" : "No data yet." ?>
    </p>
</div>

<div class="table-box">
    <h3>Participants per Workshop (First Preference)</h3>

    <table>
        <tr>
            <th>Workshop</th>
            <th>Total Registered</th>
        </tr>

        <?php foreach ($workshopStats as $ws): ?>
        <tr>
            <td><?= htmlspecialchars($ws['workshop']) ?></td>
            <td><?= $ws['count'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
