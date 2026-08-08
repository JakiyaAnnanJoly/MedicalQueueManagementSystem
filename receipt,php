<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

require_login_page();

$id = (int) ($_GET['appointment_id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid appointment id';
    exit;
}

$user = current_user();
$pdo = db();
$sql = 'SELECT a.id, a.service_name, a.location_name, a.doctor_name, a.appointment_date, a.appointment_time,
               a.ai_priority_score, a.status, a.notes, a.symptoms, a.created_at,
               p.full_name, p.email, p.phone
        FROM appointments a
        INNER JOIN patients p ON p.id = a.patient_id
        WHERE a.id = :id';
$params = [':id' => $id];

if (($user['role'] ?? '') === 'doctor') {
    $sql .= ' AND a.doctor_name = :doc';
    $params[':doc'] = (string) ($user['full_name'] ?? '');
} elseif (($user['role'] ?? '') === 'patient') {
    $allowedReceiptIds = $_SESSION['patient_receipt_ids'] ?? [];
    $allowedAt = is_array($allowedReceiptIds) ? (int) ($allowedReceiptIds[$id] ?? 0) : 0;
    if ($allowedAt <= 0 || $allowedAt < time() - 1800) {
        http_response_code(403);
        echo 'Receipt access requires patient self-service lookup first';
        exit;
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    echo 'Receipt not found';
    exit;
}

$authorizedSignerName = 'Patient Desk';
$authorizedSignerRole = 'patient';

// Prefer a patient self-service user from appointment audit history for signature display.
$patientAuditStmt = $pdo->prepare(
    'SELECT u.full_name, u.role
     FROM audit_logs a
     INNER JOIN users u ON u.id = a.actor_user_id
     WHERE a.entity_type = "appointment"
       AND a.entity_id = :id
       AND u.role = "patient"
     ORDER BY a.id DESC
     LIMIT 1'
);
$patientAuditStmt->execute([':id' => $id]);
$patientAudit = $patientAuditStmt->fetch();

if ($patientAudit && trim((string) ($patientAudit['full_name'] ?? '')) !== '') {
    $authorizedSignerName = trim((string) $patientAudit['full_name']);
    $authorizedSignerRole = 'patient';
} else {
    // Fallback to appointment creator (admin/patient) if patient self-service history is unavailable.
    $creatorStmt = $pdo->prepare(
        'SELECT u.full_name, u.role
         FROM audit_logs a
         INNER JOIN users u ON u.id = a.actor_user_id
         WHERE a.action = "appointment_created"
           AND a.entity_type = "appointment"
           AND a.entity_id = :id
         ORDER BY a.id ASC
         LIMIT 1'
    );
    $creatorStmt->execute([':id' => $id]);
    $creator = $creatorStmt->fetch();
    if ($creator && trim((string) ($creator['full_name'] ?? '')) !== '') {
        $authorizedSignerName = trim((string) $creator['full_name']);
        $authorizedSignerRole = trim((string) ($creator['role'] ?? 'staff')) ?: 'staff';
    }
}

$invoiceNo = 'MQ-' . str_pad((string) ((int) $row['id']), 6, '0', STR_PAD_LEFT);
$appointmentDateRaw = (string) $row['appointment_date'];
$appointmentDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $appointmentDateRaw);
$appointmentDateDisplay = $appointmentDateObj ? $appointmentDateObj->format('d M Y') : $appointmentDateRaw;

$appointmentTimeRaw = substr((string) $row['appointment_time'], 0, 5);
$appointmentTimeObj = DateTimeImmutable::createFromFormat('H:i', $appointmentTimeRaw);
$appointmentTimeDisplay = $appointmentTimeObj ? $appointmentTimeObj->format('h:i A') : $appointmentTimeRaw;

$createdAtRaw = (string) $row['created_at'];
$createdAtObj = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAtRaw);
$createdAtDisplay = $createdAtObj ? $createdAtObj->format('d M Y, h:i A') : $createdAtRaw;

