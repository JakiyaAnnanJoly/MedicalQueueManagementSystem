const ctxEl = document.getElementById('appContext');
const appRole = ctxEl?.dataset.role || '';

const perms = {
  canBook: ['admin', 'patient'].includes(appRole),
  canAdminDesk: ['admin', 'doctor'].includes(appRole),
  canSelfService: appRole === 'patient',
  canInsights: ['admin', 'doctor'].includes(appRole),
  canReschedule: appRole === 'admin',
  canAdminMgmt: appRole === 'admin',
};

const ui = {
  tabButtons: document.querySelectorAll('.tab-btn'),
  navJumps: document.querySelectorAll('.nav-jump'),
  panels: document.querySelectorAll('.tab-panel'),
  adminSubButtons: document.querySelectorAll('.admin-subtab-btn'),
  adminSubPanels: document.querySelectorAll('.admin-subpanel'),
  accountSubButtons: document.querySelectorAll('.account-subtab-btn'),
  accountSubPanels: document.querySelectorAll('.account-subpanel'),

  assistantMode: document.getElementById('assistantMode'),

  roleIntro: document.getElementById('roleIntro'),
  roleSummaryCards: document.getElementById('roleSummaryCards'),
  roleTableTitle: document.getElementById('roleTableTitle'),
  roleTableHead: document.getElementById('roleTableHead'),
  roleTableBody: document.getElementById('roleTableBody'),

  appointmentDate: document.getElementById('appointmentDate'),
  service: document.getElementById('service'),
  location: document.getElementById('location'),
  doctor: document.getElementById('doctor'),
  slots: document.getElementById('slots'),
  slotHint: document.getElementById('slotHint'),
  selectedView: document.getElementById('selectedView'),

  fullName: document.getElementById('fullName'),
  email: document.getElementById('email'),
  phone: document.getElementById('phone'),
  symptoms: document.getElementById('symptoms'),
  notes: document.getElementById('notes'),

  aiMessage: document.getElementById('aiMessage'),
  aiReply: document.getElementById('aiReply'),
  askAi: document.getElementById('askAi'),
  clearAiReply: document.getElementById('clearAiReply'),
  aiHintButtons: document.getElementById('aiHintButtons'),

  bookBtn: document.getElementById('bookBtn'),

  queueDate: document.getElementById('queueDate'),
  queueLocation: document.getElementById('queueLocation'),
  queueList: document.getElementById('queueList'),
  queueCount: document.getElementById('queueCount'),
  queueDetailHeader: document.getElementById('queueDetailHeader'),
  queueBasicInfo: document.getElementById('queueBasicInfo'),
  queueScheduleInfo: document.getElementById('queueScheduleInfo'),
  queueSnapshotInfo: document.getElementById('queueSnapshotInfo'),
  refreshQueue: document.getElementById('refreshQueue'),

  adminDate: document.getElementById('adminDate'),
  doctorMonth: document.getElementById('doctorMonth'),
  adminLocation: document.getElementById('adminLocation'),
  adminStatus: document.getElementById('adminStatus'),
  adminBody: document.getElementById('adminBody'),
  doctorCalendar: document.getElementById('doctorCalendar'),
  refreshAdmin: document.getElementById('refreshAdmin'),

  patientLookupForm: document.getElementById('patientLookupForm'),
  patientLookupPhone: document.getElementById('patientLookupPhone'),
  patientLookupEmail: document.getElementById('patientLookupEmail'),
  patientLookupStatus: document.getElementById('patientLookupStatus'),
  patientSelfServiceBody: document.getElementById('patientSelfServiceBody'),

  insightsDate: document.getElementById('insightsDate'),
  refreshInsights: document.getElementById('refreshInsights'),
  summaryCards: document.getElementById('summaryCards'),
  serviceBreakdown: document.getElementById('serviceBreakdown'),
  locationBreakdown: document.getElementById('locationBreakdown'),

  changePasswordForm: document.getElementById('changePasswordForm'),
  changePasswordStatus: document.getElementById('changePasswordStatus'),
  updateProfileImageForm: document.getElementById('updateProfileImageForm'),
  updateProfileImageInput: document.getElementById('accountProfileImageInput'),
  updateProfileStatus: document.getElementById('updateProfileStatus'),
  accountProfilePreview: document.getElementById('accountProfilePreview'),
  updateProfileNameForm: document.getElementById('updateProfileNameForm'),
  updateProfileNameInput: document.getElementById('updateProfileNameInput'),
  updateProfileNameStatus: document.getElementById('updateProfileNameStatus'),
  sidebarUserName: document.getElementById('sidebarUserName'),
  topbarUserName: document.getElementById('topbarUserName'),

  createUserForm: document.getElementById('createUserForm'),
  createUserLocationsWrap: document.getElementById('createUserLocationsWrap'),
  createUserLocations: document.getElementById('createUserLocations'),
  createUserDepartmentsWrap: document.getElementById('createUserDepartmentsWrap'),
  createUserDepartments: document.getElementById('createUserDepartments'),
  createUserWorkingDaysWrap: document.getElementById('createUserWorkingDaysWrap'),
  createUserWorkingDays: document.getElementById('createUserWorkingDays'),
  createUserSession1StartWrap: document.getElementById('createUserSession1StartWrap'),
  createUserSession1Start: document.getElementById('createUserSession1Start'),
  createUserSession1EndWrap: document.getElementById('createUserSession1EndWrap'),
  createUserSession1End: document.getElementById('createUserSession1End'),
  createUserSession2StartWrap: document.getElementById('createUserSession2StartWrap'),
  createUserSession2Start: document.getElementById('createUserSession2Start'),
  createUserSession2EndWrap: document.getElementById('createUserSession2EndWrap'),
  createUserSession2End: document.getElementById('createUserSession2End'),
  createUserStatus: document.getElementById('createUserStatus'),

  createLocationForm: document.getElementById('createLocationForm'),
  createLocationStatus: document.getElementById('createLocationStatus'),
  locationsBody: document.getElementById('locationsBody'),

  createDepartmentForm: document.getElementById('createDepartmentForm'),
  createDepartmentStatus: document.getElementById('createDepartmentStatus'),
  departmentsBody: document.getElementById('departmentsBody'),
  departmentBranchForm: document.getElementById('departmentBranchForm'),
  departmentBranchLocation: document.getElementById('departmentBranchLocation'),
  departmentBranchDepartments: document.getElementById('departmentBranchDepartments'),
  departmentBranchStatus: document.getElementById('departmentBranchStatus'),
  departmentBranchBody: document.getElementById('departmentBranchBody'),

  doctorLocationsForm: document.getElementById('doctorLocationsForm'),
  doctorLocationDoctor: document.getElementById('doctorLocationDoctor'),
  doctorLocationLocations: document.getElementById('doctorLocationLocations'),
  doctorLocationsBody: document.getElementById('doctorLocationsBody'),
  doctorLocationsStatus: document.getElementById('doctorLocationsStatus'),
  doctorAvailabilityForm: document.getElementById('doctorAvailabilityForm'),
  doctorAvailabilityStatus: document.getElementById('doctorAvailabilityStatus'),
  doctorAvailabilityBody: document.getElementById('doctorAvailabilityBody'),
  doctorAvailabilityCalendar: document.getElementById('doctorAvailabilityCalendar'),

  doctorDepartmentsForm: document.getElementById('doctorDepartmentsForm'),
  doctorDepartmentsDoctor: document.getElementById('doctorDepartmentsDoctor'),
  doctorDepartmentsList: document.getElementById('doctorDepartmentsList'),
  doctorDepartmentsBody: document.getElementById('doctorDepartmentsBody'),
  doctorDepartmentsStatus: document.getElementById('doctorDepartmentsStatus'),

  userRoleFilter: document.getElementById('userRoleFilter'),
  refreshUsers: document.getElementById('refreshUsers'),
  usersBody: document.getElementById('usersBody'),
  usersStatus: document.getElementById('usersStatus'),
  refreshAudit: document.getElementById('refreshAudit'),
  auditBody: document.getElementById('auditBody'),

  appModal: document.getElementById('appModal'),
  appModalTitle: document.getElementById('appModalTitle'),
  appModalMessage: document.getElementById('appModalMessage'),
  appModalOkBtn: document.getElementById('appModalOkBtn'),
  appModalCancelBtn: document.getElementById('appModalCancelBtn'),
  doctorDayModal: document.getElementById('doctorDayModal'),
  doctorDayModalTitle: document.getElementById('doctorDayModalTitle'),
  doctorDayModalBody: document.getElementById('doctorDayModalBody'),
  doctorDayModalCloseBtn: document.getElementById('doctorDayModalCloseBtn'),
};

let selectedTime = '';
let doctors = [];
let locations = [];
let departments = [];
let bookingDepartments = [];
let doctorLocationDirectory = [];
let departmentLocationDirectory = [];
let doctorDepartmentDirectory = [];
let queueRows = [];
let selectedQueueId = 0;
let doctorMonthRows = [];

const defaultAiHints = [
  'I have chest pain and shortness of breath. What should I do?',
  'My tooth hurts and gum is swollen. Which department should I choose?',
  'I twisted my knee while playing. Is Orthopedics correct?',
  'High fever for 2 days with headache. Which service should I pick?',
  'Unexplained weight loss and fatigue for weeks. Which department?',
  'How do I book and print the appointment receipt?',
];

function escapeHtml(value) {
  const div = document.createElement('div');
  div.textContent = String(value ?? '');
  return div.innerHTML;
}

function normalizePublicPath(path, fallback = '') {
  const raw = String(path || '').trim();
  if (!raw) return fallback;
  return raw.replace(/^\/+/, '');
}

function formatTime(timeValue) {
  const [h, m] = timeValue.split(':');
  const hh = Number(h);
  const suffix = hh >= 12 ? 'PM' : 'AM';
  const normalized = hh % 12 || 12;
  return `${String(normalized).padStart(2, '0')}:${m} ${suffix}`;
}

function setTab(tabId) {
  ui.tabButtons.forEach((btn) => btn.classList.toggle('active', btn.dataset.tab === tabId));
  ui.navJumps.forEach((btn) => btn.classList.toggle('active', btn.dataset.tab === tabId));
  ui.panels.forEach((panel) => panel.classList.toggle('active', panel.id === tabId));
}

function setAdminSubTab(panelId) {
  ui.adminSubButtons.forEach((btn) => btn.classList.toggle('active', btn.dataset.adminTab === panelId));
  ui.adminSubPanels.forEach((panel) => panel.classList.toggle('active', panel.id === panelId));
}

function setAccountSubTab(panelId) {
  ui.accountSubButtons.forEach((btn) => btn.classList.toggle('active', btn.dataset.accountTab === panelId));
  ui.accountSubPanels.forEach((panel) => panel.classList.toggle('active', panel.id === panelId));
}

function togglePasswordBySelector(selector, buttonEl) {
  if (!selector) return;
  const input = document.querySelector(selector);
  if (!(input instanceof HTMLInputElement)) return;
  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  if (buttonEl) {
    buttonEl.classList.toggle('is-visible', isPassword);
    buttonEl.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
  }
}

