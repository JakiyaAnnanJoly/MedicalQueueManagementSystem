<?php

declare(strict_types=1);

require __DIR__ . '/auth.php';
if (!is_logged_in()) {
    header('Location: landing.php');
    exit;
}

$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$currentMonth = (new DateTimeImmutable('today'))->format('Y-m');
$config = require __DIR__ . '/config.php';
$defaultMode = htmlspecialchars((string) ($config['ai_mode'] ?? 'mock'), ENT_QUOTES);
$user = current_user();
$roleRaw = (string) ($user['role'] ?? '');
$role = htmlspecialchars($roleRaw, ENT_QUOTES);
$fullNameRaw = (string) ($user['full_name'] ?? '');
$fullName = htmlspecialchars($fullNameRaw, ENT_QUOTES);
$profileImageRaw = trim((string) ($user['profile_image_path'] ?? ''));
$profileImageClean = ltrim($profileImageRaw, '/');
$profileImage = htmlspecialchars($profileImageClean !== '' ? $profileImageClean : 'uploads/profiles/default-avatar.svg', ENT_QUOTES);

$isAdmin = $roleRaw === 'admin';
$isPatient = $roleRaw === 'patient';
$isDoctor = $roleRaw === 'doctor';
$canBook = $isAdmin || $isPatient;
$canAdminDesk = $isAdmin || $isDoctor;
$canSelfService = $isPatient;
$canOps = $canAdminDesk || $canSelfService;
$canInsights = $isAdmin || $isDoctor;
$opsTabLabelRaw = $isAdmin ? 'Admin Desk' : ($isDoctor ? 'My Appointments' : 'Self Service');
$opsTabLabel = htmlspecialchars($opsTabLabelRaw, ENT_QUOTES);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MediQueue AI</title>
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
  <main class="shell">
    <aside class="sidebar">
      <div class="brand-wrap">
        <img class="brand-logo" src="assets/logo.svg" alt="MediQueue AI logo" />
        <h1 class="brand">MediQueue AI</h1>
      </div>
      <p class="brand-subtitle">Medical Admin Dashboard</p>

      <div class="profile-box side-profile">
        <img src="<?= $profileImage ?>" alt="Profile" class="profile-avatar" />
        <div>
          <strong id="sidebarUserName"><?= $fullName ?></strong>
          <div class="inline-status">Role: <?= $role ?></div>
        </div>
      </div>

      <div class="meta-block">
        <p class="meta-title">Main Menu</p>
        <div class="side-nav">
          <button class="tab-btn nav-jump active" data-tab="dashboardTab" type="button"><span class="nav-ico nav-ico-dashboard" aria-hidden="true"></span><span class="nav-label">Dashboard</span></button>
          <?php if ($canBook): ?><button class="tab-btn nav-jump" data-tab="bookingTab" type="button"><span class="nav-ico nav-ico-booking" aria-hidden="true"></span><span class="nav-label">Booking</span></button><?php endif; ?>
          <button class="tab-btn nav-jump" data-tab="queueTab" type="button"><span class="nav-ico nav-ico-queue" aria-hidden="true"></span><span class="nav-label">Queue</span></button>
          <?php if ($canOps): ?><button class="tab-btn nav-jump" data-tab="opsTab" type="button"><span class="nav-ico nav-ico-ops" aria-hidden="true"></span><span class="nav-label"><?= $opsTabLabel ?></span></button><?php endif; ?>
        </div>
      </div>

      <div class="meta-block">
        <p class="meta-title">Workflows</p>
        <div class="side-nav">
          <?php if ($canInsights): ?><button class="tab-btn nav-jump" data-tab="insightsTab" type="button"><span class="nav-ico nav-ico-insights" aria-hidden="true"></span><span class="nav-label">Insights</span></button><?php endif; ?>
          <button class="tab-btn nav-jump" data-tab="accountTab" type="button"><span class="nav-ico nav-ico-account" aria-hidden="true"></span><span class="nav-label">Account</span></button>
        </div>
      </div>

      <?php if ($canBook): ?>
      <div class="meta-block">
        <p class="meta-title">Assistant Mode</p>
        <select id="assistantMode">
          <option value="mock" <?= $defaultMode === 'mock' ? 'selected' : '' ?>>Mock dataset (local test)</option>
          <option value="gemini" <?= $defaultMode === 'gemini' ? 'selected' : '' ?>>Gemini</option>
        </select>
      </div>
      <?php endif; ?>
    </aside>

    <section class="content">
      <header class="topbar">
        <div class="topbar-user">
          <div>
            <strong id="topbarUserName"><?= $fullName ?></strong>
            <span class="inline-status">Role: <?= $role ?></span>
          </div>
          <a class="logout-link" href="logout.php">Logout</a>
        </div>
      </header>

      <section id="dashboardTab" class="tab-panel active">
        <div class="card">
          <div class="section-title"><span class="section-ico section-ico-dashboard" aria-hidden="true"></span><h2>Role Dashboard</h2></div>
          <p id="roleIntro" class="muted"></p>
          <div id="roleSummaryCards" class="metric-grid"></div>
        </div>
        <div class="card">
          <div class="section-title"><span class="section-ico section-ico-list" aria-hidden="true"></span><h3 id="roleTableTitle">Today List</h3></div>
          <div class="table-wrap admin-table-wrap">
            <table>
              <thead id="roleTableHead"></thead>
              <tbody id="roleTableBody"></tbody>
            </table>
          </div>
        </div>
      </section>

      <?php if ($canBook): ?>
      <section id="bookingTab" class="tab-panel">
        <div class="card">
          <div class="card-header">
            <div class="section-title"><span class="section-ico section-ico-calendar" aria-hidden="true"></span><h2>Select Date and Time</h2></div>
            <input type="date" id="appointmentDate" value="<?= htmlspecialchars($today, ENT_QUOTES) ?>" min="<?= htmlspecialchars($today, ENT_QUOTES) ?>" />
          </div>

          <div class="field-grid cols-3">
            <label>
              Service
              <select id="service"></select>
            </label>
            <label>
              Location
              <select id="location"></select>
            </label>
            <label>
              Doctor
              <select id="doctor"></select>
            </label>
          </div>

          <div id="slots" class="slots"></div>
          <p id="slotHint" class="muted">Select a date to load available slots.</p>
        </div>

        <div class="card">
          <div class="section-title"><span class="section-ico section-ico-usercard" aria-hidden="true"></span><h3>Personal Details and Visit Notes</h3></div>
          <div class="field-grid cols-2">
            <label>Full name<input id="fullName" type="text" placeholder="John Doe" /></label>
            <label>Email<input id="email" type="email" placeholder="john@email.com" /></label>
            <label>Phone<input id="phone" type="text" placeholder="+8801XXXXXXXXX" /></label>
            <label>Symptoms<textarea id="symptoms" rows="2" placeholder="Fever, headache..."></textarea></label>
          </div>
          <label class="stacked">Additional notes<textarea id="notes" rows="2" placeholder="Any previous reports or instructions"></textarea></label>
        </div>

        <div class="card ai-card">
          <div class="section-title"><span class="section-ico section-ico-ai" aria-hidden="true"></span><h3>AI Assistant</h3></div>
          <p class="muted">In Mock mode, this uses local pre-stored dataset replies. No internet or AI service required.</p>
          <div class="ai-row">
            <input id="aiMessage" type="text" placeholder="I have tooth pain and swelling, what should I select?" />
            <button id="askAi" type="button">Ask Assistant</button>
            <button id="clearAiReply" type="button" class="secondary">Clear Reply</button>
          </div>
          <div id="aiHintButtons" class="hint-buttons"></div>
          <div id="aiReply" class="ai-reply"></div>
        </div>

        <div class="footer-bar">
          <div id="selectedView">Currently Selected: -</div>
          <div class="footer-actions">
            <button id="bookBtn" type="button">Book Appointment</button>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <section id="queueTab" class="tab-panel">
        <div class="card">
          <div class="card-header">
            <div class="section-title"><span class="section-ico section-ico-queue" aria-hidden="true"></span><h2>Live Queue Board</h2></div>
            <div class="mini-filters">
              <input type="date" id="queueDate" value="<?= htmlspecialchars($today, ENT_QUOTES) ?>" />
              <select id="queueLocation">
                <option value="">All locations</option>
              </select>
              <button id="refreshQueue" type="button">Refresh</button>
            </div>
          </div>
          <div class="queue-board">
            <aside class="queue-list-panel">
              <div class="queue-list-head">
                <h3>Patient Queue</h3>
                <span id="queueCount" class="inline-status">0 waiting</span>
              </div>
              <div id="queueList" class="queue-list"></div>
            </aside>

            <section class="queue-detail-panel">
              <div id="queueDetailHeader" class="queue-patient-header"></div>
              <div class="queue-detail-grid">
                <div class="queue-info-card">
                  <h4>Basic Information</h4>
                  <div id="queueBasicInfo" class="queue-info-lines"></div>
                </div>
                <div class="queue-info-card">
                  <h4>Appointment Schedule</h4>
                  <div id="queueScheduleInfo" class="queue-info-lines"></div>
                </div>
                <div class="queue-info-card">
                  <h4>Queue Snapshot</h4>
                  <div id="queueSnapshotInfo" class="queue-info-lines"></div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </section>

      <?php if ($canOps): ?>
      <section id="opsTab" class="tab-panel">
        <?php if ($canSelfService): ?>
        <div class="card">
          <div class="section-title"><span class="section-ico section-ico-usercard" aria-hidden="true"></span><h2>Patient Self-Service Kiosk</h2></div>
          <p class="muted">Use your phone number or email to find your appointment, then check in, check out, cancel, or print your receipt.</p>
          <form id="patientLookupForm" class="field-grid cols-3">
            <label>Phone<input id="patientLookupPhone" name="phone" type="text" placeholder="+8801XXXXXXXXX" /></label>
            <label>Email<input id="patientLookupEmail" name="email" type="email" placeholder="you@email.com" /></label>
            <div class="footer-actions top-gap">
              <button type="submit">Find My Appointment</button>
            </div>
          </form>
          <p id="patientLookupStatus" class="ai-reply"></p>
        </div>
        <div class="card">
          <div class="section-title"><span class="section-ico section-ico-list" aria-hidden="true"></span><h3>My Appointment Results</h3></div>
          <div class="table-wrap admin-table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Patient</th>
                  <th>Appointment</th>
                  <th>Doctor</th>
                  <th>Status</th>
                  <th>Self-Service Actions</th>
                </tr>
              </thead>
              <tbody id="patientSelfServiceBody"></tbody>
            </table>
          </div>
        </div>
        <?php else: ?>
        <div class="admin-subtabs">
          <button class="admin-subtab-btn active" type="button" data-admin-tab="adminAppointmentsPanel">Appointments</button>
          <?php if ($isAdmin): ?><button class="admin-subtab-btn" type="button" data-admin-tab="adminCreateUserPanel">Create User</button><?php endif; ?>
          <?php if ($isAdmin): ?><button class="admin-subtab-btn" type="button" data-admin-tab="adminLocationsPanel">Locations</button><?php endif; ?>
          <?php if ($isAdmin): ?><button class="admin-subtab-btn" type="button" data-admin-tab="adminDepartmentsPanel">Departments</button><?php endif; ?>
          <?php if ($isAdmin): ?><button class="admin-subtab-btn" type="button" data-admin-tab="adminDoctorDepartmentsPanel">Doctor Departments</button><?php endif; ?>
          <?php if ($isAdmin): ?><button class="admin-subtab-btn" type="button" data-admin-tab="adminDoctorLocationsPanel">User Locations</button><?php endif; ?>
          <?php if ($isAdmin): ?><button class="admin-subtab-btn" type="button" data-admin-tab="adminUsersPanel">Users and Reset</button><?php endif; ?>
          <?php if ($isAdmin): ?><button class="admin-subtab-btn" type="button" data-admin-tab="adminAuditPanel">Audit Logs</button><?php endif; ?>
        </div>

        <div id="adminAppointmentsPanel" class="admin-subpanel active">
          <div class="card">
            <div class="card-header">
            <div class="section-title"><span class="section-ico section-ico-ops" aria-hidden="true"></span><h2><?= $opsTabLabel ?> - Appointments</h2></div>
              <div class="mini-filters">
                <?php if ($isDoctor): ?>
                <input type="month" id="doctorMonth" value="<?= htmlspecialchars($currentMonth, ENT_QUOTES) ?>" />
                <?php else: ?>
                <input type="date" id="adminDate" value="<?= htmlspecialchars($today, ENT_QUOTES) ?>" />
                <?php if ($isAdmin): ?>
                <select id="adminLocation">
                  <option value="">All locations</option>
                </select>
                <?php endif; ?>
                <select id="adminStatus">
                  <option value="">All statuses</option>
                  <option>scheduled</option>
                  <option>checked_in</option>
                  <option>in_consultation</option>
                  <option>completed</option>
                  <option>cancelled</option>
                </select>
                <?php endif; ?>
                <button id="refreshAdmin" type="button">Load</button>
              </div>
            </div>

            <?php if ($isDoctor): ?>
            <div id="doctorCalendar" class="doctor-calendar"></div>
            <?php else: ?>
            <div class="table-wrap admin-table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Current Slot</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Status Update</th>
                    <th>Reschedule</th>
                    <th>Receipt</th>
                  </tr>
                </thead>
                <tbody id="adminBody"></tbody>
              </table>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($isAdmin): ?>
        <div id="adminCreateUserPanel" class="admin-subpanel">
          <div class="card">
            <h3>Create User (Doctor/Patient)</h3>
            <form id="createUserForm" class="field-grid cols-2" enctype="multipart/form-data">
              <label>Full name<input name="full_name" type="text" required /></label>
              <label>Username<input name="username" type="text" required /></label>
              <label>Role
                <select name="role" required>
                  <option value="patient">patient</option>
                  <option value="doctor">doctor</option>
                </select>
              </label>
              <label id="createUserLocationsWrap" class="stacked full-row hidden">Assigned locations
                <select id="createUserLocations" name="location_ids[]" multiple></select>
              </label>
              <label id="createUserDepartmentsWrap" class="stacked full-row hidden">Doctor department
                <select id="createUserDepartments" name="department_id"></select>
              </label>
              <label id="createUserWorkingDaysWrap" class="stacked full-row hidden">Doctor working days
                <select id="createUserWorkingDays" name="working_days[]" multiple>
                  <option value="1">Monday</option>
                  <option value="2">Tuesday</option>
                  <option value="3">Wednesday</option>
                  <option value="4">Thursday</option>
                  <option value="5">Friday</option>
                  <option value="6">Saturday</option>
                  <option value="0">Sunday</option>
                </select>
              </label>
              <label id="createUserSession1StartWrap" class="hidden">Session 1 start time
                <input id="createUserSession1Start" name="session1_start" type="time" />
              </label>
              <label id="createUserSession1EndWrap" class="hidden">Session 1 end time
                <input id="createUserSession1End" name="session1_end" type="time" />
              </label>
              <label id="createUserSession2StartWrap" class="hidden">Session 2 start time (optional)
                <input id="createUserSession2Start" name="session2_start" type="time" />
              </label>
              <label id="createUserSession2EndWrap" class="hidden">Session 2 end time (optional)
                <input id="createUserSession2End" name="session2_end" type="time" />
              </label>
              <label>Initial password
                <div class="password-wrap">
                  <input id="createUserPassword" name="password" type="password" required />
                  <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" title="Show or hide password" data-toggle-password="#createUserPassword"></button>
                </div>
              </label>
              <label class="stacked full-row">Profile picture<input name="profile_image" type="file" accept="image/*" required /></label>
              <div class="footer-actions top-gap full-row">
                <button type="submit">Create User</button>
              </div>
            </form>
            <p id="createUserStatus" class="ai-reply"></p>
          </div>
        </div>

        <div id="adminLocationsPanel" class="admin-subpanel">
          <div class="card">
            <h3>Locations Management</h3>
            <form id="createLocationForm" class="field-grid cols-2">
              <label>Location name<input name="name" type="text" placeholder="e.g. East Branch" required /></label>
              <div class="footer-actions top-gap">
                <button type="submit">Add Location</button>
              </div>
            </form>
            <p id="createLocationStatus" class="ai-reply"></p>

            <div class="table-wrap compact-table top-gap">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="locationsBody"></tbody>
              </table>
            </div>
          </div>
        </div>

        <div id="adminDepartmentsPanel" class="admin-subpanel">
          <div class="card">
            <h3>Departments Management</h3>
            <form id="createDepartmentForm" class="field-grid cols-2">
              <label>Department name<input name="name" type="text" placeholder="e.g. Cancer" required /></label>
              <div class="footer-actions top-gap">
                <button type="submit">Add Department</button>
              </div>
            </form>
            <p id="createDepartmentStatus" class="ai-reply"></p>

            <form id="departmentBranchForm" class="field-grid cols-2 top-gap">
              <label>Branch
                <select id="departmentBranchLocation" name="location_id" required></select>
              </label>
              <label>Departments for selected branch
                <select id="departmentBranchDepartments" name="department_ids[]" multiple required></select>
              </label>
              <div class="footer-actions top-gap full-row">
                <button type="submit">Save Branch Departments</button>
              </div>
            </form>
            <p id="departmentBranchStatus" class="ai-reply"></p>

            <div class="table-wrap compact-table top-gap">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="departmentsBody"></tbody>
              </table>
            </div>

            <div class="table-wrap compact-table top-gap">
              <table>
                <thead>
                  <tr>
                    <th>Branch</th>
                    <th>Departments</th>
                  </tr>
                </thead>
                <tbody id="departmentBranchBody"></tbody>
              </table>
            </div>
          </div>
        </div>

        <div id="adminDoctorDepartmentsPanel" class="admin-subpanel">
          <div class="card">
            <h3>Doctor Department Assignment</h3>
            <form id="doctorDepartmentsForm" class="field-grid cols-2">
              <label>Doctor
                <select id="doctorDepartmentsDoctor" name="doctor_user_id" required></select>
              </label>
              <label>Assigned department
                <select id="doctorDepartmentsList" name="department_id" required></select>
              </label>
              <div class="footer-actions top-gap full-row">
                <button type="submit">Save Doctor Departments</button>
              </div>
            </form>
            <p id="doctorDepartmentsStatus" class="ai-reply"></p>

            <div class="table-wrap top-gap">
              <table>
                <thead>
                  <tr>
                    <th>Photo</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Departments</th>
                  </tr>
                </thead>
                <tbody id="doctorDepartmentsBody"></tbody>
              </table>
            </div>
          </div>
        </div>

        <div id="adminDoctorLocationsPanel" class="admin-subpanel">
          <div class="card">
            <h3>Location Assignment (Doctor/Patient)</h3>
            <form id="doctorLocationsForm" class="field-grid cols-2">
              <label>User
                <select id="doctorLocationDoctor" name="doctor_user_id" required></select>
              </label>
              <label>Assigned branches
                <select id="doctorLocationLocations" name="location_ids[]" multiple required></select>
              </label>
              <div class="footer-actions top-gap full-row">
                <button type="submit">Save User Locations</button>
              </div>
            </form>
            <p id="doctorLocationsStatus" class="ai-reply"></p>

            <div class="table-wrap top-gap">
              <table>
                <thead>
                  <tr>
                    <th>Photo</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Locations</th>
                  </tr>
                </thead>
                <tbody id="doctorLocationsBody"></tbody>
              </table>
            </div>
          </div>
        </div>

        <div id="adminUsersPanel" class="admin-subpanel">
          <div class="card">
            <h3>User Directory and Admin Reset Password</h3>
            <div class="mini-filters">
              <select id="userRoleFilter">
                <option value="">All roles</option>
                <option value="doctor">doctor</option>
                <option value="patient">patient</option>
                <option value="admin">admin</option>
              </select>
              <button type="button" id="refreshUsers">Load Users</button>
            </div>
            <div class="table-wrap compact-table">
              <table>
                <thead>
                  <tr>
                    <th>Photo</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Reset Password</th>
                  </tr>
                </thead>
                <tbody id="usersBody"></tbody>
              </table>
            </div>
            <p id="usersStatus" class="ai-reply"></p>
          </div>
        </div>

        <div id="adminAuditPanel" class="admin-subpanel">
          <div class="card">
            <div class="card-header">
              <h3>Audit Logs</h3>
              <button id="refreshAudit" type="button">Refresh Logs</button>
            </div>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Time</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Details</th>
                  </tr>
                </thead>
                <tbody id="auditBody"></tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </section>
      <?php endif; ?>

      <?php if ($canInsights): ?>
      <section id="insightsTab" class="tab-panel">
        <div class="card">
          <div class="card-header">
            <h2>Insights</h2>
            <div class="mini-filters">
              <input type="date" id="insightsDate" value="<?= htmlspecialchars($today, ENT_QUOTES) ?>" />
              <button id="refreshInsights" type="button">Analyze</button>
            </div>
          </div>

          <div id="summaryCards" class="metric-grid"></div>

          <div class="split-panels">
            <div>
              <h4>By Service</h4>
              <ul id="serviceBreakdown" class="line-list"></ul>
            </div>
            <div>
              <h4>By Location</h4>
              <ul id="locationBreakdown" class="line-list"></ul>
            </div>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <section id="accountTab" class="tab-panel">
        <?php if ($isDoctor): ?>
        <div class="account-subtabs">
          <button class="account-subtab-btn active" type="button" data-account-tab="accountProfilePanel">Profile</button>
          <button class="account-subtab-btn" type="button" data-account-tab="accountPasswordPanel">Password</button>
          <button class="account-subtab-btn" type="button" data-account-tab="accountAvailabilityPanel">Availability</button>
        </div>
        <?php endif; ?>

        <?php if ($isDoctor): ?><div id="accountProfilePanel" class="account-subpanel active"><?php endif; ?>
        <?php if ($isAdmin): ?>
        <div class="card">
          <div class="section-title"><span class="section-ico section-ico-account" aria-hidden="true"></span><h3>Update My Name</h3></div>
          <form id="updateProfileNameForm" class="field-grid cols-2">
            <label>Full name
              <input id="updateProfileNameInput" name="full_name" type="text" value="<?= $fullName ?>" required />
            </label>
            <div class="footer-actions top-gap">
              <button type="submit">Update Name</button>
            </div>
          </form>
          <p id="updateProfileNameStatus" class="ai-reply"></p>
        </div>
        <?php endif; ?>

        <div class="card">
          <div class="section-title"><span class="section-ico section-ico-account" aria-hidden="true"></span><h3>Update Profile Picture</h3></div>
          <form id="updateProfileImageForm" class="field-grid cols-2" enctype="multipart/form-data">
            <div class="profile-preview-box">
              <img id="accountProfilePreview" class="profile-preview-large" src="<?= $profileImage ?>" alt="Profile preview" />
            </div>
            <label>Choose image
              <input id="accountProfileImageInput" name="profile_image" type="file" accept="image/*" required />
            </label>
            <div class="footer-actions top-gap full-row">
              <button type="submit">Update Profile Image</button>
            </div>
          </form>
          <p id="updateProfileStatus" class="ai-reply"></p>
        </div>
        <?php if ($isDoctor): ?></div><?php endif; ?>

        <?php if ($isDoctor): ?><div id="accountPasswordPanel" class="account-subpanel"><?php endif; ?>
        <div class="card">
          <div class="section-title"><span class="section-ico section-ico-lock" aria-hidden="true"></span><h3>Change My Password</h3></div>
          <form id="changePasswordForm" class="field-grid cols-2">
            <label>Current password
              <div class="password-wrap">
                <input id="currentPasswordInput" name="current_password" type="password" required />
                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" title="Show or hide password" data-toggle-password="#currentPasswordInput"></button>
              </div>
            </label>
            <label>New password
              <div class="password-wrap">
                <input id="newPasswordInput" name="new_password" type="password" required />
                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" title="Show or hide password" data-toggle-password="#newPasswordInput"></button>
              </div>
            </label>
            <div class="footer-actions top-gap">
              <button type="submit">Change Password</button>
            </div>
          </form>
          <p id="changePasswordStatus" class="ai-reply"></p>
        </div>
        <?php if ($isDoctor): ?></div><?php endif; ?>

        <?php if ($isDoctor): ?>
        <div id="accountAvailabilityPanel" class="account-subpanel">
          <div class="card">
            <div class="section-title"><span class="section-ico section-ico-calendar" aria-hidden="true"></span><h3>My Weekly Availability</h3></div>
            <p class="muted">Manage days and sessions when you see patients. You can add multiple sessions in a day (example: 14:00-17:00 and 20:00-22:00).</p>
            <div id="doctorAvailabilityCalendar" class="availability-calendar"></div>
            <form id="doctorAvailabilityForm" class="field-grid cols-3">
              <label>Day
                <select id="availabilityWeekday" name="weekday" required>
                  <option value="1">Monday</option>
                  <option value="2">Tuesday</option>
                  <option value="3">Wednesday</option>
                  <option value="4">Thursday</option>
                  <option value="5">Friday</option>
                  <option value="6">Saturday</option>
                  <option value="0">Sunday</option>
                </select>
              </label>
              <label>Start time
                <input id="availabilityStartTime" name="start_time" type="time" required />
              </label>
              <label>End time
                <input id="availabilityEndTime" name="end_time" type="time" required />
              </label>
              <div class="footer-actions top-gap full-row">
                <button type="submit">Add Session</button>
              </div>
            </form>
            <p id="doctorAvailabilityStatus" class="ai-reply"></p>

            <div class="table-wrap compact-table">
              <table>
                <thead>
                  <tr>
                    <th>Day</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="doctorAvailabilityBody"></tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </section>
    </section>
  </main>

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <div id="appModal" class="app-modal hidden" aria-hidden="true">
    <div class="app-modal-backdrop" data-modal-close="backdrop"></div>
    <div class="app-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="appModalTitle">
      <h3 id="appModalTitle">Notification</h3>
      <p id="appModalMessage">Message</p>
      <div class="app-modal-actions">
        <button id="appModalCancelBtn" type="button" class="secondary hidden">Cancel</button>
        <button id="appModalOkBtn" type="button">OK</button>
      </div>
    </div>
  </div>

  <div id="doctorDayModal" class="app-modal hidden" aria-hidden="true">
    <div class="app-modal-backdrop" data-day-modal-close="backdrop"></div>
    <div class="app-modal-dialog doctor-day-modal" role="dialog" aria-modal="true" aria-labelledby="doctorDayModalTitle">
      <h3 id="doctorDayModalTitle">Appointments</h3>
      <div id="doctorDayModalBody" class="doctor-day-modal-body"></div>
      <div class="app-modal-actions">
        <button id="doctorDayModalCloseBtn" type="button">Close</button>
      </div>
    </div>
  </div>

  <div id="appContext" data-role="<?= $role ?>" data-name="<?= $fullName ?>" data-profile="<?= $profileImage ?>"></div>
  <script src="assets/app.js"></script>
</body>
</html>
