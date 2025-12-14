<?php
/**
 * =====================================================
 * SCCI Workshop Admin - Dashboard Page
 * صفحة لوحة التحكم الرئيسية للورش
 * =====================================================
 */

// =====================================================
// SECTION 1: Security Configuration
// إعدادات الأمان للجلسات
// =====================================================
ini_set('session.cookie_httponly', 1);  // منع الوصول للكوكيز من JavaScript
ini_set('session.cookie_secure', 1);    // إرسال الكوكيز فقط عبر HTTPS
ini_set('session.use_strict_mode', 1);  // منع استخدام session IDs غير معروفة

session_start();
require_once '../config.php';

// =====================================================
// SECTION 2: Constants Definition
// تعريف الثوابت المستخدمة في الصفحة
// =====================================================
const SESSION_TIMEOUT = 1800;  // 30 دقيقة - مدة انتهاء الجلسة
const ITEMS_PER_PAGE = 1;    // عدد العناصر في كل صفحة

// =====================================================
// SECTION 3: Authentication Check
// التحقق من تسجيل دخول المستخدم
// =====================================================
if (!isset($_SESSION['workshop_logged_in']) || !isset($_SESSION['workshop_code'])) {
    header('Location: login.php');
    exit;
}

// =====================================================
// SECTION 4: Session Timeout Check
// التحقق من انتهاء مدة الجلسة
// =====================================================
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// تحديث آخر نشاط للمستخدم
$_SESSION['last_activity'] = time();