function openModal({ title = 'Notice', message = '', showCancel = false, okText = 'OK', cancelText = 'Cancel' }) {
  if (!ui.appModal || !ui.appModalTitle || !ui.appModalMessage || !ui.appModalOkBtn || !ui.appModalCancelBtn) {
    return Promise.resolve(true);
  }

  ui.appModalTitle.textContent = title;
  ui.appModalMessage.textContent = message;
  ui.appModalOkBtn.textContent = okText;
  ui.appModalCancelBtn.textContent = cancelText;
  ui.appModalCancelBtn.classList.toggle('hidden', !showCancel);
  ui.appModal.classList.remove('hidden');
  ui.appModal.setAttribute('aria-hidden', 'false');

  return new Promise((resolve) => {
    const cleanup = () => {
      ui.appModal.classList.add('hidden');
      ui.appModal.setAttribute('aria-hidden', 'true');
      ui.appModalOkBtn.removeEventListener('click', onOk);
      ui.appModalCancelBtn.removeEventListener('click', onCancel);
      ui.appModal.removeEventListener('click', onBackdrop);
      document.removeEventListener('keydown', onEsc);
    };

    const onOk = () => {
      cleanup();
      resolve(true);
    };
    const onCancel = () => {
      cleanup();
      resolve(false);
    };
    const onBackdrop = (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;
      if (target.dataset.modalClose === 'backdrop') {
        cleanup();
        resolve(false);
      }
    };
    const onEsc = (event) => {
      if (event.key !== 'Escape') return;
      cleanup();
      resolve(false);
    };

    ui.appModalOkBtn.addEventListener('click', onOk);
    ui.appModalCancelBtn.addEventListener('click', onCancel);
    ui.appModal.addEventListener('click', onBackdrop);
    document.addEventListener('keydown', onEsc);
  });
}

function showNotice(message, title = 'Notice') {
  return openModal({ title, message, showCancel: false, okText: 'OK' });
}

function askConfirm(message, title = 'Confirm', okText = 'Yes', cancelText = 'No') {
  return openModal({ title, message, showCancel: true, okText, cancelText });
}

function statusChip(status) {
  return `<span class="status-chip s-${escapeHtml(status)}">${escapeHtml(status)}</span>`;
}

function renderAiHintButtons(hints = defaultAiHints) {
  if (!ui.aiHintButtons) return;
  const uniq = hints.filter((item, index, arr) => item && arr.indexOf(item) === index).slice(0, 8);
  ui.aiHintButtons.innerHTML = uniq.map((hint) => `<button type="button" class="hint-btn" data-ai-hint="${escapeHtml(hint)}">${escapeHtml(hint)}</button>`).join('');
}

function updateSelectionView() {
  if (!ui.selectedView) return;
  if (!selectedTime) {
    ui.selectedView.textContent = 'Currently Selected: -';
    return;
  }
  ui.selectedView.textContent = `Currently Selected: ${ui.appointmentDate.value} at ${formatTime(selectedTime)}`;
}

function suggestPriority(symptoms) {
  const text = symptoms.toLowerCase();
  if (!text.trim()) return 1;
  if (text.includes('chest pain') || text.includes('shortness') || text.includes('fainting')) return 5;
  if (text.includes('high fever') || text.includes('severe')) return 4;
  if (text.includes('pain') || text.includes('vomit')) return 3;
  return 2;
}

async function fetchJson(url, options = {}) {
  const res = await fetch(url, options);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Request failed');
  return data;
}

function metricCard(label, value) {
  return `<article class="metric"><div class="label">${escapeHtml(label)}</div><div class="value">${escapeHtml(value)}</div></article>`;
}

function renderLocationOptions(selected = '', includeAll = false) {
  const options = [];
  if (includeAll) options.push(`<option value="">All locations</option>`);
  options.push(...locations.map((loc) => `<option value="${escapeHtml(loc.name)}" ${loc.name === selected ? 'selected' : ''}>${escapeHtml(loc.name)}</option>`));
  return options.join('');
}

function renderDepartmentOptions(selected = '') {
  return bookingDepartments.map((dep) => `<option value="${escapeHtml(dep.name)}" ${dep.name === selected ? 'selected' : ''}>${escapeHtml(dep.name)}</option>`).join('');
}

function doctorsForLocation(locationName, departmentName = '') {
  if (!locationName) return doctors;
  const locationObj = locations.find((loc) => loc.name === locationName);
  if (!locationObj) return [];
  let rows = doctors.filter((doc) => Array.isArray(doc.location_ids) && doc.location_ids.includes(Number(locationObj.id)));
  if (departmentName) {
    const depObj = departments.find((dep) => dep.name === departmentName);
    if (!depObj) return [];
    rows = rows.filter((doc) => Array.isArray(doc.department_ids) && doc.department_ids.includes(Number(depObj.id)));
  }
  return rows;
}

function renderDoctorOptions(selected = '', locationName = '', includeOnDuty = false, departmentName = '') {
  const names = doctorsForLocation(locationName, departmentName).map((d) => d.full_name);
  if (includeOnDuty) names.unshift('Dr. On Duty');
  const uniqNames = names.filter((name, index, arr) => arr.indexOf(name) === index);
  return uniqNames.map((name) => `<option value="${escapeHtml(name)}" ${name === selected ? 'selected' : ''}>${escapeHtml(name)}</option>`).join('');
}

function populateMasterSelects() {
  if (ui.service) {
    const current = ui.service.value;
    ui.service.innerHTML = renderDepartmentOptions(current);
    if (!ui.service.value && bookingDepartments.length) ui.service.value = bookingDepartments[0].name;
  }

  if (ui.location) {
    const current = ui.location.value;
    ui.location.innerHTML = renderLocationOptions(current, false);
    if (!ui.location.value && locations.length) ui.location.value = locations[0].name;
  }

  if (ui.queueLocation) {
    const current = ui.queueLocation.value;
    ui.queueLocation.innerHTML = renderLocationOptions(current, true);
  }

  if (ui.adminLocation) {
    const current = ui.adminLocation.value;
    ui.adminLocation.innerHTML = renderLocationOptions(current, true);
  }

  if (ui.createUserLocations) {
    const selected = selectedMultiValues(ui.createUserLocations);
    ui.createUserLocations.innerHTML = locations.map((loc) => `<option value="${loc.id}">${escapeHtml(loc.name)}</option>`).join('');
    setMultiValues(ui.createUserLocations, selected);
  }

  if (ui.createUserDepartments) {
    const selected = Number(ui.createUserDepartments.value || 0);
    ui.createUserDepartments.innerHTML = departments.map((dep) => `<option value="${dep.id}">${escapeHtml(dep.name)}</option>`).join('');
    if (selected > 0) {
      ui.createUserDepartments.value = String(selected);
    }
  }

  if (ui.doctorLocationLocations) {
    const selected = selectedMultiValues(ui.doctorLocationLocations);
    ui.doctorLocationLocations.innerHTML = locations.map((loc) => `<option value="${loc.id}">${escapeHtml(loc.name)}</option>`).join('');
    setMultiValues(ui.doctorLocationLocations, selected);
  }

  if (ui.departmentBranchLocation) {
    const current = ui.departmentBranchLocation.value;
    ui.departmentBranchLocation.innerHTML = locations.map((loc) => `<option value="${loc.id}" ${String(loc.id) === current ? 'selected' : ''}>${escapeHtml(loc.name)}</option>`).join('');
  }

  if (ui.departmentBranchDepartments) {
    const selected = selectedMultiValues(ui.departmentBranchDepartments);
    ui.departmentBranchDepartments.innerHTML = departments.map((dep) => `<option value="${dep.id}">${escapeHtml(dep.name)}</option>`).join('');
    setMultiValues(ui.departmentBranchDepartments, selected);
  }

  refreshSelect2AdminDesk();
}

function applyDoctorOptions() {
  if (!ui.doctor || !ui.location || !ui.service) return;
  const previous = ui.doctor.value || '';
  const locationName = ui.location.value;
  const departmentName = ui.service.value || '';
  ui.doctor.innerHTML = renderDoctorOptions(previous, locationName, false, departmentName);
  if (!ui.doctor.value) {
    ui.doctor.value = doctorsForLocation(locationName, departmentName)[0]?.full_name || '';
  }
}

async function loadDoctors() {
  try {
    const data = await fetchJson('api/doctors_by_location.php');
    doctors = data.doctors || [];
  } catch (_err) {
    doctors = [];
  }
  applyDoctorOptions();
}

async function loadMasters() {
  const [locationsData, departmentsData] = await Promise.all([
    fetchJson('api/locations.php'),
    fetchJson('api/departments.php'),
  ]);

  locations = (locationsData.locations || []).filter((loc) => loc.is_active === 1 || loc.is_active === '1');
  departments = (departmentsData.departments || []).filter((dep) => dep.is_active === 1 || dep.is_active === '1');
  bookingDepartments = [...departments];

  populateMasterSelects();
}

async function loadBookingDepartments(locationName = '') {
  const params = new URLSearchParams();
  if (locationName) params.set('location', locationName);
  const query = params.toString();
  const data = await fetchJson(`api/departments.php${query ? `?${query}` : ''}`);
  bookingDepartments = (data.departments || []).filter((dep) => dep.is_active === 1 || dep.is_active === '1');

  if (ui.service) {
    const current = ui.service.value;
    ui.service.innerHTML = renderDepartmentOptions(current);
    if (!ui.service.value && bookingDepartments.length) {
      ui.service.value = bookingDepartments[0].name;
    }
  }
}

async function loadRoleDashboard() {
  if (!ui.roleSummaryCards || !ui.roleTableBody || !ui.roleTableHead || !ui.roleTableTitle || !ui.roleIntro) return;

  ui.roleSummaryCards.innerHTML = '<p class="inline-status">Loading...</p>';
  ui.roleTableBody.innerHTML = '<tr><td class="inline-status">Loading...</td></tr>';

  const today = new Date().toISOString().slice(0, 10);

  if (appRole === 'admin') {
    ui.roleIntro.textContent = 'System-wide overview for operations, staffing and audit health.';
    ui.roleTableTitle.textContent = 'Recent Audit Activity';

    const [stats, logs] = await Promise.all([
      fetchJson(`api/dashboard.php?date=${today}`),
      fetchJson('api/audit_logs.php?limit=10'),
    ]);

    const s = stats.summary || {};
    ui.roleSummaryCards.innerHTML = [
      metricCard('Total Appointments', s.total ?? 0),
      metricCard('Active Queue', Number(s.scheduled || 0) + Number(s.checked_in || 0) + Number(s.in_consultation || 0)),
      metricCard('Completed', s.completed ?? 0),
      metricCard('Cancelled', s.cancelled ?? 0),
    ].join('');

    ui.roleTableHead.innerHTML = '<tr><th>Time</th><th>Actor</th><th>Action</th><th>Entity</th></tr>';
    ui.roleTableBody.innerHTML = (logs.logs || []).map((l) => `<tr><td>${escapeHtml(l.created_at)}</td><td>${escapeHtml(l.actor_name || l.actor_username || 'system')}</td><td>${escapeHtml(l.action)}</td><td>${escapeHtml(`${l.entity_type}${l.entity_id ? ` #${l.entity_id}` : ''}`)}</td></tr>`).join('') || '<tr><td colspan="4" class="inline-status">No logs found.</td></tr>';
    return;
  }

  if (appRole === 'doctor') {
    ui.roleIntro.textContent = 'Clinical dashboard focused on your own schedule and patient flow.';
    ui.roleTableTitle.textContent = 'My Appointments Today';

    const [stats, list] = await Promise.all([
      fetchJson(`api/dashboard.php?date=${today}`),
      fetchJson(`api/appointments.php?date=${today}`),
    ]);

    const s = stats.summary || {};
    ui.roleSummaryCards.innerHTML = [
      metricCard('My Total', s.total ?? 0),
      metricCard('In Consultation', s.in_consultation ?? 0),
      metricCard('Completed', s.completed ?? 0),
      metricCard('Avg Priority', s.avg_priority ? Number(s.avg_priority).toFixed(1) : '0.0'),
    ].join('');

    ui.roleTableHead.innerHTML = '<tr><th>Time</th><th>Patient</th><th>Service</th><th>Status</th></tr>';
    ui.roleTableBody.innerHTML = (list.appointments || []).map((r) => `<tr><td>${escapeHtml(r.appointment_time.slice(0, 5))}</td><td>${escapeHtml(r.full_name)}</td><td>${escapeHtml(r.service_name)}</td><td>${statusChip(r.status)}</td></tr>`).join('') || '<tr><td colspan="4" class="inline-status">No appointments for today.</td></tr>';
    return;
  }

  ui.roleIntro.textContent = 'Kiosk dashboard for walk-up booking, self check-in, checkout, and queue viewing.';
  ui.roleTableTitle.textContent = 'Live Queue Snapshot';
  const list = await fetchJson(`api/queue.php?date=${today}`);
  const rows = list.queue || [];

  const active = rows.filter((r) => ['scheduled', 'checked_in', 'in_consultation'].includes(r.status)).length;
  const checkedIn = rows.filter((r) => r.status === 'checked_in').length;
  const consulting = rows.filter((r) => r.status === 'in_consultation').length;

  ui.roleSummaryCards.innerHTML = [
    metricCard('Queue Tokens', rows.length),
    metricCard('Active Queue', active),
    metricCard('Checked In', checkedIn),
    metricCard('In Consultation', consulting),
  ].join('');

  ui.roleTableHead.innerHTML = '<tr><th>Token</th><th>Time</th><th>Service</th><th>Status</th></tr>';
  ui.roleTableBody.innerHTML = rows.map((r) => `<tr><td>#${escapeHtml(r.token_no)}</td><td>${escapeHtml(r.appointment_time.slice(0, 5))}</td><td>${escapeHtml(r.service_name)}</td><td>${statusChip(r.status)}</td></tr>`).join('') || '<tr><td colspan="4" class="inline-status">No active queue today.</td></tr>';
}

