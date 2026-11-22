<?php
// Turn OFF display errors for production, but keep logging them
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 2. TIMEZONE
date_default_timezone_set('Africa/Kampala');

// 3. SESSION SETTINGS (MUST BE BEFORE session_start)
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');

// 4. START SESSION (MUST BE AFTER ini_set)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// 4. Database Connection
function db() {
    static $pdo = null;
    if ($pdo === null) {
        // Check Railway's MYSQL* names first, then DB_* names, then XAMPP defaults
        $host = getenv('MYSQLHOST')     ?: getenv('DB_HOST')     ?: '127.0.0.1';
        $port = getenv('MYSQLPORT')     ?: getenv('DB_PORT')     ?: '3306';
        $db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME')     ?: 'mamatrack';
        $user = getenv('MYSQLUSER')     ?: getenv('DB_USER')     ?: 'root';
        $pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

// 2. Session & Configuration
if (session_status() === PHP_SESSION_NONE) session_start();

function cfg($key = null) {
    $config = [
        'notify' => ['mode' => 'test'], // 'test' logs to DB, 'live' sends real SMS
        'clinic_name' => 'MamaTrack ANC Clinic'
    ];
    return $key ? ($config[$key] ?? null) : $config;
}

function now() { return date('Y-m-d H:i:s'); }
function today() { return date('Y-m-d'); }
function h($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function post($key) { return $_POST[$key] ?? null; }
function redirect($url) { header("Location: $url"); exit(); }
function fdate($d) { return $d ? date('d M Y', strtotime($d)) : '—'; }
function valid_date($d) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false; }

function normalize_phone($p) {
    $p = preg_replace('/[^0-9+]/', '', $p);
    if (strpos($p, '+256') === 0) return $p;
    if (strpos($p, '0') === 0) return '+256' . substr($p, 1);
    return $p;
}

function age_years($dob) {
    if (!$dob) return 0;
    return (int)date_diff(date_create($dob), date_create('today'))->y;
}

function gest_weeks($lmp, $date) {
    if (!$lmp || !valid_date($lmp)) return 0;
    $days = (strtotime($date) - strtotime($lmp)) / 86400;
    return max(0, (int)floor($days / 7));
}

function suggest_next_date($gw, $today) {
    $days = $gw < 28 ? 28 : ($gw < 36 ? 14 : 7);
    return date('Y-m-d', strtotime("$today + $days days"));
}

// 3. Security (Auth & CSRF)
function current_user() {
    if (!isset($_SESSION['uid'])) return null;
    static $u = null;
    if ($u === null) {
        $s = db()->prepare('SELECT * FROM users WHERE id=? AND active=1');
        $s->execute([$_SESSION['uid']]);
        $u = $s->fetch();
    }
    return $u;
}

function require_login($force_change = false) {
    $u = current_user();
    if (!$u) redirect('login.php');
    
    // Check if we are already on the profile page to prevent an infinite loop
    $on_profile_page = (basename($_SERVER['PHP_SELF']) === 'profile.php');
    
    if ($force_change && $u['must_change'] && !$on_profile_page) {
        redirect('profile.php');
    }
    return $u;
}

function require_role($role) {
    $u = require_login();
    if ($u['role'] !== $role) { http_response_code(403); exit('Access denied'); }
    return $u;
}

function csrf_field() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

function csrf_check() {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
        http_response_code(400); exit('Invalid security token.');
    }
}

// 4. Audit & Flash Messages
function audit($action, $detail = '') {
    $u = current_user();
    db()->prepare('INSERT INTO audit_log(user_id,user_name,action,detail,ip,created_at) VALUES(?,?,?,?,?,?)')
        ->execute([$u['id'] ?? null, $u['name'] ?? 'System', $action, $detail, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', now()]);
}

function flash($msg, $type = 'ok') { $_SESSION['flash'] = ['msg' => $msg, 'type' => $type]; }
function get_flash() { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

// 5. Data Fetchers
function get_patient($id) {
    if (!$id) return null;
    $s = db()->prepare('SELECT p.*, u.name AS doctor_name FROM patients p LEFT JOIN users u ON u.id=p.doctor_id WHERE p.id=?');
    $s->execute([$id]);
    return $s->fetch();
}

function patient_visits($id) {
    $s = db()->prepare('SELECT * FROM visits WHERE patient_id=? ORDER BY visit_date ASC, id ASC');
    $s->execute([$id]);
    return $s->fetchAll();
}

function missed_count($id) {
    return (int)db()->prepare('SELECT COUNT(*) FROM appointments WHERE patient_id=? AND status="missed"')->execute([$id]) ? 
           db()->query("SELECT COUNT(*) FROM appointments WHERE patient_id=$id AND status='missed'")->fetchColumn() : 0;
}

function doctors() {
    return db()->query("SELECT id, name FROM users WHERE role='doctor' AND active=1 ORDER BY name")->fetchAll();
}

// 6. Clinical Logic & Risk Assessment
const URINE_SCALE = ['neg' => 0, 'trace' => 1, '1+' => 2, '2+' => 3, '3+' => 4];

function is_htn($v) { return ($v['systolic'] ?? 0) >= 140 || ($v['diastolic'] ?? 0) >= 90; }

function refresh_risk($pid) {
    $p = get_patient($pid);
    $visits = patient_visits($pid);
    $missed = missed_count($pid);
    $r = assess_risk($p, $visits, $missed);
    db()->prepare('UPDATE patients SET risk_level=?, risk_score=? WHERE id=?')->execute([$r['level'], $r['score'], $pid]);
    return $r;
}

function assess_risk($p, $visits, $missed) {
    $score = 0; $factors = [];
    $last = end($visits) ?: [];
    
    if (is_htn($last)) { $score += 3; $factors[] = ['pts'=>3, 'label'=>'Hypertension', 'detail'=>"BP {$last['systolic']}/{$last['diastolic']}", 'critical'=>true]; }
    if (($last['hemoglobin'] ?? 99) < 11) { $score += 2; $factors[] = ['pts'=>2, 'label'=>'Anaemia', 'detail'=>"Hb {$last['hemoglobin']}", 'critical'=>false]; }
    if (URINE_SCALE[$last['urine_protein'] ?? 'neg'] >= 2) { $score += 2; $factors[] = ['pts'=>2, 'label'=>'Proteinuria', 'detail'=>$last['urine_protein'], 'critical'=>true]; }
    if ($missed >= 2) { $score += 2; $factors[] = ['pts'=>2, 'label'=>'Frequent missed visits', 'detail'=>"Missed $missed appointments", 'critical'=>false]; }
    if ($p['hiv_status'] === 'positive') { $score += 1; $factors[] = ['pts'=>1, 'label'=>'HIV Positive', 'detail'=>'Requires enhanced monitoring', 'critical'=>false]; }
    if (age_years($p['dob']) < 18 || age_years($p['dob']) > 35) { $score += 1; $factors[] = ['pts'=>1, 'label'=>'High-risk age', 'detail'=>age_years($p['dob']).' years', 'critical'=>false]; }

    $level = $score >= 5 ? 'high' : ($score >= 2 ? 'moderate' : 'low');
    $action = $level === 'high' ? 'Urgent clinical review required.' : ($level === 'moderate' ? 'Close monitoring advised.' : 'Routine ANC care.');
    
    return ['score' => $score, 'level' => $level, 'action' => $action, 'factors' => $factors];
}

function num_field($name, $label, $min, $max, &$errors, $required = false) {
    $v = post($name);

    // Empty value handling
    if ($v === '' || $v === null) {
        if ($required) {
            $errors[] = "$label is required.";
        }
        return null;
    }

    // Range / type validation
    if (!is_numeric($v) || $v < $min || $v > $max) {
        $errors[] = "$label must be between $min and $max.";
        return null;
    }

    return (float)$v;
}
// 7. Appointments & Notifications
function new_appointment($pid, $doc_id, $date, $time, $reason, $created_by, $rescheduled_from = null) {
    $token = bin2hex(random_bytes(16));
    db()->prepare('INSERT INTO appointments(patient_id,doctor_id,appt_date,appt_time,reason,status,token,rescheduled_from,created_by,created_at) VALUES(?,?,?,?,?,?,?,?,?,?)')
        ->execute([$pid, $doc_id, $date, $time, $reason, 'scheduled', $token, $rescheduled_from, $created_by, now()]);
    return (int)db()->lastInsertId();
}

function notify_person($appt_id, $recipient, $phone, $email, $subject, $message) {
    $channel = 'sms';
    $status = cfg('notify')['mode'] === 'live' ? 'sent' : 'queued_test';
    db()->prepare('INSERT INTO notifications(appointment_id,recipient,channel,to_addr,message,status,created_at) VALUES(?,?,?,?,?,?,?)')
        ->execute([$appt_id, $recipient, $channel, $phone, $message, $status, now()]);
}

function process_missed_appointments() {
    $count = 0;
    $rows = db()->query("SELECT * FROM appointments WHERE status='scheduled' AND appt_date < '" . today() . "'")->fetchAll();
    
    foreach ($rows as $a) {
        // 1. Update status to missed
        db()->prepare("UPDATE appointments SET status='missed', missed_at=? WHERE id=?")->execute([now(), $a['id']]);
        
        $p = get_patient($a['patient_id']);
        
        // 2. Notify the PATIENT
        notify_person($a['id'], 'patient:'.$p['name'], $p['phone'], null, 'Missed Visit', 
            "Dear {$p['name']}, you missed your ANC visit on ".fdate($a['appt_date']).". Please contact us to reschedule.");
            
        // 3. Notify the DOCTOR (New Feature!)
        $docStmt = db()->prepare('SELECT name, phone FROM users WHERE id=?'); 
        $docStmt->execute([$a['doctor_id']]);
        $doc = $docStmt->fetch();
        
        if ($doc && $doc['phone']) {
            notify_person($a['id'], 'doctor:'.$doc['name'], $doc['phone'], null, 'Patient Missed Visit', 
                "ALERT Dr. {$doc['name']}: Patient {$p['name']} ({$p['mrn']}) missed her scheduled ANC visit on ".fdate($a['appt_date']).".");
        }
        
        $count++;
    }
    return $count;
}

function send_reminders() {
    $count = 0; $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $rows = db()->query("SELECT * FROM appointments WHERE status='scheduled' AND appt_date='$tomorrow' AND reminder_sent=0")->fetchAll();
    foreach ($rows as $a) {
        $p = get_patient($a['patient_id']);
        notify_person($a['id'], 'patient:'.$p['name'], $p['phone'], null, 'Reminder', "Reminder: ANC visit tomorrow at {$a['appt_time']}.");
        db()->prepare("UPDATE appointments SET reminder_sent=1 WHERE id=?")->execute([$a['id']]);
        $count++;
    }
    return $count;
}

function maybe_run_checks() {
    // Temporarily forced to run on every page load for testing
    process_missed_appointments();
    send_reminders();
}

// 8. UI Helpers
function risk_badge($l) { $c = $l==='high'?'bad':($l==='moderate'?'warn':'ok'); return "<span class='badge r-$c'>$l</span>"; }
function status_badge($s) { $c = $s==='missed'?'bad':($s==='cancelled'?'cancel':($s==='attended'?'ok':'')); return "<span class='badge s-$c'>$s</span>"; }

function page_header($title, $active = '') {
    $u = current_user();
    $f = get_flash();
    ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MamaTrack - <?= h($title) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
       body { 
        background: #f5f5f5; /* Fallback color */
        /* Replace 'assets/background.png' with the actual name of your image file */
        background-image: url('/background.jpeg'); 
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        background-attachment: fixed;
        color: #333; 
        display: flex; 
        min-height: 100vh; 
    }
    /* Sidebar */
    .sidebar { 
        width: 220px; 
        background: #0d4f4f; 
        color: white; 
        padding: 20px; 
        position: fixed; 
        left: 0;
        top: 0;
        height: 100vh; 
        transition: transform 0.3s ease; 
        z-index: 1001; 
        overflow-y: auto;
    }
    .sidebar h2 { 
        margin-bottom: 30px; 
        font-size: 20px; 
        text-align: center;
        color: white;
    }
    .sidebar a { 
        display: block; 
        color: white; 
        text-decoration: none; 
        padding: 12px; 
        margin: 5px 0; 
        border-radius: 4px; 
        transition: background 0.2s;
    }
    .sidebar a:hover, .sidebar a.active { 
        background: rgba(255,255,255,0.2); 
    }
    
    /* Main Content */
    .content { 
        margin-left: 220px; 
        padding: 30px; 
        flex: 1; 
        transition: margin-left 0.3s ease; 
        width: calc(100% - 220px);
    }
    
    /* Cards & Layout */
    .card { 
        background: white; 
        padding: 25px; 
        border-radius: 8px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.08); 
        margin-bottom: 20px; 
        border: 1px solid #e0e0e0;
    }
    .card.narrow { max-width: 500px; }
    .card.form { max-width: 800px; }
    .cols { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    
    .stats { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px; 
        margin-bottom: 20px; 
    }
    .stat { 
        background: white; 
        padding: 20px; 
        border-radius: 8px; 
        text-align: center; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        border: 1px solid #e0e0e0;
    }
    .stat .n { 
        font-size: 32px; 
        font-weight: bold; 
        color: #333; 
        margin-bottom: 8px; 
    }
    .stat.bad .n { color: #dc3545; }
    .stat.warn .n { color: #ff9800; }
    .stat .stat-label {
        color: #555;
        font-size: 14px;
        font-weight: 500;
    }
    
    /* Tables */
    .tbl { 
        overflow-x: auto; 
        -webkit-overflow-scrolling: touch; 
        background: white; 
        border-radius: 8px; 
    }
    table { 
        width: 100%; 
        border-collapse: collapse; 
        min-width: 600px; 
    } 
    th, td { 
        padding: 12px 15px; 
        text-align: left; 
        border-bottom: 1px solid #eee; 
        color: #333;
    }
    th { 
        background: #f8f9fa; 
        font-weight: 600; 
        color: #555; 
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }
    td { color: #333; }
    tr:last-child td { border-bottom: none; }
    
    /* Forms & Buttons */
    label { 
        display: block; 
        margin: 15px 0 5px; 
        font-weight: 500; 
        font-size: 14px; 
        color: #333;
    }
    input, select, textarea { 
        width: 100%; 
        padding: 10px; 
        border: 1px solid #ddd; 
        border-radius: 4px; 
        font-size: 14px; 
        color: #ceceff;
        transition: border 0.2s; 
    }
    input:focus, select:focus, textarea:focus { 
        border-color: #0d4f4f; 
        outline: none; 
    }
    .btn { 
        display: inline-block; 
        padding: 10px 20px; 
        background: #14b8a6; 
        color: white; 
        text-decoration: none; 
        border-radius: 4px; 
        border: none; 
        cursor: pointer; 
        font-size: 14px; 
        font-weight: 500; 
        transition: background 0.2s; 
    }
    .btn:hover { background: #0d9488; }
    .btn.ghost { 
        background: transparent; 
        color: #14b8a6; 
        border: 1px solid #14b8a6; 
    }
    .btn.ghost:hover { background: #f0fdfa; }
    .btn.sm { padding: 6px 12px; font-size: 12px; }
    .bar { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 20px; 
        flex-wrap: wrap; 
        gap: 10px; 
    }
    .inline { display: inline-flex; gap: 10px; align-items: center; }
    .tabs { 
        margin-bottom: 20px; 
        border-bottom: 2px solid #eee; 
    } 
    .tabs a { 
        padding: 10px 20px; 
        text-decoration: none; 
        color: #666; 
        display: inline-block; 
        border-bottom: 2px solid transparent; 
        margin-bottom: -2px; 
    } 
    .tabs a.on { 
        border-color: #14b8a6; 
        color: #14b8a6; 
        font-weight: bold; 
    }
    
    /* Badges & Flash Messages */
    .badge { 
        padding: 4px 10px; 
        border-radius: 12px; 
        font-size: 11px; 
        font-weight: bold; 
        text-transform: uppercase; 
        display: inline-block; 
    }
    .r-high, .s-bad { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; } 
    .r-moderate, .s-warn { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; } 
    .r-low, .s-ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .flash { 
        padding: 12px 15px; 
        border-radius: 4px; 
        margin-bottom: 20px; 
        border: 1px solid transparent;
    }
    .flash.err { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
    .flash.warn { background: #fff3cd; color: #856404; border-color: #ffeeba; }
    .flash.ok { background: #d4edda; color: #155724; border-color: #c3e6cb; }
    .muted { color: #666; font-size: 13px; } 
    .small { font-size: 12px; color: #555; }
    .nowrap { white-space: nowrap; }
    
    /* Risk Card Specifics */
    .card.risk-high { border-left: 5px solid #dc3545; }
    .card.risk-moderate { border-left: 5px solid #ffc107; }
    .card.risk-low { border-left: 5px solid #28a745; }
    .factors { list-style: none; padding: 0; }
    .factors li { padding: 8px 0; border-bottom: 1px solid #eee; color: #333; }
    .factors li.crit { color: #dc3545; font-weight: bold; }
    
    /* Headings */
    h2 { 
        color: #333; 
        font-size: 20px; 
        font-weight: 600; 
        margin-bottom: 15px; 
    }
    .card h2 { margin-top: 0; }
    
    /* Links - underline only on hover */
    a { 
        color: #14b8a6; 
        text-decoration: none; 
        transition: color 0.2s;
    }
    a:hover { 
        text-decoration: underline; 
        color: #0d9488;
    }
    .sidebar a { text-decoration: none; }
    .sidebar a:hover { text-decoration: none; }
    .btn { text-decoration: none; }
    .btn:hover { text-decoration: none; }
    
    /* Hamburger & Overlay (Hidden on Desktop) */
    .menu-toggle { 
        display: none; 
        position: fixed; 
        top: 15px; 
        left: 15px; 
        z-index: 1002; 
        background: #0d4f4f; 
        color: white; 
        border: none; 
        padding: 10px 15px; 
        border-radius: 4px; 
        font-size: 20px; 
        cursor: pointer; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.2); 
    }
    .overlay { 
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0,0,0,0.5); 
        z-index: 1000; 
    }
    .overlay.active { display: block; }
    
    /* Grid for dashboard */
    .grid2 { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 20px; 
    }
    
    /* Charts container */
    .charts { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
        gap: 20px; 
    }
    .chart { 
        background: #f8f9fa; 
        padding: 15px; 
        border-radius: 8px; 
        min-height: 200px; 
    }
    
    /* MOBILE RESPONSIVE DESIGN (Max 768px)      */
    @media (max-width: 768px) {
        .menu-toggle { display: block; }
        .sidebar { transform: translateX(-100%); }
        .sidebar.active { transform: translateX(0); }
        .content { margin-left: 0; padding: 20px; padding-top: 70px; width: 100%; }
        .cols { grid-template-columns: 1fr; }
        .stats { grid-template-columns: 1fr; }
        .grid2 { grid-template-columns: 1fr; }
        .bar { flex-direction: column; align-items: stretch; }
        .inline { flex-direction: column; align-items: stretch; }
        .btn { width: 100%; text-align: center; margin-bottom: 5px; }
        .tabs a { width: 100%; text-align: center; border-bottom: none; border-right: 2px solid transparent; }
        .tabs a.on { border-right-color: #14b8a6; border-bottom: none; }
    }
</style>
    </head><body>
    <?php if ($u): ?>
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>
       <div class="sidebar" id="sidebar">
        <h2> MamaTrack</h2>
        <!-- User Name Display -->
        <div style="background: rgba(0,0,0,0.2); padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center;">
            <small style="color: rgba(255,255,255,0.7);">Logged in as</small><br>
            <b style="color: white;"><?= h($u['name']) ?></b>
        </div>
 
        <a href="dashboard.php" class="<?= $active==='dash'?'active':'' ?>">Dashboard</a> <a href="patients.php" class="<?= $active==='patients'?'active':'' ?>">Mothers</a>
        <a href="appointments.php" class="<?= $active==='appts'?'active':'' ?>">Appointments</a>
        <a href="notifications.php" class="<?= $active==='notif'?'active':'' ?>">Alerts</a>
        <?php if ($u['role'] === 'admin'): ?><a href="users.php" class="<?= $active==='users'?'active':'' ?>">Staff</a>
        <a href="audit.php" class="<?= $active==='audit'?'active':'' ?>">Audit Log</a><?php endif; ?>
        <a href="profile.php">Profile</a>
        <a href="logout.php" style="margin-top: 30px; background: rgba(35, 68, 45, 0.85);">Logout</a>
    </div>
    <div class="content">
    <?php endif; ?>
    <?php if ($f): ?><div class="flash <?= $f['type'] ?>"><?= h($f['msg']) ?></div><?php endif; ?>
<?php 
} 

function page_footer() {
    if (current_user()) {
        echo '</div>'; // Closes .content
        echo '<script>
            function toggleMenu() {
                document.getElementById("sidebar").classList.toggle("active");
                document.getElementById("overlay").classList.toggle("active");
            }
            document.querySelectorAll(".sidebar a").forEach(link => {
                link.addEventListener("click", () => {
                    if (window.innerWidth <= 768) toggleMenu();
                });
            });
        </script>';
    }
    
    // Only load Chart.js on the patient profile page where charts exist
    if (basename($_SERVER['PHP_SELF']) === 'patient.php') {
        echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".chart").forEach(function(div) {
                // 1. Fix the HTML encoding issue using a textarea trick
                var txt = div.getAttribute("data-chart");
                var txtarea = document.createElement("textarea");
                txtarea.innerHTML = txt;
                var data = JSON.parse(txtarea.value);

                // 2. Create a proper canvas element for Chart.js
                var canvas = document.createElement("canvas");
                div.innerHTML = ""; // Clear the empty div
                div.appendChild(canvas);
                canvas.style.width = "100%";
                canvas.style.height = "250px";

                // 3. Draw the chart
                new Chart(canvas, {
                    type: "line",
                    data: {
                        labels: data.labels,
                        datasets: data.series.map(function(s) {
                            return {
                                label: s.name,
                                data: s.data,
                                borderColor: s.color,
                                backgroundColor: s.color + "20",
                                tension: 0.3,
                                fill: false,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            };
                        })
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: "top" },
                            title: { display: true, text: data.title, font: { size: 14, weight: "bold" } }
                        },
                        scales: {
                            y: { beginAtZero: false, grid: { color: "#eee" } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
        });
        </script>';
    }
    
    echo '</body></html>';
}