$statusDisplay = ucwords(str_replace('_', ' ', (string) $row['status']));

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Invoice <?= e($invoiceNo) ?></title>
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
  <style>
    :root {
      --ink: #233337;
      --muted: #62767a;
      --line: #d7e3e3;
      --brand: #149b90;
      --bg: #f2f7f7;
      --surface: #ffffff;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 24px;
      font-family: Manrope, Inter, "Segoe UI", Arial, sans-serif;
      background: radial-gradient(circle at 12% 8%, #cef0ea, transparent 34%), var(--bg);
      color: var(--ink);
    }
    .invoice {
      max-width: 880px;
      margin: 0 auto;
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 16px 36px rgba(11, 44, 42, 0.12);
    }
    .pad { padding: 24px; }
    .head {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      border-bottom: 1px solid var(--line);
      background: linear-gradient(180deg, #f8fcfc 0%, #ffffff 100%);
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .brand-logo {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      flex: 0 0 auto;
      box-shadow: 0 4px 12px rgba(20, 106, 96, 0.22);
    }
    .brand-copy h1 {
      margin: 0;
      font-family: Inter, Manrope, sans-serif;
      font-size: 30px;
      line-height: 1.1;
      letter-spacing: -0.02em;
      color: #0f7b73;
    }
    .brand-copy p { margin: 6px 0 0; color: var(--muted); }
    .invoice-meta {
      min-width: 260px;
      display: grid;
      gap: 6px;
      align-content: start;
    }
    .meta-row {
      display: grid;
      grid-template-columns: 110px 1fr;
      gap: 8px;
      font-size: 14px;
    }
    .meta-row .k { color: var(--muted); font-weight: 600; }
    .meta-row .v { font-weight: 700; }
    .status-pill {
      display: inline-block;
      margin-top: 8px;
      padding: 6px 10px;
      border-radius: 999px;
      background: #e8f7f5;
      border: 1px solid #bde4df;
      color: #1d756d;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .bill-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-top: 16px;
    }
    .panel {
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 14px;
      background: #fbfdfd;
    }
    .panel h3 {
      margin: 0 0 10px;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #567074;
    }
    .panel p {
      margin: 6px 0;
      font-size: 14px;
      line-height: 1.45;
    }
    .table-wrap { margin-top: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td {
      padding: 11px 10px;
      border-bottom: 1px solid var(--line);
      text-align: left;
      font-size: 14px;
      vertical-align: top;
    }
    th {
      background: #f3f9f9;
      color: #587478;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .num { text-align: right; white-space: nowrap; }
    .summary {
      width: min(340px, 100%);
      margin-left: auto;
      margin-top: 14px;
      border: 1px solid var(--line);
      border-radius: 12px;
      overflow: hidden;
    }
    .sum-row {
      display: grid;
      grid-template-columns: 1fr auto;
      padding: 10px 12px;
      border-bottom: 1px solid var(--line);
      font-size: 14px;
    }
    .sum-row:last-child { border-bottom: 0; }
    .sum-row.total {
      background: #ecf9f7;
      font-weight: 800;
      font-size: 15px;
    }
    .notes {
      margin-top: 16px;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 12px;
      background: #fcfefe;
    }
    .notes h4 { margin: 0 0 8px; font-size: 14px; color: #567074; }
    .notes p { margin: 6px 0; font-size: 14px; }
    .foot {
      margin-top: 20px;
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: end;
      gap: 10px;
    }
    .sign {
      text-align: right;
      font-size: 13px;
      color: var(--muted);
    }
    .sign .name {
      margin-top: 6px;
      font-family: "Brush Script MT", "Segoe Script", "Lucida Handwriting", cursive;
      font-size: 34px;
      line-height: 1;
      color: #176e65;
    }
    .sign .role {
      margin-top: 2px;
      font-size: 11px;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #6a8388;
    }
    .sign .line {
      margin-top: 8px;
      border-top: 1px solid #9eb6b8;
      width: 200px;
      margin-left: auto;
      padding-top: 6px;
    }
    .actions {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      padding: 0 24px 24px;
    }
    .btn {
      background: linear-gradient(180deg, #1aa99c 0%, #138f84 100%);
      color: #fff;
      border: 0;
      border-radius: 10px;
      padding: 10px 14px;
      cursor: pointer;
      text-decoration: none;
      font-weight: 700;
    }
    .btn.secondary {
      background: #dfe9e9;
      color: #34535a;
    }
    @media (max-width: 780px) {
      body { padding: 12px; }
      .pad { padding: 16px; }
      .head,
      .bill-grid,
      .foot { grid-template-columns: 1fr; display: grid; }
      .invoice-meta { min-width: 0; }
      .actions { padding: 0 16px 16px; }
    }
    @media print {
      body { padding: 0; background: #fff; }
      .invoice { box-shadow: none; border: 0; border-radius: 0; }
      .bill-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 12px !important;
      }
      .bill-grid .panel {
        break-inside: avoid;
        page-break-inside: avoid;
      }
      .actions { display: none; }
    }
  </style>
</head>
<body>
  <section class="invoice">
    <div class="pad head">
      <div class="brand">
        <img class="brand-logo" src="assets/logo.svg" alt="MediQueue AI logo" />
        <div class="brand-copy">
          <h1>MediQueue AI</h1>
          <p>Appointment Invoice / Receipt</p>
          <span class="status-pill"><?= e($statusDisplay) ?></span>
        </div>
      </div>
      <div class="invoice-meta">
        <div class="meta-row"><span class="k">Invoice No</span><span class="v"><?= e($invoiceNo) ?></span></div>
        <div class="meta-row"><span class="k">Appointment ID</span><span class="v">#<?= (int) $row['id'] ?></span></div>
        <div class="meta-row"><span class="k">Issued At</span><span class="v"><?= e($createdAtDisplay) ?></span></div>
        <div class="meta-row"><span class="k">Visit Date</span><span class="v"><?= e($appointmentDateDisplay) ?>, <?= e($appointmentTimeDisplay) ?></span></div>
      </div>
    </div>

    <div class="pad">
      <div class="bill-grid">
        <article class="panel">
          <h3>Bill To (Patient)</h3>
          <p><strong><?= e((string) $row['full_name']) ?></strong></p>
          <p>Email: <?= e((string) $row['email']) ?></p>
          <p>Phone: <?= e((string) $row['phone']) ?></p>
        </article>
        <article class="panel">
          <h3>Clinic Details</h3>
          <p><strong>Location:</strong> <?= e((string) $row['location_name']) ?></p>
          <p><strong>Doctor:</strong> <?= e((string) $row['doctor_name']) ?></p>
          <p><strong>Priority Score:</strong> <?= (int) $row['ai_priority_score'] ?>/5</p>
        </article>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:50%">Description</th>
              <th>Service Date</th>
              <th class="num">Qty</th>
              <th class="num">Unit Price</th>
              <th class="num">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <strong><?= e((string) $row['service_name']) ?> Consultation Booking</strong><br />
                <span style="color:#62767a;font-size:13px;">Scheduled with <?= e((string) $row['doctor_name']) ?></span>
              </td>
              <td><?= e($appointmentDateDisplay) ?></td>
              <td class="num">1</td>
              <td class="num">0.00</td>
              <td class="num">0.00</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="summary">
        <div class="sum-row"><span>Subtotal</span><span>0.00</span></div>
        <div class="sum-row"><span>Tax</span><span>0.00</span></div>
        <div class="sum-row total"><span>Total</span><span>0.00</span></div>
      </div>

      <section class="notes">
        <h4>Clinical Notes</h4>
        <p><strong>Symptoms:</strong> <?= e(trim((string) ($row['symptoms'] ?? '')) !== '' ? (string) $row['symptoms'] : '-') ?></p>
        <p><strong>Additional Notes:</strong> <?= e(trim((string) ($row['notes'] ?? '')) !== '' ? (string) $row['notes'] : '-') ?></p>
      </section>

      <div class="foot">
        <p style="margin:0;color:#62767a;font-size:13px;">This invoice confirms appointment booking in MediQueue AI.</p>
        <div class="sign">
          <div class="name"><?= e($authorizedSignerName) ?></div>
          <div class="role"><?= e($authorizedSignerRole) ?></div>
          <div class="line">Authorized Signature</div>
        </div>
      </div>
    </div>

    <div class="actions">
      <button class="btn" type="button" onclick="window.print()">Print Invoice</button>
      <a class="btn secondary" href="index.php">Back</a>
    </div>
  </section>
</body>
</html>