async function loadSlots() {
  if (!ui.appointmentDate || !ui.location || !ui.slots || !ui.slotHint) return;
  if (!ui.location.value) {
    ui.slots.innerHTML = '';
    ui.slotHint.textContent = 'No location configured yet. Ask System Admin to add a location.';
    return;
  }
  selectedTime = '';
  updateSelectionView();
  ui.slots.innerHTML = '<p class="muted">Loading slots...</p>';

  const selectedDoctor = String(ui.doctor?.value || '').trim();
  if (!selectedDoctor) {
    ui.slots.innerHTML = '';
    ui.slotHint.textContent = 'No doctor available for selected branch and department.';
    return;
  }

  const q = `date=${encodeURIComponent(ui.appointmentDate.value)}&location=${encodeURIComponent(ui.location.value)}&doctor=${encodeURIComponent(selectedDoctor)}`;
  try {
    const data = await fetchJson(`api/get_slots.php?${q}`);
    const slotStatuses = data.slot_statuses || [];
    ui.slots.innerHTML = '';

    if (!slotStatuses.length) {
      ui.slotHint.textContent = 'No upcoming slots available for this date.';
      return;
    }

    let availableCount = 0;
    slotStatuses.forEach((slotInfo) => {
      const slot = String(slotInfo.time || '');
      const raw = slot.slice(0, 5);
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'slot-btn';
      btn.textContent = raw;

      if (!slotInfo.is_available) {
        btn.classList.add('unavailable');
        btn.disabled = true;
        btn.title = slotInfo.is_taken_doctor
          ? 'Booked for selected doctor'
          : 'Booked for selected location';
      } else {
        availableCount += 1;
        btn.addEventListener('click', () => {
          document.querySelectorAll('.slot-btn').forEach((el) => el.classList.remove('selected'));
          btn.classList.add('selected');
          selectedTime = raw;
          updateSelectionView();
        });
      }

      ui.slots.appendChild(btn);
    });

    ui.slotHint.textContent = `${availableCount} slot(s) available for ${selectedDoctor}. Red slots are already booked. Past times are hidden.`;
  } catch (err) {
    ui.slots.innerHTML = '';
    ui.slotHint.textContent = err.message;
  }
}

async function bookAppointment() {
  if (!perms.canBook || !ui.service) return;

  const payload = {
    service: ui.service.value,
    location: ui.location.value,
    doctor: ui.doctor.value || '',
    date: ui.appointmentDate.value,
    time: selectedTime,
    full_name: ui.fullName.value.trim(),
    email: ui.email.value.trim(),
    phone: ui.phone.value.trim(),
    symptoms: ui.symptoms.value.trim(),
    notes: ui.notes.value.trim(),
    ai_priority_score: suggestPriority(ui.symptoms.value),
  };

  if (!payload.full_name || !payload.email || !payload.phone || !payload.time || !payload.doctor) {
    await showNotice('Please fill personal details, choose a doctor, and select a time slot.', 'Missing Information');
    return;
  }

  try {
    const data = await fetchJson('api/book.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    const shouldOpenReceipt = await askConfirm(
      `Booked successfully. Appointment ID: ${data.appointment_id}. Open printable receipt now?`,
      'Booking Successful',
      'Open Receipt',
      'Later'
    );
    if (shouldOpenReceipt) {
      window.open(data.receipt_url, '_blank');
    }

    await Promise.all([loadSlots(), loadQueue(), loadAdmin(), loadInsights(), loadRoleDashboard()]);
  } catch (err) {
    await showNotice(err.message, 'Booking Failed');
  }
}

async function askAssistant(message) {
  if (!ui.aiReply) return null;
  const cleaned = message.trim();
  if (!cleaned) {
    renderAiReply('Type a question first.');
    return null;
  }

  renderAiReply('Thinking...');

  try {
    const res = await fetch('api/ai_assistant.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: cleaned, mode: ui.assistantMode.value, stream: true }),
    });

    if (!res.ok) {
      const errData = await res.json().catch(() => ({}));
      throw new Error(errData.error || 'Request failed');
    }

    let data = null;
    if (!res.body) {
      data = await res.json();
      renderAiReply(String(data.reply || ''));
    } else {
      const reader = res.body.getReader();
      const decoder = new TextDecoder();
      let buffer = '';
      let partialReply = '';

      while (true) {
        const { value, done } = await reader.read();
        if (done) break;
        buffer += decoder.decode(value, { stream: true });

        let splitAt = buffer.indexOf('\n\n');
        while (splitAt !== -1) {
          const block = buffer.slice(0, splitAt);
          buffer = buffer.slice(splitAt + 2);

          const event = parseSseBlock(block);
          if (event) {
            if (event.name === 'delta' && event.data?.text) {
              partialReply += String(event.data.text);
              renderAiReply(partialReply);
            } else if (event.name === 'final' && event.data) {
              data = event.data;
              partialReply = String(data.reply || partialReply);
              renderAiReply(partialReply);
            } else if (event.name === 'error' && event.data?.error) {
              throw new Error(String(event.data.error));
            }
          }

          splitAt = buffer.indexOf('\n\n');
        }
      }

      if (!data) {
        if (!partialReply.trim()) throw new Error('Streaming response ended unexpectedly.');
        data = { reply: partialReply, provider: 'unknown', mode: ui.assistantMode.value };
        renderAiReply(partialReply);
      }
    }

    if (data.suggested_department && ui.service) ui.service.value = data.suggested_department;
    if (data.suggested_priority && ui.notes && !ui.symptoms.value.trim()) ui.notes.value = `Assistant suggested priority ${data.suggested_priority}.`;

    return data;
  } catch (err) {
    renderAiReply(err.message);
    return null;
  }
}