// =====================================================
// SECTION 5: Helper Functions
// الدوال المساعدة
// =====================================================
function sanitize($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// =====================================================
// SECTION 6: Get Workshop Data
// جلب بيانات الورشة من الجلسة
// =====================================================
$workshop_code = $_SESSION['workshop_code'];
$workshop_name = $_SESSION['workshop_name'];

// =====================================================
// SECTION 7: Pagination Setup
// إعداد الصفحات (Pagination)
// =====================================================
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

try {
    // =====================================================
    // SECTION 8: Database Queries - Total Count
    // الاستعلام الأول: حساب إجمالي المشاركين
    // =====================================================
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM participants 
        WHERE first_preference = ? OR second_preference = ? OR third_preference = ?
    ");
    $countStmt->execute([$workshop_code, $workshop_code, $workshop_code]);
    $totalParticipants = $countStmt->fetch()['total'];
    
    // =====================================================
    // SECTION 9: Calculate Total Pages
    // حساب عدد الصفحات الكلي
    // =====================================================
    $totalPages = max(1, ceil($totalParticipants / ITEMS_PER_PAGE));
    
    // =====================================================
    // SECTION 10: Database Queries - Get Participants
    // الاستعلام الثاني: جلب المشاركين للصفحة الحالية
    // ⚠️ مشكلة: الترتيب حسب الأفضلية أولاً ثم التاريخ
    // هذا يعني أن أحدث المسجلين قد لا يظهرون في الأعلى
    // =====================================================
$stmt = $pdo->prepare("
    SELECT * FROM participants 
    WHERE first_preference = ? 
       OR second_preference = ? 
       OR third_preference = ?
    ORDER BY 
        CASE 
            WHEN first_preference = ? THEN 1 
            WHEN second_preference = ? THEN 2 
            WHEN third_preference = ? THEN 3 
        END ASC,
        registration_date DESC
    LIMIT ? OFFSET ?
");

// ربط القيم بالاستعلام
$stmt->bindValue(1, $workshop_code, PDO::PARAM_STR); // WHERE first_preference
$stmt->bindValue(2, $workshop_code, PDO::PARAM_STR); // WHERE second_preference
$stmt->bindValue(3, $workshop_code, PDO::PARAM_STR); // WHERE third_preference
$stmt->bindValue(4, $workshop_code, PDO::PARAM_STR); // CASE first_preference
$stmt->bindValue(5, $workshop_code, PDO::PARAM_STR); // CASE second_preference
$stmt->bindValue(6, $workshop_code, PDO::PARAM_STR); // CASE third_preference
$stmt->bindValue(7, ITEMS_PER_PAGE, PDO::PARAM_INT); // LIMIT
$stmt->bindValue(8, $offset, PDO::PARAM_INT);        // OFFSET

$stmt->execute();
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
// =====================================================
// SECTION 11: Database Queries - Statistics
// الاستعلام الثالث: حساب الإحصائيات
// =====================================================
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN first_preference = ? THEN 1 ELSE 0 END) as first_count,
        SUM(CASE WHEN second_preference = ? THEN 1 ELSE 0 END) as second_count,
        SUM(CASE WHEN third_preference = ? THEN 1 ELSE 0 END) as third_count,
        SUM(CASE WHEN DATE(registration_date) = CURDATE() AND first_preference = ? THEN 1 ELSE 0 END) as today_count,
        SUM(CASE WHEN tech_skills IS NOT NULL AND tech_skills != '' THEN 1 ELSE 0 END) as skills_count
    FROM participants 
    WHERE first_preference = ? OR second_preference = ? OR third_preference = ?
");
$statsStmt->execute([
    $workshop_code,  // 1: first_count
    $workshop_code,  // 2: second_count
    $workshop_code,  // 3: third_count
    $workshop_code,  // 4: today_count (first_preference)
    $workshop_code,  // 5: WHERE first_preference
    $workshop_code,  // 6: WHERE second_preference
    $workshop_code   // 7: WHERE third_preference
]);
$statsData = $statsStmt->fetch();
    
    // =====================================================
    // SECTION 12: Build Statistics Array
    // بناء مصفوفة الإحصائيات
    // =====================================================
    $stats = [
        'total' => $statsData['total'] ?? 0,
        'first' => $statsData['first_count'] ?? 0,
        'second' => $statsData['second_count'] ?? 0,
        'third' => $statsData['third_count'] ?? 0,
        'today' => $statsData['today_count'] ?? 0,
        'skills' => $statsData['skills_count'] ?? 0
    ];
    
} catch (PDOException $e) {
    // =====================================================
    // SECTION 13: Error Handling
    // معالجة أخطاء قاعدة البيانات
    // =====================================================
    error_log('Dashboard DB Error: ' . $e->getMessage());
    die('
        <div style="text-align: center; padding: 50px; font-family: Arial;">
            <h3>Database Error</h3>
            <p>Please try again later</p>
            <a href="logout.php">← Return to Login</a>
        </div>
    ');
}

// =====================================================
// SECTION 14: Calculate Session & Pagination Info
// حساب معلومات الجلسة والصفحات
// =====================================================
$remaining_time = SESSION_TIMEOUT - (time() - $_SESSION['last_activity']);
$startNum = $totalParticipants > 0 ? $offset + 1 : 0;
$endNum = min($offset + ITEMS_PER_PAGE, $totalParticipants);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <!-- ===================================================== -->
    <!-- SECTION 15: HTML Head - Meta Tags & CSS -->
    <!-- رأس الصفحة والروابط -->
    <!-- ===================================================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($workshop_name); ?> - Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/SCCI_Logo.png">
    <link rel="stylesheet" href="../admin/assets/css/root.css">
    <link rel="stylesheet" href="../admin/assets/css/dashboard.css">

</head>

<body>
    <div class="container">
        <!-- ===================================================== -->
        <!-- SECTION 16: Header Section -->
        <!-- قسم الرأس: العنوان، المؤقت، زر الخروج -->
        <!-- ===================================================== -->
        <div class="header">
            <h1><?php echo sanitize($workshop_name); ?></h1>
            <p>Participants interested in this workshop</p>
            <div style="margin-top: 10px;">
                <!-- عداد الجلسة -->
                <span class="session-timer">
                    Session: <span id="countdown"><?php echo gmdate('i:s', $remaining_time); ?></span>
                </span>
                <!-- زر تسجيل الخروج -->
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- SECTION 17: Statistics Cards -->
        <!-- بطاقات الإحصائيات: إجمالي، الخيارات، اليوم، المهارات -->
        <!-- ===================================================== -->
        <div class="stats">
            <!-- الخيار الأول -->
            <div class="stat-box">
                <h3><?php echo $stats['first']; ?></h3>
                <p>Total 1st Choice</p>
            </div>
            <!-- الخيار الثاني -->
            <div class="stat-box">
                <h3><?php echo $stats['second']; ?></h3>
                <p> 2nd Choice</p>
            </div>
            <!-- الخيار الثالث -->
            <div class="stat-box">
                <h3><?php echo $stats['third']; ?></h3>
                <p> 3rd Choice</p>
            </div>
            <!-- التسجيلات اليوم -->
            <div class="stat-box">
                <h3><?php echo $stats['today']; ?></h3>
                <p>Today</p>
            </div>
            <!-- عدد من لديهم مهارات -->
            <div class="stat-box">
                <h3><?php echo $stats['skills']; ?></h3>
                <p>With Skills</p>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- SECTION 18: Search & Filters Section -->
        <!-- قسم البحث والفلاتر -->
        <!-- ⚠️ مشكلة: البحث يعمل فقط على الصفحة الحالية -->
        <!-- لا يبحث في جميع المشاركين في قاعدة البيانات -->
        <!-- ===================================================== -->
        <div class="search-filters">
            <!-- حقل البحث -->
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search...">
                <!-- ⚠️ مفقود: زر X لمسح البحث -->
            </div>
            <!-- فلتر الأفضلية -->
            <select class="filter-select" id="preferenceFilter">
                <option value="">All Preferences</option>
                <option value="first">1st Only</option>
                <option value="second">2nd Only</option>
                <option value="third">3rd Only</option>
            </select>
            <!-- فلتر المهارات -->
            <select class="filter-select" id="skillsFilter">
                <option value="">All</option>
                <option value="with-skills">With Skills</option>
                <option value="no-skills">No Skills</option>
            </select>
            <!-- زر التصدير -->
            <button class="export-btn" onclick="exportAll()">Export All CSV</button>
        </div>

        <!-- ===================================================== -->
        <!-- SECTION 19: Pagination Info -->
        <!-- معلومات الصفحات: عرض X-Y من Z -->
        <!-- ===================================================== -->
        <?php if ($totalParticipants > 0): ?>
        <div class="pagination-info">
            Showing <?php echo $startNum; ?>-<?php echo $endNum; ?> of <?php echo $totalParticipants; ?> participants
        </div>
        <?php endif; ?>

        <!-- ===================================================== -->
        <!-- SECTION 20: Data Table -->
        <!-- جدول البيانات الرئيسي -->
        <!-- ===================================================== -->
        <div class="table-container">
            <?php if (count($participants) > 0): ?>
            <table id="participantsTable">
                <!-- رأس الجدول -->
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>University</th>
                        <th>Faculty</th>
                        <th>Level</th>
                        <th>Preference</th>
                        <th>Tech Skills</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <!-- محتوى الجدول -->
                <tbody>
                    <?php foreach ($participants as $p): 
                        // تحديد نوع الأفضلية ولون الشارة
                        if ($p['first_preference'] === $workshop_code) {
                            $pref = 'First'; $class = 'badge-1st'; $data = 'first';
                        } elseif ($p['second_preference'] === $workshop_code) {
                            $pref = 'Second'; $class = 'badge-2nd'; $data = 'second';
                        } else {
                            $pref = 'Third'; $class = 'badge-3rd'; $data = 'third';
                        }
                    ?>
                    <tr data-preference="<?php echo $data; ?>">
                        <td><?php echo sanitize($p['name']); ?></td>
                        <td><?php echo sanitize($p['email']); ?></td>
                        <td><?php echo sanitize($p['phone']); ?></td>
                        <!-- ⚠️ ملاحظة: يدعم خطأ إملائي قديم في اسم عمود university -->
                        <td><?php echo sanitize($p['university'] ?? $p['university'] ?? '-'); ?></td>
                        <td><?php echo sanitize($p['faculty'] ?? '-'); ?></td>
                        <td><?php echo sanitize($p['level'] ?? '-'); ?></td>
                        <td><span class="workshop-badge <?php echo $class; ?>"><?php echo $pref; ?></span></td>
                        <!-- المهارات التقنية - قابلة للتوسيع عند النقر -->
                        <td class="tech-skills" onclick="toggleSkills(this)">
                            <?php echo !empty(trim($p['tech_skills'] ?? '')) ? sanitize($p['tech_skills']) : '-'; ?>
                        </td>
                        <!-- الفتشرة لتحديث الحالة -->
                        <td>
                            <select class="status-select status-<?php echo $p['status'] ?? 'pending'; ?>"
                                data-id="<?php echo $p['id']; ?>" onchange="updateStatus(this)">
                                <option value="pending"
                                    <?php echo ($p['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>⏳ Pending
                                </option>
                                <option value="contacted"
                                    <?php echo ($p['status'] ?? 'pending') === 'contacted' ? 'selected' : ''; ?>>🟡
                                    Contacted</option>
                                <option value="scheduled"
                                    <?php echo ($p['status'] ?? 'pending') === 'scheduled' ? 'selected' : ''; ?>>🟢
                                    Scheduled</option>
                                <option value="rejected"
                                    <?php echo ($p['status'] ?? 'pending') === 'rejected' ? 'selected' : ''; ?>>🔴
                                    Canceld</option>
                            </select>
                        </td>
                        <td><?php echo date('d M Y, g:i A', strtotime($p['registration_date'])); ?></td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <!-- رسالة عدم وجود مشاركين -->
            <div style="text-align: center; padding: 40px; color: #666;">
                <h3>No participants yet</h3>
                <p>Participants will appear here once they register</p>
            </div>
            <?php endif; ?>
            <!-- رسالة عدم وجود نتائج بحث -->
            <div class="no-results" id="noResults" style="display: none;">No matching results</div>
        </div>

        <!-- ===================================================== -->
        <!-- SECTION 21: Pagination Controls -->
        <!-- أدوات التنقل بين الصفحات -->
        <!-- ===================================================== -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <!-- زر الصفحة الأولى -->
            <a href="?page=1" class="<?php echo $page == 1 ? 'disabled' : ''; ?>">First</a>
            <!-- زر السابق -->
            <a href="?page=<?php echo max(1, $page - 1); ?>"
                class="<?php echo $page == 1 ? 'disabled' : ''; ?>">Previous</a>

            <?php
            // حساب نطاق أرقام الصفحات المعروضة
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            
            // نقاط (...) قبل الأرقام
            if ($start > 1) echo '<span>...</span>';
            
            // عرض أرقام الصفحات
            for ($i = $start; $i <= $end; $i++):
            ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>

            <!-- نقاط (...) بعد الأرقام -->
            <?php if ($end < $totalPages) echo '<span>...</span>'; ?>

            <!-- زر التالي -->
            <a href="?page=<?php echo min($totalPages, $page + 1); ?>"
                class="<?php echo $page == $totalPages ? 'disabled' : ''; ?>">Next</a>
            <!-- زر الصفحة الأخيرة -->
            <a href="?page=<?php echo $totalPages; ?>"
                class="<?php echo $page == $totalPages ? 'disabled' : ''; ?>">Last</a>

            <!-- حقل الانتقال لصفحة معينة -->
            <div class="page-input">
                <span>Go to:</span>
                <input type="number" id="gotoPage" min="1" max="<?php echo $totalPages; ?>"
                    value="<?php echo $page; ?>">
                <button onclick="goToPage()">Go</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===================================================== -->
    <!-- SECTION 22: JavaScript Code -->
    <!-- الكود البرمجي للصفحة -->
    <!-- ===================================================== -->
    <script>
    // =====================================================
    // SUBSECTION 22.1: Session Timer
    // مؤقت الجلسة - العد التنازلي
    // =====================================================
    let remainingTime = <?php echo $remaining_time; ?>;
    const countdown = document.getElementById('countdown');

    const timer = setInterval(function() {
        remainingTime--;

        // إذا انتهت الجلسة
        if (remainingTime <= 0) {
            clearInterval(timer);
            alert('Session expired');
            window.location.href = 'logout.php';
            return;
        }

        // تحديث العرض
        const m = Math.floor(remainingTime / 60);
        const s = remainingTime % 60;
        countdown.textContent = m.toString().padStart(2, '0') + ':' + s.toString().padStart(2, '0');

        // تغيير اللون في آخر 5 دقائق
        if (remainingTime <= 300) countdown.style.color = '#dc2626';
    }, 1000);

    // =====================================================
    // SUBSECTION 22.2: Pagination Navigation
    // الانتقال لصفحة معينة
    // =====================================================
    function goToPage() {
        const pageNum = document.getElementById('gotoPage').value;
        const maxPage = <?php echo $totalPages; ?>;
        if (pageNum >= 1 && pageNum <= maxPage) {
            window.location.href = '?page=' + pageNum;
        }
    }

    // =====================================================
    // SUBSECTION 22.3: Client-Side Filtering
    // الفلترة على جانب المتصفح
    // ⚠️ مشكلة: يعمل فقط على الصفحة الحالية
    // =====================================================
    const allRows = Array.from(document.querySelectorAll('#participantsTable tbody tr'));

    function filterTable() {
        const search = document.getElementById('searchInput').value.toLowerCase().trim();
        const pref = document.getElementById('preferenceFilter').value;
        const skills = document.getElementById('skillsFilter').value;
        let count = 0;

        allRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const hasSkills = row.cells[7].textContent.trim() !== '-';
            const rowPref = row.getAttribute('data-preference');

            // تحديد ما إذا كان الصف يطابق المعايير
            const show = (!search || text.includes(search)) &&
                (!pref || rowPref === pref) &&
                (!skills || (skills === 'with-skills' && hasSkills) || (skills === 'no-skills' && !hasSkills));

            row.style.display = show ? '' : 'none';
            if (show) count++;
        });

        // إظهار/إخفاء رسالة "لا توجد نتائج"
        document.getElementById('noResults').style.display = count === 0 ? 'block' : 'none';
        document.getElementById('participantsTable').style.display = count === 0 ? 'none' : 'table';
    }

    // =====================================================
    // SUBSECTION 22.4: Event Listeners
    // ربط الأحداث بالعناصر
    // =====================================================
    if (document.getElementById('searchInput')) {
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('preferenceFilter').addEventListener('change', filterTable);
        document.getElementById('skillsFilter').addEventListener('change', filterTable);
    }

    // =====================================================
    // SUBSECTION 22.5: Toggle Skills Display
    // توسيع/طي عرض المهارات التقنية
    // =====================================================
    function toggleSkills(el) {
        if (el.textContent.trim() !== '-') el.classList.toggle('expanded');
    }

    // =====================================================
    // SUBSECTION 22.6: Export Function
    // تصدير جميع البيانات إلى CSV
    // =====================================================
    
    function exportAll() {
    if (confirm('Export ALL <?php echo $totalParticipants; ?> participants?')) {
        window.location.href = 'export.php?workshop=<?php echo urlencode($workshop_code); ?>';
    }
}

// =====================================================
// SUBSECTION 22.7: Update Status Function
// تحديث حالة المشارك
// =====================================================
function updateStatus(selectElement) {
    const participantId = selectElement.getAttribute('data-id');
    const newStatus = selectElement.value;
    const originalClass = selectElement.className;
    
    // Update visual appearance immediately
    selectElement.className = 'status-select status-' + newStatus;
    selectElement.disabled = true;
    
    // Send AJAX request
    fetch('update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + participantId + '&status=' + newStatus
    })

    
    .then(response => response.json())
    .then(data => {
        selectElement.disabled = false;
        if (data.success) {
            // Show success feedback (optional)
            selectElement.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.5)';
            setTimeout(() => {
                selectElement.style.boxShadow = '';
            }, 1000);
        } else {
            // Revert on error
            alert('Failed to update status: ' + (data.error || 'Unknown error'));
            selectElement.className = originalClass;
        }
    })
    .catch(error => {
        selectElement.disabled = false;
        alert('Error: ' + error);
        selectElement.className = originalClass;
    });
}
</script>
    
    
    
    </script>
</body>

</html>