function renderAiReply(text) {
  if (!ui.aiReply) return;
  const raw = String(text || '').replace(/\r/g, '');
  if (!raw.trim()) {
    ui.aiReply.innerHTML = '';
    return;
  }

  const lines = raw.split('\n');
  const chunks = [];
  let paragraph = [];
  let bullets = [];

  const flushParagraph = () => {
    if (!paragraph.length) return;
    chunks.push(`<p>${paragraph.map((line) => escapeHtml(line)).join('<br>')}</p>`);
    paragraph = [];
  };

  const flushBullets = () => {
    if (!bullets.length) return;
    chunks.push(`<ul>${bullets.map((line) => `<li>${escapeHtml(line)}</li>`).join('')}</ul>`);
    bullets = [];
  };

  lines.forEach((line) => {
    const trimmed = line.trim();
    if (!trimmed) {
      flushParagraph();
      flushBullets();
      return;
    }

    const headingMatch = trimmed.match(/^#{1,3}\s+(.+)$/);
    if (headingMatch) {
      flushParagraph();
      flushBullets();
      chunks.push(`<h4>${escapeHtml(headingMatch[1])}</h4>`);
      return;
    }

    const bulletMatch = trimmed.match(/^([-*•]|\d+\.)\s+(.+)$/);
    if (bulletMatch) {
      flushParagraph();
      bullets.push(bulletMatch[2]);
      return;
    }

    flushBullets();
    paragraph.push(trimmed);
  });

  flushParagraph();
  flushBullets();

  ui.aiReply.innerHTML = chunks.length ? chunks.join('') : `<p>${escapeHtml(raw)}</p>`;
}

function parseSseBlock(block) {
  const lines = block.split('\n');
  let name = 'message';
  const dataLines = [];

  lines.forEach((line) => {
    if (line.startsWith('event:')) {
      name = line.slice(6).trim();
      return;
    }
    if (line.startsWith('data:')) {
      dataLines.push(line.slice(5).trimStart());
    }
  });

  if (!dataLines.length) return null;

  try {
    return { name, data: JSON.parse(dataLines.join('\n')) };
  } catch (err) {
    return null;
  }
}

function queueStatusSummary(rows) {
  const counters = { scheduled: 0, checked_in: 0, in_consultation: 0 };
  rows.forEach((row) => {
    if (Object.prototype.hasOwnProperty.call(counters, row.status)) counters[row.status] += 1;
  });
  return counters;
}

function renderQueuePatientDetails(row) {
  if (!ui.queueDetailHeader || !ui.queueBasicInfo || !ui.queueScheduleInfo || !ui.queueSnapshotInfo) return;

  const statusText = String(row.status || '').replace(/_/g, ' ');
  const isKiosk = appRole === 'patient';
  ui.queueDetailHeader.innerHTML = `
    <div>
      <h3>${isKiosk ? `Token #${escapeHtml(row.token_no)}` : escapeHtml(row.full_name)}</h3>
      <p class="inline-status">Token #${escapeHtml(row.token_no)} • ${escapeHtml(statusText)}</p>
    </div>
    <div>${statusChip(row.status)}</div>
  `;

  ui.queueBasicInfo.innerHTML = [
    `<p><strong>${isKiosk ? 'Token' : 'Phone'}</strong><span>${escapeHtml(isKiosk ? `#${row.token_no}` : (row.phone || '-'))}</span></p>`,
    `<p><strong>Service</strong><span>${escapeHtml(row.service_name)}</span></p>`,
    `<p><strong>Priority</strong><span>${escapeHtml(row.ai_priority_score)}</span></p>`,
  ].join('');

  ui.queueScheduleInfo.innerHTML = [
    `<p><strong>Appointment Time</strong><span>${escapeHtml(formatTime(row.appointment_time.slice(0, 5)))}</span></p>`,
    `<p><strong>Doctor</strong><span>${escapeHtml(row.doctor_name)}</span></p>`,
    `<p><strong>Location</strong><span>${escapeHtml(row.location_name)}</span></p>`,
  ].join('');

  const stats = queueStatusSummary(queueRows);
  ui.queueSnapshotInfo.innerHTML = [
    `<p><strong>Waiting</strong><span>${escapeHtml(queueRows.length)}</span></p>`,
    `<p><strong>Checked In</strong><span>${escapeHtml(stats.checked_in)}</span></p>`,
    `<p><strong>In Consultation</strong><span>${escapeHtml(stats.in_consultation)}</span></p>`,
  ].join('');
}

function renderQueueList() {
  if (!ui.queueList || !ui.queueCount) return;

  if (!queueRows.length) {
    ui.queueCount.textContent = '0 waiting';
    ui.queueList.innerHTML = '<p class="inline-status">No active queue for selected criteria.</p>';
    if (ui.queueDetailHeader) ui.queueDetailHeader.innerHTML = '<p class="inline-status">Select filters to load patient queue.</p>';
    if (ui.queueBasicInfo) ui.queueBasicInfo.innerHTML = '<p class="inline-status">No patient selected.</p>';
    if (ui.queueScheduleInfo) ui.queueScheduleInfo.innerHTML = '<p class="inline-status">No patient selected.</p>';
    if (ui.queueSnapshotInfo) ui.queueSnapshotInfo.innerHTML = '<p class="inline-status">No queue data.</p>';
    return;
  }

  ui.queueCount.textContent = `${queueRows.length} waiting`;
  if (!queueRows.some((row) => Number(row.id) === Number(selectedQueueId))) {
    selectedQueueId = Number(queueRows[0].id);
  }

  ui.queueList.innerHTML = queueRows.map((row) => {
    const isActive = Number(row.id) === Number(selectedQueueId);
    const isKiosk = appRole === 'patient';
    return `
      <button type="button" class="queue-list-item ${isActive ? 'active' : ''}" data-queue-id="${row.id}">
        <div class="queue-list-main">
          <strong>${isKiosk ? `Token #${escapeHtml(row.token_no)}` : escapeHtml(row.full_name)}</strong>
          <span class="inline-status">${escapeHtml(isKiosk ? row.service_name : (row.phone || '-'))}</span>
        </div>
        <div class="queue-list-meta">
          <span>#${escapeHtml(row.token_no)}</span>
          <span>${escapeHtml(row.appointment_time.slice(0, 5))}</span>
          ${statusChip(row.status)}
        </div>
      </button>
    `;
  }).join('');

  const selectedRow = queueRows.find((row) => Number(row.id) === Number(selectedQueueId));
  if (selectedRow) {
    renderQueuePatientDetails(selectedRow);
  }
}

async function loadQueue() {
  if (!ui.queueList) return;
  ui.queueList.innerHTML = '<p class="inline-status">Loading queue...</p>';

  const queueLocationRaw = ui.queueLocation ? String(ui.queueLocation.value || '') : '';
  const queueLocation = ['all', 'all locations', 'all branches'].includes(queueLocationRaw.toLowerCase()) ? '' : queueLocationRaw;
  const q = `date=${encodeURIComponent(ui.queueDate.value)}&location=${encodeURIComponent(queueLocation)}`;
  try {
    const data = await fetchJson(`api/queue.php?${q}`);
    queueRows = data.queue || [];
    renderQueueList();
  } catch (err) {
    queueRows = [];
    if (ui.queueCount) ui.queueCount.textContent = '0 waiting';
    ui.queueList.innerHTML = `<p class="inline-status">${escapeHtml(err.message)}</p>`;
  }
}

function patientLookupContact() {
  return {
    phone: String(ui.patientLookupPhone?.value || '').trim(),
    email: String(ui.patientLookupEmail?.value || '').trim(),
  };
}

function patientActionButtons(row) {
  const status = String(row.status || '');
  const buttons = [];
  if (status === 'scheduled') {
    buttons.push(`<button type="button" data-patient-action="check_in" data-appointment-id="${row.id}">Check In</button>`);
    buttons.push(`<button type="button" class="secondary" data-patient-action="cancel" data-appointment-id="${row.id}">Cancel</button>`);
  } else if (status === 'checked_in' || status === 'in_consultation') {
    buttons.push(`<button type="button" data-patient-action="checkout" data-appointment-id="${row.id}">Check Out</button>`);
  }
  buttons.push(`<a class="receipt-link" target="_blank" href="receipt.php?appointment_id=${row.id}">Print</a>`);
  return `<div class="action-row">${buttons.join('')}</div>`;
}

function renderPatientSelfServiceRows(rows) {
  if (!ui.patientSelfServiceBody) return;
  if (!rows.length) {
    ui.patientSelfServiceBody.innerHTML = '<tr><td colspan="5" class="inline-status">No appointments found. Enter the phone or email used during booking.</td></tr>';
    return;
  }

  ui.patientSelfServiceBody.innerHTML = rows.map((row) => `
    <tr>
      <td>${escapeHtml(row.full_name)}<br><span class="inline-status">${escapeHtml(row.phone || row.email || '-')}</span></td>
      <td>${escapeHtml(row.appointment_date)} ${escapeHtml(String(row.appointment_time || '').slice(0, 5))}<br><span class="inline-status">${escapeHtml(row.service_name)} at ${escapeHtml(row.location_name)}</span></td>
      <td>${escapeHtml(row.doctor_name)}</td>
      <td>${statusChip(row.status)}</td>
      <td>${patientActionButtons(row)}</td>
    </tr>
  `).join('');
}

async function lookupPatientAppointments(event) {
  event?.preventDefault();
  if (!perms.canSelfService || !ui.patientLookupStatus) return;

  const contact = patientLookupContact();
  if (!contact.phone && !contact.email) {
    ui.patientLookupStatus.textContent = 'Enter your phone number or email to find your appointment.';
    renderPatientSelfServiceRows([]);
    return;
  }

  ui.patientLookupStatus.textContent = 'Looking up your appointment...';
  try {
    const data = await fetchJson('api/patient_self_service.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'lookup', ...contact }),
    });
    renderPatientSelfServiceRows(data.appointments || []);
    ui.patientLookupStatus.textContent = `${(data.appointments || []).length} appointment(s) found.`;
  } catch (err) {
    renderPatientSelfServiceRows([]);
    ui.patientLookupStatus.textContent = err.message;
  }
}

async function runPatientSelfServiceAction(appointmentId, action) {
  if (!perms.canSelfService || !ui.patientLookupStatus) return;
  const contact = patientLookupContact();
  if (!contact.phone && !contact.email) {
    ui.patientLookupStatus.textContent = 'Enter your phone number or email before taking an action.';
    return;
  }

  const labels = { check_in: 'check in', checkout: 'check out', cancel: 'cancel' };
  const confirmed = await askConfirm(`Do you want to ${labels[action] || 'continue'} for this appointment?`, 'Confirm Self-Service Action', 'Yes', 'No');
  if (!confirmed) return;

  ui.patientLookupStatus.textContent = 'Updating appointment...';
  try {
    await fetchJson('api/patient_self_service.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, appointment_id: Number(appointmentId), ...contact }),
    });
    ui.patientLookupStatus.textContent = 'Appointment updated.';
    await Promise.all([lookupPatientAppointments(), loadQueue(), loadRoleDashboard()]);
  } catch (err) {
    ui.patientLookupStatus.textContent = err.message;
  }
}

function allowedStatusOptions() {
  if (appRole === 'doctor') return ['in_consultation', 'completed'];
  return ['scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled'];
}

function actionSelect(id, currentStatus) {
  const statuses = allowedStatusOptions();
  const merged = statuses.includes(currentStatus) ? statuses : [currentStatus, ...statuses];
  const disabledAttr = currentStatus === 'completed' ? ' disabled' : '';
  const options = merged.map((s) => `<option value="${s}" ${s === currentStatus ? 'selected' : ''}>${s}</option>`).join('');
  return `<select data-action-status="${id}"${disabledAttr}>${options}</select>`;
}

function rescheduleControls(row) {
  if (!perms.canReschedule) return '<span class="inline-status">Not allowed</span>';
  if (String(row.status || '') === 'completed') return '<span class="inline-status">Completed cannot be rescheduled.</span>';

  const locationOptions = renderLocationOptions(row.location_name, false);
  let doctorOptions = renderDoctorOptions(row.doctor_name, row.location_name, false, row.service_name);
  if (!doctorOptions.includes(`value="${escapeHtml(row.doctor_name)}"`)) {
    doctorOptions = `<option value="${escapeHtml(row.doctor_name)}" selected>${escapeHtml(row.doctor_name)}</option>${doctorOptions}`;
  }

  return `
    <div class="action-row">
      <div class="action-inline"><input data-r-date="${row.id}" type="date" value="${escapeHtml(row.appointment_date)}" /><input data-r-time="${row.id}" type="time" value="${escapeHtml(row.appointment_time.slice(0, 5))}" /></div>
      <div class="action-inline"><select data-r-location="${row.id}" data-r-service="${escapeHtml(row.service_name)}">${locationOptions}</select><select data-r-doctor="${row.id}">${doctorOptions}</select></div>
      <div class="action-inline"><button type="button" data-reschedule="${row.id}">Save</button></div>
    </div>`;
}

function openDoctorDayModal(date, rows) {
  if (!ui.doctorDayModal || !ui.doctorDayModalTitle || !ui.doctorDayModalBody || !ui.doctorDayModalCloseBtn) return;
  ui.doctorDayModalTitle.textContent = `Appointments: ${date}`;

  const sorted = [...rows].sort((a, b) => String(a.appointment_time).localeCompare(String(b.appointment_time)));
  ui.doctorDayModalBody.innerHTML = sorted.map((row) => {
    const completedLocked = String(row.status || '') === 'completed';
    return `
      <article class="doctor-day-item">
        <div class="doctor-day-time">${escapeHtml(row.appointment_time.slice(0, 5))}</div>
        <div>
          <div class="doctor-day-head">
            <h4>${escapeHtml(row.full_name)}</h4>
            ${statusChip(row.status)}
          </div>
          <p><strong>Service:</strong> ${escapeHtml(row.service_name)}</p>
          <p><strong>Phone:</strong> ${escapeHtml(row.phone || '-')}</p>
          <p><strong>Location:</strong> ${escapeHtml(row.location_name)}</p>
          <div class="action-row top-gap">
            ${actionSelect(row.id, row.status)}
            <button type="button" data-save-status="${row.id}" ${completedLocked ? 'disabled' : ''}>Update</button>
          </div>
          ${completedLocked ? '<p class="inline-status">Completed cannot be changed.</p>' : ''}
          <p class="top-gap"><a class="receipt-link" target="_blank" href="receipt.php?appointment_id=${row.id}">Print</a></p>
        </div>
      </article>
    `;
  }).join('') || '<p class="inline-status">No appointments on this date.</p>';

  ui.doctorDayModal.classList.remove('hidden');
  ui.doctorDayModal.setAttribute('aria-hidden', 'false');
}

function closeDoctorDayModal() {
  if (!ui.doctorDayModal) return;
  ui.doctorDayModal.classList.add('hidden');
  ui.doctorDayModal.setAttribute('aria-hidden', 'true');
}

function renderDoctorMonthCalendar(monthValue, rows) {
  if (!ui.doctorCalendar) return;
  if (!/^\d{4}-\d{2}$/.test(monthValue)) {
    ui.doctorCalendar.innerHTML = '<p class="inline-status">Select a valid month.</p>';
    return;
  }

  const [year, month] = monthValue.split('-').map((n) => Number(n));
  const firstDate = new Date(year, month - 1, 1);
  const daysInMonth = new Date(year, month, 0).getDate();
  const startWeekday = firstDate.getDay();
  const dayCounts = {};
  rows.forEach((row) => {
    const d = String(row.appointment_date || '');
    if (d.startsWith(monthValue)) {
      dayCounts[d] = Number(dayCounts[d] || 0) + 1;
    }
  });

  const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  const cells = [];
  for (let i = 0; i < startWeekday; i += 1) {
    cells.push('<div class="doctor-month-cell empty"></div>');
  }
  for (let day = 1; day <= daysInMonth; day += 1) {
    const dayStr = `${monthValue}-${String(day).padStart(2, '0')}`;
    const count = Number(dayCounts[dayStr] || 0);
    cells.push(`
      <button type="button" class="doctor-month-cell day" data-doctor-day="${dayStr}">
        <span class="doctor-month-daynum">${day}</span>
        ${count > 0 ? `<span class="doctor-month-count">${count}</span>` : '<span class="doctor-month-count muted">0</span>'}
      </button>
    `);
  }

  ui.doctorCalendar.innerHTML = `
    <div class="doctor-month-head">${weekDays.map((d) => `<span>${d}</span>`).join('')}</div>
    <div class="doctor-month-grid">${cells.join('')}</div>
  `;
}

async function loadAdmin() {
  if (!perms.canAdminDesk) return;
  if (appRole === 'doctor' && ui.doctorCalendar) {
    ui.doctorCalendar.innerHTML = '<p class="inline-status">Loading appointments...</p>';
  } else if (ui.adminBody) {
    ui.adminBody.innerHTML = '<tr><td colspan="8" class="inline-status">Loading appointments...</td></tr>';
  } else {
    return;
  }

  const q = new URLSearchParams();
  if (appRole === 'doctor') {
    const month = String(ui.doctorMonth?.value || '').trim();
    if (!month) {
      ui.doctorCalendar.innerHTML = '<p class="inline-status">Select a month first.</p>';
      return;
    }
    q.set('month', month);
  } else {
    q.set('date', ui.adminDate.value);
    const adminLocationRaw = String(ui.adminLocation?.value || '');
    const adminLocation = ['all', 'all locations', 'all branches'].includes(adminLocationRaw.toLowerCase()) ? '' : adminLocationRaw;
    if (adminLocation) q.set('location', adminLocation);
    if (ui.adminStatus?.value) q.set('status', ui.adminStatus.value);
  }

  try {
    const data = await fetchJson(`api/appointments.php?${q.toString()}`);
    const rows = data.appointments || [];

    if (appRole === 'doctor') {
      doctorMonthRows = rows;
      renderDoctorMonthCalendar(String(ui.doctorMonth?.value || ''), rows);
      return;
    }

    if (!rows.length) {
      if (ui.adminBody) ui.adminBody.innerHTML = '<tr><td colspan="8" class="inline-status">No appointments found.</td></tr>';
      return;
    }

    if (!ui.adminBody) return;
    ui.adminBody.innerHTML = rows.map((row) => {
      const completedLocked = String(row.status || '') === 'completed';
      return `
      <tr>
        <td>${row.id}</td>
        <td>${escapeHtml(row.full_name)}<br><span class="inline-status">${escapeHtml(row.phone)}</span></td>
        <td>${escapeHtml(row.appointment_date)} ${escapeHtml(row.appointment_time.slice(0, 5))}<br><span class="inline-status">${escapeHtml(row.location_name)}</span></td>
        <td>${escapeHtml(row.service_name)}<br><span class="inline-status">${escapeHtml(row.doctor_name)}</span></td>
        <td>${statusChip(row.status)}</td>
        <td><div class="action-row">${actionSelect(row.id, row.status)}<button type="button" data-save-status="${row.id}" ${completedLocked ? 'disabled' : ''}>Update</button></div>${completedLocked ? '<span class="inline-status">Completed cannot be changed.</span>' : ''}</td>
        <td>${rescheduleControls(row)}</td>
        <td><a class="receipt-link" target="_blank" href="receipt.php?appointment_id=${row.id}">Print</a></td>
      </tr>
    `;
    }).join('');
  } catch (err) {
    if (appRole === 'doctor' && ui.doctorCalendar) {
      ui.doctorCalendar.innerHTML = `<p class="inline-status">${escapeHtml(err.message)}</p>`;
    } else if (ui.adminBody) {
      ui.adminBody.innerHTML = `<tr><td colspan="8" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
    }
  }
}

async function updateStatus(appointmentId) {
  const select = document.querySelector(`[data-action-status="${appointmentId}"]`);
  if (!select) return;

  try {
    await fetchJson('api/update_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ appointment_id: Number(appointmentId), status: select.value }),
    });

    if (appRole === 'doctor') {
      closeDoctorDayModal();
    }
    await Promise.all([loadAdmin(), loadQueue(), loadInsights(), loadRoleDashboard()]);
  } catch (err) {
    await showNotice(err.message, 'Status Update Failed');
  }
}

async function rescheduleAppointment(appointmentId) {
  if (!perms.canReschedule) return;

  const dateEl = document.querySelector(`[data-r-date="${appointmentId}"]`);
  const timeEl = document.querySelector(`[data-r-time="${appointmentId}"]`);
  const locationEl = document.querySelector(`[data-r-location="${appointmentId}"]`);
  const doctorEl = document.querySelector(`[data-r-doctor="${appointmentId}"]`);
  if (!dateEl || !timeEl || !locationEl || !doctorEl) return;

  try {
    await fetchJson('api/reschedule.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ appointment_id: Number(appointmentId), date: dateEl.value, time: timeEl.value, location: locationEl.value, doctor: doctorEl.value }),
    });

    await showNotice('Appointment rescheduled successfully.', 'Updated');
    await Promise.all([loadAdmin(), loadQueue(), loadSlots(), loadInsights(), loadRoleDashboard()]);
  } catch (err) {
    await showNotice(err.message, 'Reschedule Failed');
  }
}

function buildBreakdown(list, emptyText) {
  if (!list.length) return `<li>${escapeHtml(emptyText)}</li>`;
  return list.map((row) => `<li>${escapeHtml(row[0])}: <strong>${escapeHtml(row[1])}</strong></li>`).join('');
}

async function loadInsights() {
  if (!perms.canInsights || !ui.summaryCards) return;

  ui.summaryCards.innerHTML = '<p class="inline-status">Calculating...</p>';
  try {
    const data = await fetchJson(`api/dashboard.php?date=${encodeURIComponent(ui.insightsDate.value)}`);
    const s = data.summary || {};

    ui.summaryCards.innerHTML = [
      metricCard('Total', s.total ?? 0),
      metricCard('Active Queue', Number(s.scheduled || 0) + Number(s.checked_in || 0) + Number(s.in_consultation || 0)),
      metricCard('Completed', s.completed ?? 0),
      metricCard('Avg Priority', s.avg_priority ? Number(s.avg_priority).toFixed(1) : '0.0'),
    ].join('');

    const services = (data.by_service || []).map((i) => [i.service_name, i.count]);
    const locations = (data.by_location || []).map((i) => [i.location_name, i.count]);

    ui.serviceBreakdown.innerHTML = buildBreakdown(services, 'No service data for this date.');
    ui.locationBreakdown.innerHTML = buildBreakdown(locations, 'No location data for this date.');
  } catch (err) {
    ui.summaryCards.innerHTML = `<p class="inline-status">${escapeHtml(err.message)}</p>`;
    if (ui.serviceBreakdown) ui.serviceBreakdown.innerHTML = '<li>Unavailable</li>';
    if (ui.locationBreakdown) ui.locationBreakdown.innerHTML = '<li>Unavailable</li>';
  }
}

function selectedMultiValues(selectEl) {
  if (!(selectEl instanceof HTMLSelectElement)) return [];
  return Array.from(selectEl.selectedOptions).map((opt) => Number(opt.value)).filter((n) => Number.isInteger(n) && n > 0);
}

function setMultiValues(selectEl, values) {
  if (!(selectEl instanceof HTMLSelectElement)) return;
  const set = new Set((values || []).map((v) => Number(v)));
  Array.from(selectEl.options).forEach((opt) => {
    opt.selected = set.has(Number(opt.value));
  });
}

function initSelect2AdminDesk() {
  const jq = window.jQuery;
  if (!jq || !jq.fn || typeof jq.fn.select2 !== 'function') return;

  const targets = [
    { selector: '#adminLocation', multiple: false },
    { selector: '#createUserLocations', multiple: true },
    { selector: '#createUserDepartments', multiple: false },
    { selector: '#createUserWorkingDays', multiple: true },
    { selector: '#doctorLocationLocations', multiple: true },
    { selector: '#doctorDepartmentsList', multiple: false },
    { selector: '#doctorDepartmentsDoctor', multiple: false },
    { selector: '#departmentBranchLocation', multiple: false },
    { selector: '#departmentBranchDepartments', multiple: true },
  ];

  targets.forEach((target) => {
    const el = document.querySelector(target.selector);
    if (!(el instanceof HTMLSelectElement)) return;

    const $el = jq(el);
    if ($el.hasClass('select2-hidden-accessible')) return;

    $el.select2({
      width: '100%',
      closeOnSelect: !target.multiple,
      placeholder: target.multiple ? 'Select branches' : 'Select one',
    });
  });
}

function refreshSelect2AdminDesk() {
  const jq = window.jQuery;
  if (!jq || !jq.fn || typeof jq.fn.select2 !== 'function') return;

  ['#adminLocation', '#createUserLocations', '#createUserDepartments', '#createUserWorkingDays', '#doctorLocationLocations', '#doctorDepartmentsList', '#doctorDepartmentsDoctor', '#departmentBranchLocation', '#departmentBranchDepartments'].forEach((selector) => {
    const el = document.querySelector(selector);
    if (!(el instanceof HTMLSelectElement)) return;
    const $el = jq(el);
    if ($el.hasClass('select2-hidden-accessible')) {
      $el.trigger('change.select2');
    }
  });
}

function toggleCreateUserLocationField() {
  if (!ui.createUserForm || !ui.createUserLocationsWrap || !ui.createUserLocations || !ui.createUserDepartmentsWrap || !ui.createUserDepartments || !ui.createUserWorkingDaysWrap || !ui.createUserWorkingDays || !ui.createUserSession1StartWrap || !ui.createUserSession1Start || !ui.createUserSession1EndWrap || !ui.createUserSession1End || !ui.createUserSession2StartWrap || !ui.createUserSession2Start || !ui.createUserSession2EndWrap || !ui.createUserSession2End) return;
  const roleField = ui.createUserForm.querySelector('[name="role"]');
  const needsLocation = roleField instanceof HTMLSelectElement && ['doctor', 'patient'].includes(roleField.value);
  const isDoctor = roleField instanceof HTMLSelectElement && roleField.value === 'doctor';
  const isStaffSingleLocation = roleField instanceof HTMLSelectElement && ['doctor', 'patient'].includes(roleField.value);
  ui.createUserLocationsWrap.classList.toggle('hidden', !needsLocation);
  ui.createUserLocations.required = needsLocation;
  ui.createUserLocations.multiple = true;
  ui.createUserDepartmentsWrap.classList.toggle('hidden', !isDoctor);
  ui.createUserDepartments.required = isDoctor;
  ui.createUserWorkingDaysWrap.classList.toggle('hidden', !isDoctor);
  ui.createUserWorkingDays.required = isDoctor;
  ui.createUserSession1StartWrap.classList.toggle('hidden', !isDoctor);
  ui.createUserSession1EndWrap.classList.toggle('hidden', !isDoctor);
  ui.createUserSession2StartWrap.classList.toggle('hidden', !isDoctor);
  ui.createUserSession2EndWrap.classList.toggle('hidden', !isDoctor);
  ui.createUserSession1Start.required = isDoctor;
  ui.createUserSession1End.required = isDoctor;

  if (isStaffSingleLocation) {
    const selected = selectedMultiValues(ui.createUserLocations);
    setMultiValues(ui.createUserLocations, selected.length ? [selected[0]] : []);
  }

  refreshSelect2AdminDesk();
}

const weekdayNames = {
  0: 'Sunday',
  1: 'Monday',
  2: 'Tuesday',
  3: 'Wednesday',
  4: 'Thursday',
  5: 'Friday',
  6: 'Saturday',
};

async function loadMyAvailability() {
  if (!ui.doctorAvailabilityBody) return;
  ui.doctorAvailabilityBody.innerHTML = '<tr><td colspan="4" class="inline-status">Loading availability...</td></tr>';
  try {
    const data = await fetchJson('api/my_availability.php');
    const rows = data.availability || [];
    renderAvailabilityCalendar(rows);
    if (!rows.length) {
      ui.doctorAvailabilityBody.innerHTML = '<tr><td colspan="4" class="inline-status">No availability sessions added yet.</td></tr>';
      return;
    }
    ui.doctorAvailabilityBody.innerHTML = rows.map((row) => `
      <tr>
        <td>${escapeHtml(weekdayNames[Number(row.weekday)] || String(row.weekday))}</td>
        <td>${escapeHtml(String(row.start_time).slice(0, 5))}</td>
        <td>${escapeHtml(String(row.end_time).slice(0, 5))}</td>
        <td><button type="button" data-del-availability="${row.id}" class="secondary">Remove</button></td>
      </tr>
    `).join('');
  } catch (err) {
    renderAvailabilityCalendar([]);
    ui.doctorAvailabilityBody.innerHTML = `<tr><td colspan="4" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
  }
}

function renderAvailabilityCalendar(rows) {
  if (!ui.doctorAvailabilityCalendar) return;

  const dayOrder = [1, 2, 3, 4, 5, 6, 0];
  const byDay = new Map(dayOrder.map((day) => [day, []]));
  rows.forEach((row) => {
    const weekday = Number(row.weekday);
    if (!byDay.has(weekday)) return;
    byDay.get(weekday).push({
      id: Number(row.id),
      start: String(row.start_time || '').slice(0, 5),
      end: String(row.end_time || '').slice(0, 5),
    });
  });

  dayOrder.forEach((day) => {
    byDay.get(day).sort((a, b) => String(a.start).localeCompare(String(b.start)));
  });

  ui.doctorAvailabilityCalendar.innerHTML = dayOrder.map((day) => {
    const sessions = byDay.get(day) || [];
    const sessionHtml = sessions.length
      ? sessions.map((s) => `<span class="availability-chip">${escapeHtml(s.start)} - ${escapeHtml(s.end)}</span>`).join('')
      : '<span class="availability-empty">No session</span>';
    return `
      <article class="availability-day">
        <h4>${escapeHtml(weekdayNames[day])}</h4>
        <div class="availability-slots">${sessionHtml}</div>
      </article>
    `;
  }).join('');
}

async function addAvailability(event) {
  event.preventDefault();
  if (!ui.doctorAvailabilityForm || !ui.doctorAvailabilityStatus) return;

  const fd = new FormData(ui.doctorAvailabilityForm);
  const payload = {
    action: 'add',
    weekday: Number(fd.get('weekday') || 0),
    start_time: String(fd.get('start_time') || ''),
    end_time: String(fd.get('end_time') || ''),
  };

  ui.doctorAvailabilityStatus.textContent = 'Saving availability...';
  try {
    await fetchJson('api/my_availability.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    ui.doctorAvailabilityStatus.textContent = 'Availability session added.';
    ui.doctorAvailabilityForm.reset();
    await loadMyAvailability();
  } catch (err) {
    ui.doctorAvailabilityStatus.textContent = err.message;
  }
}

async function deleteAvailability(id) {
  if (!ui.doctorAvailabilityStatus) return;
  ui.doctorAvailabilityStatus.textContent = 'Removing availability...';
  try {
    await fetchJson('api/my_availability.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id: Number(id) }),
    });
    ui.doctorAvailabilityStatus.textContent = 'Availability removed.';
    await loadMyAvailability();
  } catch (err) {
    ui.doctorAvailabilityStatus.textContent = err.message;
  }
}

async function createLocation(event) {
  event.preventDefault();
  if (!ui.createLocationForm || !ui.createLocationStatus) return;

  const fd = new FormData(ui.createLocationForm);
  const name = String(fd.get('name') || '').trim();
  if (!name) {
    ui.createLocationStatus.textContent = 'Location name is required.';
    return;
  }

  ui.createLocationStatus.textContent = 'Adding location...';

  try {
    await fetchJson('api/locations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name }),
    });

    ui.createLocationForm.reset();
    ui.createLocationStatus.textContent = 'Location added successfully.';
    await Promise.all([loadMasters(), loadLocationsTable(), loadDoctorLocationDirectory(), loadDoctorDepartmentsDirectory(), loadDepartmentLocationDirectory(), loadDoctors()]);
  } catch (err) {
    ui.createLocationStatus.textContent = err.message;
  }
}

async function createDepartment(event) {
  event.preventDefault();
  if (!ui.createDepartmentForm || !ui.createDepartmentStatus) return;

  const fd = new FormData(ui.createDepartmentForm);
  const name = String(fd.get('name') || '').trim();
  if (!name) {
    ui.createDepartmentStatus.textContent = 'Department name is required.';
    return;
  }

  ui.createDepartmentStatus.textContent = 'Adding department...';

  try {
    await fetchJson('api/departments.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name }),
    });

    ui.createDepartmentForm.reset();
    ui.createDepartmentStatus.textContent = 'Department added successfully.';
    await Promise.all([loadMasters(), loadDepartmentsTable(), loadDoctorDepartmentsDirectory(), loadDepartmentLocationDirectory()]);
  } catch (err) {
    ui.createDepartmentStatus.textContent = err.message;
  }
}

function syncDepartmentBranchSelection() {
  if (!ui.departmentBranchLocation || !ui.departmentBranchDepartments) return;
  const locationId = Number(ui.departmentBranchLocation.value || 0);
  const row = departmentLocationDirectory.find((item) => Number(item.location_id) === locationId);
  setMultiValues(ui.departmentBranchDepartments, row?.department_ids || []);
  refreshSelect2AdminDesk();
}

async function loadDepartmentLocationDirectory() {
  if (!perms.canAdminMgmt || !ui.departmentBranchBody) return;

  ui.departmentBranchBody.innerHTML = '<tr><td colspan="2" class="inline-status">Loading branch departments...</td></tr>';
  try {
    const data = await fetchJson('api/department_locations.php');
    departmentLocationDirectory = data.mappings || [];

    if (ui.departmentBranchLocation) {
      const current = Number(ui.departmentBranchLocation.value || 0);
      ui.departmentBranchLocation.innerHTML = (data.locations || []).map((loc) => `<option value="${loc.id}" ${Number(loc.id) === current ? 'selected' : ''}>${escapeHtml(loc.name)}</option>`).join('');
      if (!ui.departmentBranchLocation.value && (data.locations || []).length) {
        ui.departmentBranchLocation.value = String(data.locations[0].id);
      }
      syncDepartmentBranchSelection();
    }

    if (!departmentLocationDirectory.length) {
      ui.departmentBranchBody.innerHTML = '<tr><td colspan="2" class="inline-status">No branch-department mappings found.</td></tr>';
      return;
    }

    ui.departmentBranchBody.innerHTML = departmentLocationDirectory.map((item) => `<tr><td>${escapeHtml(item.location_name)}</td><td>${escapeHtml(item.department_names || '-')}</td></tr>`).join('');
  } catch (err) {
    ui.departmentBranchBody.innerHTML = `<tr><td colspan="2" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
  }
}

async function saveDepartmentBranchMapping(event) {
  event.preventDefault();
  if (!ui.departmentBranchForm || !ui.departmentBranchLocation || !ui.departmentBranchDepartments || !ui.departmentBranchStatus) return;

  const locationId = Number(ui.departmentBranchLocation.value || 0);
  const departmentIds = selectedMultiValues(ui.departmentBranchDepartments);
  if (locationId <= 0 || !departmentIds.length) {
    ui.departmentBranchStatus.textContent = 'Select one branch and at least one department.';
    return;
  }

  ui.departmentBranchStatus.textContent = 'Saving branch departments...';

  try {
    await fetchJson('api/department_locations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ location_id: locationId, department_ids: departmentIds }),
    });

    ui.departmentBranchStatus.textContent = 'Branch departments updated.';
    await Promise.all([loadDepartmentLocationDirectory(), loadBookingDepartments(ui.location?.value || '')]);
  } catch (err) {
    ui.departmentBranchStatus.textContent = err.message;
  }
}

function syncDoctorDepartmentsSelection() {
  if (!ui.doctorDepartmentsDoctor || !ui.doctorDepartmentsList) return;
  const doctorId = Number(ui.doctorDepartmentsDoctor.value || 0);
  const row = doctorDepartmentDirectory.find((d) => Number(d.id) === doctorId);
  ui.doctorDepartmentsList.value = row && Number(row.department_id || 0) > 0 ? String(row.department_id) : '';
  refreshSelect2AdminDesk();
}

async function loadDoctorDepartmentsDirectory() {
  if (!perms.canAdminMgmt || !ui.doctorDepartmentsBody) return;

  ui.doctorDepartmentsBody.innerHTML = '<tr><td colspan="5" class="inline-status">Loading doctors...</td></tr>';
  try {
    const data = await fetchJson('api/doctor_departments.php');
    doctorDepartmentDirectory = data.doctors || [];

    if (ui.doctorDepartmentsDoctor) {
      const currentId = Number(ui.doctorDepartmentsDoctor.value || 0);
      ui.doctorDepartmentsDoctor.innerHTML = doctorDepartmentDirectory.map((doctor) => `<option value="${doctor.id}" ${Number(doctor.id) === currentId ? 'selected' : ''}>${escapeHtml(doctor.full_name)}</option>`).join('');
      if (!ui.doctorDepartmentsDoctor.value && doctorDepartmentDirectory.length) {
        ui.doctorDepartmentsDoctor.value = String(doctorDepartmentDirectory[0].id);
      }
    }

    if (ui.doctorDepartmentsList) {
      const selected = Number(ui.doctorDepartmentsList.value || 0);
      ui.doctorDepartmentsList.innerHTML = (data.departments || []).map((dep) => `<option value="${dep.id}">${escapeHtml(dep.name)}</option>`).join('');
      if (selected > 0) {
        ui.doctorDepartmentsList.value = String(selected);
      }
      syncDoctorDepartmentsSelection();
    }

    if (!doctorDepartmentDirectory.length) {
      ui.doctorDepartmentsBody.innerHTML = '<tr><td colspan="5" class="inline-status">No doctors found.</td></tr>';
      return;
    }

    ui.doctorDepartmentsBody.innerHTML = doctorDepartmentDirectory.map((doctor) => {
      const image = normalizePublicPath(doctor.profile_image_path, 'uploads/profiles/default-doctor.svg');
      return `<tr><td><img class="tiny-avatar" src="${escapeHtml(image)}" alt="doctor" /></td><td>${doctor.id}</td><td>${escapeHtml(doctor.full_name)}</td><td>${escapeHtml(doctor.username)}</td><td>${escapeHtml(doctor.department_name || '-')}</td></tr>`;
    }).join('');
  } catch (err) {
    ui.doctorDepartmentsBody.innerHTML = `<tr><td colspan="5" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
  }
}

async function saveDoctorDepartments(event) {
  event.preventDefault();
  if (!ui.doctorDepartmentsForm || !ui.doctorDepartmentsDoctor || !ui.doctorDepartmentsList || !ui.doctorDepartmentsStatus) return;

  const doctorId = Number(ui.doctorDepartmentsDoctor.value || 0);
  const departmentId = Number(ui.doctorDepartmentsList.value || 0);
  if (doctorId <= 0 || departmentId <= 0) {
    ui.doctorDepartmentsStatus.textContent = 'Select one doctor and one department.';
    return;
  }

  ui.doctorDepartmentsStatus.textContent = 'Saving doctor departments...';

  try {
    await fetchJson('api/doctor_departments.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ doctor_user_id: doctorId, department_id: departmentId }),
    });

    ui.doctorDepartmentsStatus.textContent = 'Doctor departments updated.';
    await Promise.all([loadDoctorDepartmentsDirectory(), loadDoctors()]);
  } catch (err) {
    ui.doctorDepartmentsStatus.textContent = err.message;
  }
}

async function loadLocationsTable() {
  if (!perms.canAdminMgmt || !ui.locationsBody) return;

  ui.locationsBody.innerHTML = '<tr><td colspan="3" class="inline-status">Loading locations...</td></tr>';
  try {
    const data = await fetchJson('api/locations.php');
    const rows = data.locations || [];
    if (!rows.length) {
      ui.locationsBody.innerHTML = '<tr><td colspan="3" class="inline-status">No locations found.</td></tr>';
      return;
    }

    ui.locationsBody.innerHTML = rows.map((loc) => `<tr><td>${loc.id}</td><td>${escapeHtml(loc.name)}</td><td>${loc.is_active === 1 || loc.is_active === '1' ? 'active' : 'inactive'}</td></tr>`).join('');
  } catch (err) {
    ui.locationsBody.innerHTML = `<tr><td colspan="3" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
  }
}

async function loadDepartmentsTable() {
  if (!perms.canAdminMgmt || !ui.departmentsBody) return;

  ui.departmentsBody.innerHTML = '<tr><td colspan="3" class="inline-status">Loading departments...</td></tr>';
  try {
    const data = await fetchJson('api/departments.php');
    const rows = data.departments || [];
    if (!rows.length) {
      ui.departmentsBody.innerHTML = '<tr><td colspan="3" class="inline-status">No departments found.</td></tr>';
      return;
    }

    ui.departmentsBody.innerHTML = rows.map((dep) => `<tr><td>${dep.id}</td><td>${escapeHtml(dep.name)}</td><td>${dep.is_active === 1 || dep.is_active === '1' ? 'active' : 'inactive'}</td></tr>`).join('');
  } catch (err) {
    ui.departmentsBody.innerHTML = `<tr><td colspan="3" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
  }
}

function syncDoctorLocationFormSelection() {
  if (!ui.doctorLocationDoctor || !ui.doctorLocationLocations) return;
  const userId = Number(ui.doctorLocationDoctor.value || 0);
  const row = doctorLocationDirectory.find((d) => Number(d.id) === userId);
  ui.doctorLocationLocations.multiple = !(row && ['doctor', 'patient'].includes(String(row.role || '')));
  if (row && ['doctor', 'patient'].includes(String(row.role || ''))) {
    const currentIds = Array.isArray(row.location_ids) ? row.location_ids : [];
    setMultiValues(ui.doctorLocationLocations, currentIds.length ? [currentIds[0]] : []);
  } else {
    setMultiValues(ui.doctorLocationLocations, row?.location_ids || []);
  }
  refreshSelect2AdminDesk();
}

async function loadDoctorLocationDirectory() {
  if (!perms.canAdminMgmt || !ui.doctorLocationsBody) return;

  ui.doctorLocationsBody.innerHTML = '<tr><td colspan="6" class="inline-status">Loading users...</td></tr>';
  try {
    const data = await fetchJson('api/staff_locations.php');
    doctorLocationDirectory = data.staff || [];

    if (ui.doctorLocationDoctor) {
      const currentId = Number(ui.doctorLocationDoctor.value || 0);
      ui.doctorLocationDoctor.innerHTML = doctorLocationDirectory.map((staff) => `<option value="${staff.id}" ${Number(staff.id) === currentId ? 'selected' : ''}>${escapeHtml(staff.full_name)} (${escapeHtml(staff.role)})</option>`).join('');
      if (!ui.doctorLocationDoctor.value && doctorLocationDirectory.length) {
        ui.doctorLocationDoctor.value = String(doctorLocationDirectory[0].id);
      }
      syncDoctorLocationFormSelection();
    }

    if (!doctorLocationDirectory.length) {
      ui.doctorLocationsBody.innerHTML = '<tr><td colspan="6" class="inline-status">No users found.</td></tr>';
      return;
    }

    ui.doctorLocationsBody.innerHTML = doctorLocationDirectory.map((staff) => {
      const fallback = staff.role === 'doctor' ? 'uploads/profiles/default-doctor.svg' : 'uploads/profiles/default-avatar.svg';
      const image = normalizePublicPath(staff.profile_image_path, fallback);
      return `<tr><td><img class="tiny-avatar" src="${escapeHtml(image)}" alt="profile" /></td><td>${staff.id}</td><td>${escapeHtml(staff.full_name)}</td><td>${escapeHtml(staff.username)}</td><td>${escapeHtml(staff.role)}</td><td>${escapeHtml(staff.locations || '-')}</td></tr>`;
    }).join('');
  } catch (err) {
    ui.doctorLocationsBody.innerHTML = `<tr><td colspan="6" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
  }
}

async function saveDoctorLocations(event) {
  event.preventDefault();
  if (!ui.doctorLocationsForm || !ui.doctorLocationsStatus || !ui.doctorLocationDoctor || !ui.doctorLocationLocations) return;

  const userId = Number(ui.doctorLocationDoctor.value || 0);
  let locationIds = selectedMultiValues(ui.doctorLocationLocations);
  const staffRow = doctorLocationDirectory.find((d) => Number(d.id) === userId);
  if (staffRow && ['doctor', 'patient'].includes(String(staffRow.role || '')) && locationIds.length > 1) {
    locationIds = [locationIds[0]];
  }
  if (userId <= 0 || !locationIds.length) {
    ui.doctorLocationsStatus.textContent = 'Select one user and at least one location.';
    return;
  }

  ui.doctorLocationsStatus.textContent = 'Saving user locations...';

  try {
    await fetchJson('api/staff_locations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId, location_ids: locationIds }),
    });

    ui.doctorLocationsStatus.textContent = 'User location mapping updated.';
    await Promise.all([loadDoctorLocationDirectory(), loadDoctors()]);
  } catch (err) {
    ui.doctorLocationsStatus.textContent = err.message;
  }
}

async function createUser(event) {
  event.preventDefault();
  if (!perms.canAdminMgmt || !ui.createUserForm || !ui.createUserStatus) return;

  const fd = new FormData(ui.createUserForm);
  const role = String(fd.get('role') || '');
  const locationIds = fd.getAll('location_ids[]').map((v) => Number(v)).filter((n) => Number.isInteger(n) && n > 0);
  const departmentId = Number(fd.get('department_id') || 0);
  if (['doctor', 'patient'].includes(role) && locationIds.length !== 1) {
    ui.createUserStatus.textContent = 'Please assign exactly one location for this user.';
    return;
  }
  if (role === 'doctor' && departmentId <= 0) {
    ui.createUserStatus.textContent = 'Please assign one department for doctor.';
    return;
  }

  ui.createUserStatus.textContent = 'Creating user...';

  try {
    const res = await fetch('api/create_user.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Failed to create user');

    ui.createUserStatus.textContent = `User created. ID: ${data.user_id}`;
    ui.createUserForm.reset();
    toggleCreateUserLocationField();
    await Promise.all([loadUsers(), loadDoctors(), loadAuditLogs(), loadDoctorLocationDirectory(), loadDoctorDepartmentsDirectory()]);
  } catch (err) {
    ui.createUserStatus.textContent = err.message;
  }
}

function previewAccountProfileImage() {
  if (!ui.updateProfileImageInput || !ui.accountProfilePreview) return;
  const file = ui.updateProfileImageInput.files?.[0];
  if (!file) return;
  const objectUrl = URL.createObjectURL(file);
  ui.accountProfilePreview.src = objectUrl;
}

async function updateMyProfileImage(event) {
  event.preventDefault();
  if (!ui.updateProfileImageForm || !ui.updateProfileStatus) return;

  const fd = new FormData(ui.updateProfileImageForm);
  const file = fd.get('profile_image');
  if (!(file instanceof File) || !file.name) {
    ui.updateProfileStatus.textContent = 'Please choose an image first.';
    return;
  }

  ui.updateProfileStatus.textContent = 'Updating profile image...';

  try {
    const res = await fetch('api/update_profile_image.php', {
      method: 'POST',
      body: fd,
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Failed to update profile image');

    const newSrc = `${normalizePublicPath(data.profile_image_path)}?t=${Date.now()}`;
    document.querySelectorAll('.profile-avatar').forEach((img) => {
      if (img instanceof HTMLImageElement) img.src = newSrc;
    });
    if (ui.accountProfilePreview) {
      ui.accountProfilePreview.src = newSrc;
    }

    ui.updateProfileStatus.textContent = 'Profile image updated successfully.';
    ui.updateProfileImageForm.reset();
    if (perms.canAdminMgmt) {
      await loadUsers();
    }
  } catch (err) {
    ui.updateProfileStatus.textContent = err.message;
  }
}

async function updateMyProfileName(event) {
  event.preventDefault();
  if (!ui.updateProfileNameForm || !ui.updateProfileNameInput || !ui.updateProfileNameStatus) return;

  const fullName = ui.updateProfileNameInput.value.trim();
  if (!fullName) {
    ui.updateProfileNameStatus.textContent = 'Full name is required.';
    return;
  }

  ui.updateProfileNameStatus.textContent = 'Updating name...';
  try {
    const data = await fetchJson('api/update_profile_name.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ full_name: fullName }),
    });

    const updatedName = String(data.full_name || fullName);
    ui.updateProfileNameStatus.textContent = 'Name updated successfully.';
    if (ui.sidebarUserName) ui.sidebarUserName.textContent = updatedName;
    if (ui.topbarUserName) ui.topbarUserName.textContent = updatedName;
    ui.updateProfileNameInput.value = updatedName;
  } catch (err) {
    ui.updateProfileNameStatus.textContent = err.message;
  }
}

async function loadUsers() {
  if (!perms.canAdminMgmt || !ui.usersBody) return;

  ui.usersBody.innerHTML = '<tr><td colspan="7" class="inline-status">Loading users...</td></tr>';
  const role = ui.userRoleFilter?.value || '';
  const url = role ? `api/users.php?role=${encodeURIComponent(role)}` : 'api/users.php';

  try {
    const data = await fetchJson(url);
    const rows = data.users || [];

    if (!rows.length) {
      ui.usersBody.innerHTML = '<tr><td colspan="7" class="inline-status">No users found.</td></tr>';
      return;
    }

    ui.usersBody.innerHTML = rows.map((u) => {
      const img = normalizePublicPath(u.profile_image_path, 'uploads/profiles/default-avatar.svg');
      return `<tr><td><img class="tiny-avatar" src="${escapeHtml(img)}" alt="profile" /></td><td>${u.id}</td><td>${escapeHtml(u.full_name)}</td><td>${escapeHtml(u.username)}</td><td>${escapeHtml(u.role)}</td><td>${u.is_active === 1 || u.is_active === '1' ? 'active' : 'inactive'}</td><td><div class="action-inline"><input id="resetPasswordInput${u.id}" type="password" data-reset-input="${u.id}" placeholder="New password" /><button type="button" class="password-toggle-btn" aria-label="Toggle password visibility" title="Show or hide password" data-toggle-password="#resetPasswordInput${u.id}"></button><button type="button" data-reset-pass="${u.id}">Reset</button></div></td></tr>`;
    }).join('');

    if (ui.usersStatus) ui.usersStatus.textContent = `${rows.length} user(s) loaded.`;
  } catch (err) {
    ui.usersBody.innerHTML = `<tr><td colspan="7" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
    if (ui.usersStatus) ui.usersStatus.textContent = err.message;
  }
}

async function resetPassword(userId) {
  const input = document.querySelector(`[data-reset-input="${userId}"]`);
  if (!input || !ui.usersStatus) return;

  try {
    await fetchJson('api/reset_password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: Number(userId), new_password: input.value }),
    });

    input.value = '';
    ui.usersStatus.textContent = `Password reset successful for user ${userId}.`;
    await loadAuditLogs();
  } catch (err) {
    ui.usersStatus.textContent = err.message;
  }
}

async function changeMyPassword(event) {
  event.preventDefault();
  if (!ui.changePasswordForm || !ui.changePasswordStatus) return;

  const fd = new FormData(ui.changePasswordForm);
  const payload = {
    current_password: String(fd.get('current_password') || ''),
    new_password: String(fd.get('new_password') || ''),
  };

  ui.changePasswordStatus.textContent = 'Changing password...';
  try {
    await fetchJson('api/change_password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    ui.changePasswordStatus.textContent = 'Password changed successfully.';
    ui.changePasswordForm.reset();
  } catch (err) {
    ui.changePasswordStatus.textContent = err.message;
  }
}

async function loadAuditLogs() {
  if (!perms.canAdminMgmt || !ui.auditBody) return;

  ui.auditBody.innerHTML = '<tr><td colspan="6" class="inline-status">Loading logs...</td></tr>';
  try {
    const data = await fetchJson('api/audit_logs.php?limit=80');
    const rows = data.logs || [];

    if (!rows.length) {
      ui.auditBody.innerHTML = '<tr><td colspan="6" class="inline-status">No logs found.</td></tr>';
      return;
    }

    ui.auditBody.innerHTML = rows.map((r) => {
      const entity = `${r.entity_type}${r.entity_id ? ` #${r.entity_id}` : ''}`;
      let details = '-';
      if (r.details_json) {
        try {
          details = escapeHtml(JSON.stringify(JSON.parse(r.details_json)));
        } catch (_e) {
          details = escapeHtml(String(r.details_json));
        }
      }
      return `<tr><td>${r.id}</td><td>${escapeHtml(r.created_at)}</td><td>${escapeHtml(r.actor_name || r.actor_username || 'system')}</td><td>${escapeHtml(r.action)}</td><td>${escapeHtml(entity)}</td><td>${details}</td></tr>`;
    }).join('');
  } catch (err) {
    ui.auditBody.innerHTML = `<tr><td colspan="6" class="inline-status">${escapeHtml(err.message)}</td></tr>`;
  }
}

function bindEvents() {
  ui.tabButtons.forEach((btn) => btn.addEventListener('click', () => setTab(btn.dataset.tab)));
  ui.navJumps.forEach((btn) => btn.addEventListener('click', () => setTab(btn.dataset.tab)));
  ui.adminSubButtons.forEach((btn) => btn.addEventListener('click', () => setAdminSubTab(btn.dataset.adminTab)));
  ui.accountSubButtons.forEach((btn) => btn.addEventListener('click', () => setAccountSubTab(btn.dataset.accountTab)));

  ui.appointmentDate?.addEventListener('change', loadSlots);
  ui.location?.addEventListener('change', async () => {
    try {
      await loadBookingDepartments(ui.location?.value || '');
    } catch (_err) {
      bookingDepartments = [];
      if (ui.service) ui.service.innerHTML = '';
    }
    applyDoctorOptions();
    loadSlots();
  });
  ui.service?.addEventListener('change', () => {
    applyDoctorOptions();
    loadSlots();
  });
  ui.doctor?.addEventListener('change', loadSlots);
  ui.bookBtn?.addEventListener('click', bookAppointment);
  ui.askAi?.addEventListener('click', () => askAssistant(ui.aiMessage.value));
  ui.clearAiReply?.addEventListener('click', () => {
    renderAiReply('');
    if (ui.aiMessage) ui.aiMessage.value = '';
  });
  ui.aiHintButtons?.addEventListener('click', async (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const hint = target.dataset.aiHint;
    if (!hint) return;
    if (ui.aiMessage) ui.aiMessage.value = hint;
    await askAssistant(hint);
  });

  ui.refreshQueue?.addEventListener('click', loadQueue);
  ui.queueDate?.addEventListener('change', loadQueue);
  ui.queueLocation?.addEventListener('change', loadQueue);
  ui.queueList?.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const button = target.closest('[data-queue-id]');
    if (!(button instanceof HTMLElement)) return;
    selectedQueueId = Number(button.dataset.queueId || 0);
    renderQueueList();
  });

  ui.patientLookupForm?.addEventListener('submit', lookupPatientAppointments);
  ui.patientSelfServiceBody?.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const button = target.closest('[data-patient-action]');
    if (!(button instanceof HTMLElement)) return;
    runPatientSelfServiceAction(button.dataset.appointmentId || '', button.dataset.patientAction || '');
  });

  ui.refreshAdmin?.addEventListener('click', loadAdmin);
  ui.adminDate?.addEventListener('change', loadAdmin);
  ui.doctorMonth?.addEventListener('change', loadAdmin);
  ui.adminLocation?.addEventListener('change', loadAdmin);
  ui.adminStatus?.addEventListener('change', loadAdmin);

  ui.adminBody?.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    if (target.dataset.saveStatus) updateStatus(target.dataset.saveStatus);
    if (target.dataset.reschedule) rescheduleAppointment(target.dataset.reschedule);
  });

  ui.doctorCalendar?.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const dayEl = target.closest('[data-doctor-day]');
    if (!(dayEl instanceof HTMLElement)) return;
    const date = String(dayEl.dataset.doctorDay || '');
    if (!date) return;
    const dayRows = doctorMonthRows.filter((row) => String(row.appointment_date || '') === date);
    openDoctorDayModal(date, dayRows);
  });

  ui.doctorDayModal?.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    if (target.dataset.dayModalClose === 'backdrop') {
      closeDoctorDayModal();
      return;
    }
    if (target.dataset.saveStatus) {
      updateStatus(target.dataset.saveStatus);
    }
  });
  ui.doctorDayModalCloseBtn?.addEventListener('click', closeDoctorDayModal);

  ui.adminBody?.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLSelectElement)) return;
    if (!target.dataset.rLocation) return;

    const appointmentId = target.dataset.rLocation;
    const serviceName = target.dataset.rService || '';
    const doctorSelect = document.querySelector(`[data-r-doctor="${appointmentId}"]`);
    if (!(doctorSelect instanceof HTMLSelectElement)) return;

    const currentDoctor = doctorSelect.value;
    doctorSelect.innerHTML = renderDoctorOptions(currentDoctor, target.value, false, serviceName);
    if (!doctorSelect.value) {
      const fallback = doctorsForLocation(target.value, serviceName)[0];
      if (fallback) doctorSelect.value = fallback.full_name;
    }
  });

  ui.refreshInsights?.addEventListener('click', loadInsights);
  ui.insightsDate?.addEventListener('change', loadInsights);

  ui.createUserForm?.addEventListener('submit', createUser);
  ui.createUserForm?.querySelector('[name="role"]')?.addEventListener('change', toggleCreateUserLocationField);
  ui.createLocationForm?.addEventListener('submit', createLocation);
  ui.createDepartmentForm?.addEventListener('submit', createDepartment);
  ui.departmentBranchLocation?.addEventListener('change', syncDepartmentBranchSelection);
  ui.departmentBranchForm?.addEventListener('submit', saveDepartmentBranchMapping);
  ui.doctorDepartmentsDoctor?.addEventListener('change', syncDoctorDepartmentsSelection);
  ui.doctorDepartmentsForm?.addEventListener('submit', saveDoctorDepartments);
  ui.doctorLocationDoctor?.addEventListener('change', syncDoctorLocationFormSelection);
  ui.doctorLocationsForm?.addEventListener('submit', saveDoctorLocations);
  ui.doctorAvailabilityForm?.addEventListener('submit', addAvailability);
  ui.doctorAvailabilityBody?.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const removeButton = target.closest('[data-del-availability]');
    if (!(removeButton instanceof HTMLElement)) return;
    const availabilityId = Number(removeButton.dataset.delAvailability || 0);
    if (availabilityId > 0) {
      deleteAvailability(availabilityId);
    }
  });
  ui.refreshUsers?.addEventListener('click', loadUsers);
  ui.userRoleFilter?.addEventListener('change', loadUsers);

  ui.usersBody?.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    if (target.dataset.resetPass) resetPassword(target.dataset.resetPass);
  });

  ui.refreshAudit?.addEventListener('click', loadAuditLogs);
  ui.changePasswordForm?.addEventListener('submit', changeMyPassword);
  ui.updateProfileNameForm?.addEventListener('submit', updateMyProfileName);
  ui.updateProfileImageForm?.addEventListener('submit', updateMyProfileImage);
  ui.updateProfileImageInput?.addEventListener('change', previewAccountProfileImage);

  document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    if (!target.classList.contains('password-toggle-btn')) return;
    togglePasswordBySelector(target.dataset.togglePassword || '', target);
  });
}

async function bootstrap() {
  if (appRole === 'doctor' && ui.queueLocation) {
    ui.queueLocation.value = '';
    ui.queueLocation.disabled = true;
  }

  bindEvents();
  renderAiHintButtons();
  try {
    await loadMasters();
  } catch (_err) {
    locations = [];
    departments = [];
    populateMasterSelects();
  }
  initSelect2AdminDesk();
  if (ui.location) {
    try {
      await loadBookingDepartments(ui.location.value || '');
    } catch (_err) {
      bookingDepartments = [];
      if (ui.service) ui.service.innerHTML = '';
    }
  }
  await loadDoctors();
  toggleCreateUserLocationField();

  const loaders = [loadRoleDashboard(), loadQueue()];
  if (perms.canBook) loaders.push(loadSlots());
  if (perms.canAdminDesk) loaders.push(loadAdmin());
  if (perms.canSelfService) renderPatientSelfServiceRows([]);
  if (perms.canInsights) loaders.push(loadInsights());
  if (perms.canAdminMgmt) loaders.push(loadUsers(), loadAuditLogs(), loadLocationsTable(), loadDepartmentsTable(), loadDoctorLocationDirectory(), loadDoctorDepartmentsDirectory(), loadDepartmentLocationDirectory());
  if (appRole === 'doctor') loaders.push(loadMyAvailability());

  await Promise.all(loaders);
}

bootstrap();
