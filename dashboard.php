<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrollment Tracker Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<link rel="stylesheet" href="/enrollment-tracker/assets/css/style.css">
</head>

<body>

<?php if (!isset($_GET['login'])): ?>

<div id="loginForm">
    <h2>🎓 Enrollment Tracker</h2>
    <p>CAS Department Management System</p>
    <form id="login">
        <input type="password" id="password" placeholder="Enter admin password" required autofocus>
        <button type="submit">Login to Dashboard</button>
    </form>
</div>

<?php else: ?>

<div class="container" id="dashboard" style="display:none;">

    <!-- Mobile menu button (small screens) -->
    <div class="dashboard-topbar">
        <button id="mobileNavToggle" class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false">
            <span class="icon">☰</span>
            <span class="label">Menu</span>
        </button>
    </div>

    <div id="navOverlay" class="nav-overlay"></div>

    <div class="dashboard-layout">
        <!-- SIDEBAR -->
        <aside id="sideNav" class="card side-nav">
            <div class="side-nav-header">
                <h3>Dashboard Menu</h3>
                <button id="desktopNavToggle" class="side-nav-toggle" type="button" aria-label="Collapse sidebar" aria-expanded="true">◀</button>
            </div>

            <div class="tabs">
                <button class="tab-btn active" data-tab="overview"><span class="tab-icon">📊</span><span class="tab-text">Overview</span></button>
                <button class="tab-btn" data-tab="enrollments"><span class="tab-icon">👥</span><span class="tab-text">Enrollments</span></button>
                <button class="tab-btn" data-tab="add-enrollment"><span class="tab-icon">➕</span><span class="tab-text">Add Enrollment</span></button>
                <button class="tab-btn" data-tab="predictions"><span class="tab-icon">🔮</span><span class="tab-text">Predictions</span></button>
                <button class="tab-btn" data-tab="evaluation"><span class="tab-icon">📈</span><span class="tab-text">Evaluation</span></button>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-panel">
            <!-- TOP NAVBAR (MAIN ONLY) -->
            <div class="main-topbar">
                <div class="left">
                    <button id="brandToggle" class="brand-toggle" type="button" aria-label="Toggle sidebar">
                        <span class="brand-logo">ET</span>
                        <span class="brand-name">Enrollment Tracker</span>
                    </button>

                    <button id="topSidebarToggle" class="icon-btn" type="button" aria-label="Toggle sidebar">☰</button>
                </div>

                <div class="profile-area">
                    <span id="profileName" class="profile-name">Admin</span>
                    <span id="profileAvatar" class="profile-avatar">A</span>
                </div>
            </div>

            <!-- HERO BANNER (PART OF MAIN CONTENT) -->
            <div id="headerBanner" class="hero-banner">
                <h1 id="greetingText">Good morning, Admin</h1>
                <p id="todayText">Today is —</p>
                <p id="termText">Second Semester, S.Y 2025 - 2026</p>
            </div>

            <div id="overview" class="tab-content active">
                <div class="summary-grid" id="summaryGrid"></div>

                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
                        <h2>📊 All Programs Combined Enrollment Trend</h2>
                        <div class="select-container" style="margin:0;">
                            <select id="overviewYearFilter" style="flex:1;max-width:180px;">
                                <option value="">All Years</option>
                            </select>
                            <select id="overviewYearStart" style="flex:1;max-width:180px;">
                                <option value="">Start Year</option>
                            </select>
                            <select id="overviewYearEnd" style="flex:1;max-width:180px;">
                                <option value="">End Year</option>
                            </select>
                            <select id="overviewSemesterFilter" style="flex:1;max-width:160px;">
                                <option value="">All Semesters</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                                <option value="12">Semester 1 & 2</option>
                                <option value="3">Semester 3</option>
                            </select>
                            <button onclick="tracker.refreshCombinedChart()" style="margin:0;">🔄 Refresh</button>
                        </div>
                    </div>
                    <div class="chart-container" style="height:400px;">
                        <canvas id="combinedChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                        <h2>📈 Enrollment Trends by Program</h2>
                        <button onclick="tracker.loadAllProgramsCharts()" style="margin:0;">🔄 Refresh</button>
                    </div>
                    <div id="status" class="status"></div>
                    <div class="programs-charts-grid" id="programsChartsGrid">
                        <div class="text-center" style="padding:40px;grid-column:1/-1;">Loading charts...</div>
                    </div>
                </div>
            </div>

            <div id="enrollments" class="tab-content">
                <div class="card">
                    <h2>📋 Enrollment Records</h2>
                    
                    <div class="select-container">
                        <select id="enrollProgramFilter">
                            <option value="">All Programs</option>
                        </select>
                        <select id="enrollYearFilter">
                            <option value="">All Years</option>
                        </select>
                        <button onclick="tracker.refreshEnrollmentsTable()">🔄 Refresh</button>
                        <button id="logoutBtn" style="background:#e53e3e;margin-left:auto;">🚪 Logout</button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="program">Program <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="academic_year">Academic Year <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="semester">Semester <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="male">Male <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="female">Female <span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="total">Total <span class="sort-indicator"></span></th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="enrollmentsTable">
                                <tr><td colspan="7" class="text-center">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="add-enrollment" class="tab-content">
                <div class="card">
                    <h2>➕ Add New Enrollment Record</h2>
                    
                    <form id="addEnrollmentForm" onsubmit="tracker.handleAddEnrollment(event)">
                        <div class="form-group">
                            <label>Program <span style="color:red">*</span></label>
                            <select id="formProgram" required style="width:100%;" onchange="tracker.updateAvailableYears()">
                                <option value="">Select a program</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Academic Year <span style="color:red">*</span></label>
                                <select id="formYear" required style="width:100%;">
                                    <option value="">Select academic year</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Semester <span style="color:red">*</span></label>
                                <select id="formSemester" required style="width:100%;">
                                    <option value="">Select semester</option>
                                    <option value="1">First</option>
                                    <option value="2">Second</option>
                                    <option value="3">Summer</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Male Students <span style="color:red">*</span></label>
                                <input type="number" id="formMale" min="0" required style="width:100%;">
                            </div>
                            <div class="form-group">
                                <label>Female Students <span style="color:red">*</span></label>
                                <input type="number" id="formFemale" min="0" required style="width:100%;">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-success">✅ Add Enrollment</button>
                            <button type="reset">🔄 Clear</button>
                        </div>
                    </form>

                    <div id="addStatus" class="status"></div>
                </div>

                <div class="card">
                    <h2>📝 Recently Added</h2>
                    <div id="recentList"></div>
                </div>
            </div>

            <div id="predictions" class="tab-content">
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
                        <h2>📊 All Programs Combined Enrollment Trend</h2>
                        <div class="select-container" style="margin:0;">
                            <select id="predSemesterFilter" style="flex:1;max-width:160px;">
                                <option value="">All Semesters</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                                <option value="12">Semester 1 &amp; 2</option>
                                <option value="3">Semester 3</option>
                            </select>
                            <select id="predModelFilter" style="flex:1;max-width:160px;">
                                <option value="Ensemble">Ensemble</option>
                                <option value="Prophet">Prophet</option>
                                <option value="LSTM">LSTM</option>
                                <option value="XGBoost">XGBoost</option>
                            </select>
                            <button onclick="tracker.refreshPredCombinedChart()" style="margin:0;">🔄 Refresh</button>
                        </div>
                    </div>
                    <div class="chart-container" style="height:400px;">
                        <canvas id="predCombinedChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
                        <h2>📈 Enrollment Trends by Program</h2>
                        <div class="select-container" style="margin:0;">
                            <select id="predProgModelFilter" style="flex:1;max-width:160px;">
                                <option value="Ensemble">Ensemble</option>
                                <option value="Prophet">Prophet</option>
                                <option value="LSTM">LSTM</option>
                                <option value="XGBoost">XGBoost</option>
                            </select>
                            <button onclick="tracker.refreshPredByProgramCharts()" style="margin:0;">🔄 Refresh</button>
                        </div>
                    </div>
                    <div class="programs-charts-grid" id="predProgramsChartsGrid">
                        <div class="text-center" style="padding:40px;grid-column:1/-1;color:#718096;">Loading charts...</div>
                    </div>
                </div>

                <div class="card">
                    <h2>🔮 Per-Program Prediction Detail</h2>
                    <div class="select-container">
                        <label style="font-weight:700;">Select Program:</label>
                        <select id="predProgramFilter" style="flex:1;max-width:400px;">
                            <option value="">Choose a program</option>
                        </select>
                        <button onclick="tracker.refreshPredictions()">🔄 Load Detail</button>
                    </div>

                    <div id="predictionChartContainer" class="card" style="margin-top:20px;display:none;">
                        <h3 id="predictionChartTitle"></h3>
                        <div id="predictionLegend" class="custom-legend" style="display:none;"></div>
                        <div id="availableModels" class="available-models" style="display:none;"></div>
                        <div class="chart-container">
                            <canvas id="predictionChart"></canvas>
                        </div>
                    </div>

                    <div id="predictionCompareContainer" class="card" style="margin-top:20px;display:none;">
                        <h3>📋 Prediction Comparison by Algorithm</h3>
                        <div class="prediction-table-wrap">
                            <table class="prediction-compare-table">
                                <thead>
                                    <tr>
                                        <th>Period</th>
                                        <th style="color:#D81B60;">Prophet</th>
                                        <th style="color:#FF6F00;">LSTM</th>
                                        <th style="color:#6D4C41;">XGBoost</th>
                                        <th style="color:#00897B;">Ensemble</th>
                                    </tr>
                                </thead>
                                <tbody id="predictionCompareTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="predictionStatsContainer" style="margin-top:20px;display:none;">
                        <div class="predictions-grid" id="predictionsGrid"></div>
                    </div>
                </div>
            </div>

            <div id="evaluation" class="tab-content">
                <div class="card">
                    <h2>📊 Model Evaluation Metrics</h2>
                    <div class="select-container">
                        <label>Select Program:</label>
                        <select id="evaluationProgramFilter" style="flex:1;max-width:400px;">
                            <option value="">Choose a program</option>
                        </select>
                        <button onclick="tracker.loadEvaluationMetrics()">🔄 Load Metrics</button>
                    </div>

                    <div id="comparisonContainer" style="display:none;">
                        <h3 style="margin:30px 0 15px 0;">Common Metrics</h3>
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th style="color:#f093fb;">Prophet</th>
                                    <th style="color:#ed8936;">LSTM</th>
                                    <th style="color:#6D4C41;">XGBoost</th>
                                    <th style="color:#2f855a;">Best Model</th>
                                </tr>
                            </thead>
                            <tbody id="commonMetricsTable"></tbody>
                        </table>

                        <h3 style="margin:30px 0 15px 0;">Detailed Model Metrics</h3>
                        <div class="metrics-container" id="modelCardsContainer"></div>
                    </div>
                </div>

                <div class="card">
                    <h2>📘 Metrics Guide</h2>
                    <div class="metric-section" style="border-left-color:#667eea;">
                        <h3>Common Metrics</h3>
                        <div class="metric-description">
                            <strong>MAE</strong> = Mean Absolute Error, lower is better.<br>
                            <strong>RMSE</strong> = Root Mean Squared Error, lower is better.<br>
                            <strong>MAPE</strong> = Mean Absolute Percentage Error, lower is better.<br>
                            <strong>R²</strong> = Coefficient of Determination, higher is better.<br>
                            <strong>RMSLE</strong> = Root Mean Squared Log Error, lower is better.<br>
                            <strong>Theil_U</strong> = compares against a naive forecast, lower is better.
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

</div>

<?php endif; ?>

<div id="editModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.4);">
    <div style="background-color:#fefefe;margin:10% auto;padding:30px;border:1px solid #888;border-radius:18px;width:90%;max-width:500px;box-shadow:0 8px 25px rgba(0,0,0,0.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2>✏️ Edit Enrollment</h2>
            <span onclick="tracker.closeEditModal()" style="font-size:28px;font-weight:bold;color:#aaa;cursor:pointer;">&times;</span>
        </div>
        
        <form id="editEnrollmentForm" onsubmit="tracker.handleEditEnrollment(event)">
            <div class="form-group">
                <label>Program</label>
                <input type="text" id="editProgramName" disabled style="width:100%;background:#f0f0f0;">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Academic Year</label>
                    <input type="text" id="editYear" disabled style="width:100%;background:#f0f0f0;">
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <input type="text" id="editSemester" disabled style="width:100%;background:#f0f0f0;">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Male Students</label>
                    <input type="number" id="editMale" min="0" required style="width:100%;">
                </div>
                <div class="form-group">
                    <label>Female Students</label>
                    <input type="number" id="editFemale" min="0" required style="width:100%;">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-success">✅ Save Changes</button>
                <button type="button" onclick="tracker.closeEditModal()">Cancel</button>
            </div>
        </form>

        <div id="editStatus" class="status"></div>
    </div>
</div>

<script>
const programNames = {
    1: 'BA Communication',
    2: 'BA English',
    3: 'BA Political Science',
    4: 'BLIS',
    5: 'BM Music Education',
    6: 'BS Biology',
    7: 'BS Information Technology',
    8: 'BS Social Work'
};

class EnrollmentTracker{
    constructor(){
        this.charts = {};
        this.predictionChart = null;
        this.combinedChart = null;
        this.predCombinedChart = null;
        this.predProgramCharts = {};
        this.allEnrollments = [];
        this.allPrograms = [];
        this.allPredictions = [];
        this.allMetrics = [];
        this.editingRecord = null;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.navCollapsed = false;

        // TODO later: replace with session-based name when login is implemented
        this.userName = 'Admin';

        this.init();
    }

    init(){
<?php if (isset($_GET['login'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('dashboard').style.display='block';

            // NEW: top navbar + greeting banner text
            this.setupTopNavbar();
            this.updateGreetingHeader();
            setInterval(() => this.updateGreetingHeader(), 60000);

            // existing
            this.startBackgroundScheduler();
            this.setupTabs();
            this.setupSideNav();
            this.loadPrograms();
            this.bindEvents();
            this.setupEnrollmentsSorting();
        });
<?php endif; ?>
    }

    // ========== NEW: Top navbar ==========
    setupTopNavbar(){
        const brandToggle = document.getElementById('brandToggle');
        const topSidebarToggle = document.getElementById('topSidebarToggle');

        const profileName = document.getElementById('profileName');
        const profileAvatar = document.getElementById('profileAvatar');

        if(profileName) profileName.textContent = this.userName;
        if(profileAvatar) profileAvatar.textContent = (this.userName || 'U').trim().charAt(0).toUpperCase();

        const doToggle = () => {
            // desktop collapse
            if(this.layoutEl && !window.matchMedia('(max-width: 768px)').matches){
                this.toggleDesktopNav();
                return;
            }
            // mobile open/close
            this.toggleMobileNav();
        };

        brandToggle?.addEventListener('click', doToggle);
        topSidebarToggle?.addEventListener('click', doToggle);
    }

    updateGreetingHeader(){
        const now = new Date();
        const h = now.getHours();

        let greeting = 'Good morning';
        if(h >= 12 && h < 18) greeting = 'Good afternoon';
        if(h >= 18) greeting = 'Good evening';

        const longDate = now.toLocaleDateString('en-US', {
            weekday:'long',
            year:'numeric',
            month:'long',
            day:'numeric'
        });

        const greetingEl = document.getElementById('greetingText');
        const todayEl = document.getElementById('todayText');
        const termEl = document.getElementById('termText');

        if(greetingEl) greetingEl.textContent = `${greeting}, ${this.userName}`;
        if(todayEl) todayEl.textContent = `Today is ${longDate}`;
        if(termEl) termEl.textContent = `Second Semester, S.Y 2025 - 2026`;
    }

    setupTabs(){
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.getAttribute('data-tab');
                
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                btn.classList.add('active');
                document.getElementById(tabId).classList.add('active');

                if(tabId === 'enrollments'){
                    this.refreshEnrollmentsTable();
                }else if(tabId === 'predictions'){
                    document.getElementById('predictionChartContainer').style.display = 'none';
                    document.getElementById('predictionStatsContainer').style.display = 'none';
                    document.getElementById('predictionCompareContainer').style.display = 'none';
                    this.refreshPredCombinedChart();
                    this.refreshPredByProgramCharts();
                }else if(tabId === 'overview'){
                    this.loadAllProgramsCharts();
                    this.refreshCombinedChart();
                }

                if(window.matchMedia('(max-width: 768px)').matches){
                    this.closeMobileNav();
                }
            });
        });
    }

    setupSideNav(){
        this.layoutEl = document.querySelector('.dashboard-layout');
        this.sideNavEl = document.getElementById('sideNav');
        this.navOverlayEl = document.getElementById('navOverlay');
        this.desktopNavToggleEl = document.getElementById('desktopNavToggle');
        this.mobileNavToggleEl = document.getElementById('mobileNavToggle');

        this.desktopNavToggleEl?.addEventListener('click', () => this.toggleDesktopNav());
        this.mobileNavToggleEl?.addEventListener('click', () => this.toggleMobileNav());
        this.navOverlayEl?.addEventListener('click', () => this.closeMobileNav());

        window.addEventListener('resize', () => this.handleNavResize());
        this.handleNavResize();
    }

    toggleDesktopNav(){
        if(!this.layoutEl || window.matchMedia('(max-width: 768px)').matches) return;

        this.navCollapsed = !this.navCollapsed;
        this.layoutEl.classList.toggle('collapsed', this.navCollapsed);

        if(this.desktopNavToggleEl){
            this.desktopNavToggleEl.textContent = this.navCollapsed ? '▶' : '◀';
            this.desktopNavToggleEl.setAttribute('aria-expanded', String(!this.navCollapsed));
            this.desktopNavToggleEl.setAttribute('aria-label', this.navCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }
    }

    openMobileNav(){
        if(!this.sideNavEl || !this.navOverlayEl) return;
        this.sideNavEl.classList.add('open');
        this.navOverlayEl.classList.add('active');
        this.mobileNavToggleEl?.setAttribute('aria-expanded', 'true');
    }

    closeMobileNav(){
        if(!this.sideNavEl || !this.navOverlayEl) return;
        this.sideNavEl.classList.remove('open');
        this.navOverlayEl.classList.remove('active');
        this.mobileNavToggleEl?.setAttribute('aria-expanded', 'false');
    }

    toggleMobileNav(){
        if(!this.sideNavEl) return;
        if(this.sideNavEl.classList.contains('open')){
            this.closeMobileNav();
        }else{
            this.openMobileNav();
        }
    }

    handleNavResize(){
        if(!this.layoutEl || !this.sideNavEl || !this.navOverlayEl) return;

        if(window.matchMedia('(max-width: 768px)').matches){
            this.layoutEl.classList.remove('collapsed');
            this.sideNavEl.classList.remove('open');
            this.navOverlayEl.classList.remove('active');
        }else{
            this.sideNavEl.classList.remove('open');
            this.navOverlayEl.classList.remove('active');
            this.layoutEl.classList.toggle('collapsed', this.navCollapsed);
        }
    }

    applyTimeBasedBackground(){
        const now = new Date();
        const minutes = now.getHours() * 60 + now.getMinutes();
        const headerBanner = document.getElementById('headerBanner');

        if(!headerBanner) return;

        headerBanner.classList.remove('bg-day', 'bg-afternoon', 'bg-night');

        // 5:01 AM to 12:59 PM
        if(minutes >= 301 && minutes <= 779){
            headerBanner.classList.add('bg-day');
            return;
        }

        // 1:00 PM to 5:00 PM
        if(minutes >= 780 && minutes <= 1020){
            headerBanner.classList.add('bg-afternoon');
            return;
        }

        // 5:01 PM to 5:00 AM
        headerBanner.classList.add('bg-night');
    }

    startBackgroundScheduler(){
        this.applyTimeBasedBackground();
        this.backgroundTimer = setInterval(() => this.applyTimeBasedBackground(), 30000);
    }

    showStatus(msg,type='success',elementId='status'){
        const status=document.getElementById(elementId);
        if(!status) return;
        status.textContent=msg;
        status.className=`status ${type}`;
        status.style.display='block';
        setTimeout(()=>status.style.display='none',5000);
    }

    async loadPrograms(){
        try{
            const res=await fetch('api/programs.php');
            if(!res.ok) throw new Error(`HTTP ${res.status}`);

            this.allPrograms=await res.json();

            const selects=['formProgram','enrollProgramFilter','predProgramFilter','evaluationProgramFilter'];
            selects.forEach(selectId => {
                const select=document.getElementById(selectId);
                if(select){
                    const firstOption = selectId === 'formProgram'
                        ? '<option value="">Select a program</option>'
                        : '<option value="">All Programs</option>';

                    const html = firstOption +
                        this.allPrograms.map(p=>`<option value="${p.id}">${p.name}</option>`).join('');
                    select.innerHTML=html;
                }
            });

            const evalSelect = document.getElementById('evaluationProgramFilter');
            if (evalSelect) {
                evalSelect.innerHTML = '<option value="">Choose a program</option>' +
                    this.allPrograms.map(p=>`<option value="${p.id}">${p.name}</option>`).join('');
            }

            await this.loadYears();
            await this.loadPredictionsData();
            this.loadAllProgramsCharts();
            this.refreshCombinedChart();
            this.refreshPredCombinedChart();
            this.refreshPredByProgramCharts();

        }catch(e){
            this.showStatus('Failed to load programs: '+e.message,'error');
        }
    }

    async loadPredictionsData(){
        try{
            const res = await fetch('api/predictions.php');
            const json = await res.json();
            this.allPredictions = Array.isArray(json) ? json : (json.data || []);
        }catch(e){
            console.error('Failed to load predictions', e);
            this.allPredictions = [];
        }
    }

    async loadYears(){
        try{
            const res=await fetch('api/enrollments.php');
            if(!res.ok) throw new Error(`HTTP ${res.status}`);

            this.allEnrollments=await res.json();
            
            const validData = this.allEnrollments.filter(e => {
                const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1;
            });
            
            const years = [...new Set(validData.map(e => e.academic_year))].sort().reverse();

            const enrollYearFilter = document.getElementById('enrollYearFilter');
            const yearHtml = years.map(y => `<option value="${y}">${y}</option>`).join('');
            
            if(enrollYearFilter) enrollYearFilter.innerHTML = '<option value="">All Years</option>' + yearHtml;

            const overviewYearFilter = document.getElementById('overviewYearFilter');
            if(overviewYearFilter) overviewYearFilter.innerHTML = '<option value="">All Years</option>' + yearHtml;

            const overviewYearStart = document.getElementById('overviewYearStart');
            const overviewYearEnd = document.getElementById('overviewYearEnd');
            if(overviewYearStart) overviewYearStart.innerHTML = '<option value="">Start Year</option>' + yearHtml;
            if(overviewYearEnd) overviewYearEnd.innerHTML = '<option value="">End Year</option>' + yearHtml;

            if(overviewYearEnd && years.length > 0) {
                overviewYearEnd.value = years[0];
            }

        }catch(e){
            console.error('Failed to load years:', e);
        }
    }

    bindEvents(){
        document.getElementById('enrollProgramFilter')?.addEventListener('change',()=>this.refreshEnrollmentsTable());
        document.getElementById('enrollYearFilter')?.addEventListener('change',()=>this.refreshEnrollmentsTable());
        document.getElementById('overviewYearFilter')?.addEventListener('change',()=>this.refreshCombinedChart());
        document.getElementById('overviewYearStart')?.addEventListener('change',()=>this.refreshCombinedChart());
        document.getElementById('overviewYearEnd')?.addEventListener('change',()=>this.refreshCombinedChart());
        document.getElementById('overviewSemesterFilter')?.addEventListener('change',()=>this.refreshCombinedChart());

        document.getElementById('predProgramFilter')?.addEventListener('change',()=>{
            document.getElementById('predictionChartContainer').style.display = 'none';
            document.getElementById('predictionStatsContainer').style.display = 'none';
            document.getElementById('predictionCompareContainer').style.display = 'none';
        });

        document.getElementById('logoutBtn')?.addEventListener('click',()=>{
            if(confirm('Are you sure you want to logout?')){
                window.location.href = 'api/logout.php';
            }
        });

        window.addEventListener('click', (e) => {
            const modal = document.getElementById('editModal');
            if(e.target === modal){
                this.closeEditModal();
            }
        });
    }

    setupEnrollmentsSorting(){
        const headers = document.querySelectorAll('#enrollments th.sortable');
        headers.forEach(th => {
            th.addEventListener('click', () => {
                const col = th.getAttribute('data-sort');
                if(this.sortColumn === col){
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortColumn = col;
                    this.sortDirection = 'asc';
                }
                this.updateSortIndicators();
                this.refreshEnrollmentsTable();
            });
        });
    }

    updateSortIndicators(){
        const headers = document.querySelectorAll('#enrollments th.sortable');
        headers.forEach(th=>{
            const col = th.getAttribute('data-sort');
            const indicator = th.querySelector('.sort-indicator');
            if(!indicator) return;
            if(this.sortColumn === col){
                indicator.textContent = this.sortDirection === 'asc' ? '▲' : '▼';
            } else {
                indicator.textContent = '';
            }
        });
    }

    updateAvailableYears(){
        const programId = document.getElementById('formProgram').value;
        const yearSelect = document.getElementById('formYear');

        if(!programId){
            yearSelect.innerHTML = '<option value="">Select academic year</option>';
            return;
        }

        const existingYears = this.allEnrollments
            .filter(e => e.program_id == programId)
            .filter(e => {
                const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1;
            })
            .map(e => e.academic_year);

        const uniqueExistingYears = new Set(existingYears);
        const currentYear = new Date().getFullYear();
        const availableYears = [];
        
        for(let i = currentYear; i < currentYear + 5; i++){
            const yearRange = `${i}-${i+1}`;
            if(!uniqueExistingYears.has(yearRange)){
                availableYears.push(yearRange);
            }
        }

        if(availableYears.length === 0){
            yearSelect.innerHTML = '<option value="">No available years for this program</option>';
            return;
        }

        yearSelect.innerHTML = '<option value="">Select academic year</option>' +
            availableYears.map(y => `<option value="${y}">${y}</option>`).join('');
    }

    async refreshCombinedChart(){
        try{
            const yearFilter = document.getElementById('overviewYearFilter').value;
            const semFilter = document.getElementById('overviewSemesterFilter').value;

            let filtered = [...this.allEnrollments].filter(e => {
                const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1;
            });

            if(yearFilter){
                filtered = filtered.filter(e => e.academic_year === yearFilter);
            }

            const startFilter = document.getElementById('overviewYearStart').value;
            const endFilter = document.getElementById('overviewYearEnd').value;

            if(startFilter){
                const startYear = parseInt(startFilter.split('-')[0]);
                filtered = filtered.filter(e => parseInt(String(e.academic_year).split('-')[0]) >= startYear);
            }
            if(endFilter){
                const endYear = parseInt(endFilter.split('-')[0]);
                filtered = filtered.filter(e => parseInt(String(e.academic_year).split('-')[0]) <= endYear);
            }

            if(semFilter){
                if(['1','2','3'].includes(semFilter)){
                    filtered = filtered.filter(e => parseInt(e.semester) === parseInt(semFilter));
                }else if(semFilter === '12'){
                    filtered = filtered.filter(e => [1,2].includes(parseInt(e.semester)));
                }
            }

            const aggregate = {};
            filtered.forEach(e => {
                let key = e.academic_year;
                if(!semFilter || semFilter === '12'){
                    key = `${e.academic_year} S${e.semester}`;
                }

                if(!aggregate[key]){
                    aggregate[key] = { total:0, male:0, female:0 };
                }
                aggregate[key].male += parseInt(e.male)||0;
                aggregate[key].female += parseInt(e.female)||0;
                aggregate[key].total += (parseInt(e.male)||0) + (parseInt(e.female)||0);
            });

            const chartData = Object.keys(aggregate).map(key => {
                let academic_year = key;
                let semester = null;
                if(key.includes(' S')){
                    const parts = key.split(' S');
                    academic_year = parts[0];
                    semester = parseInt(parts[1]);
                }
                return { academic_year, semester, ...aggregate[key] };
            });

            this.createCombinedChart(chartData);
        }catch(e){
            this.showStatus('Error loading combined chart: '+e.message,'error');
        }
    }

    createCombinedChart(chartData){
        const container = document.getElementById('combinedChart').closest('.chart-container');
        if(chartData.length === 0){
            if(container) container.style.display = 'none';
            return;
        }
        if(container) container.style.display = 'block';

        chartData.sort((a,b)=>{
            const yearA=parseInt(a.academic_year.split('-')[0]);
            const yearB=parseInt(b.academic_year.split('-')[0]);
            if(yearA !== yearB) return yearA - yearB;
            if(a.semester != null && b.semester != null){
                return a.semester - b.semester;
            }
            return 0;
        });

        const labels = chartData.map(e => e.semester != null ? `${e.academic_year} S${e.semester}` : e.academic_year);
        const totals = chartData.map(e=>e.total||0);
        const males = chartData.map(e=>e.male||0);
        const females = chartData.map(e=>e.female||0);

        const ctx = document.getElementById('combinedChart');
        if(!ctx) return;

        if(this.combinedChart){
            this.combinedChart.destroy();
        }

        this.combinedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total Enrollment',
                        data: totals,
                        borderColor: '#ed8936',
                        backgroundColor: 'rgba(237, 137, 54, 0.15)',
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: '#ed8936'
                    },
                    {
                        label: 'Male Students',
                        data: males,
                        borderColor: '#2b6cb0',
                        backgroundColor: 'rgba(43, 108, 176, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#2b6cb0'
                    },
                    {
                        label: 'Female Students',
                        data: females,
                        borderColor: '#d53f8c',
                        backgroundColor: 'rgba(213, 63, 140, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#d53f8c'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: true,
                        font: {weight: 'bold', size: 11},
                        color: '#2d3748',
                        backgroundColor: 'rgba(255,255,255,0.95)',
                        borderRadius: 4,
                        padding: 4,
                        anchor: 'end',
                        align: 'top'
                    },
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value.toLocaleString()
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    async loadAllProgramsCharts(){
        try{
            this.showStatus('Loading all programs charts...');

            const allData = this.allEnrollments;
            this.renderSummary(allData);

            const grid = document.getElementById('programsChartsGrid');
            grid.innerHTML = '';

            for(let programId of Object.keys(programNames).map(Number)){
                const programData = allData.filter(e => {
                    const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
                    return (endYear - startYear) === 1 && e.program_id == programId;
                });

                if(programData.length === 0) continue;

                const card = document.createElement('div');
                card.className = 'program-chart-card';
                
                const total = programData.reduce((sum,e) => sum + (parseInt(e.male)||0) + (parseInt(e.female)||0), 0);
                const totalMale = programData.reduce((sum,e) => sum + (parseInt(e.male)||0), 0);
                const totalFemale = programData.reduce((sum,e) => sum + (parseInt(e.female)||0), 0);

                card.innerHTML = `
                    <h3>${programNames[programId]}</h3>
                    <div class="program-stats">
                        <div class="stat-item">Total: <strong>${total}</strong></div>
                        <div class="stat-item">Male: <strong>${totalMale}</strong></div>
                        <div class="stat-item">Female: <strong>${totalFemale}</strong></div>
                    </div>
                    <div class="chart-container">
                        <canvas id="chart-prog-${programId}"></canvas>
                    </div>
                `;

                grid.appendChild(card);

                setTimeout(() => {
                    this.createProgramChart(programId, programData);
                }, 0);
            }

            this.showStatus('All programs charts loaded');

        }catch(e){
            this.showStatus('Error loading charts: '+e.message,'error');
        }
    }

    createProgramChart(programId, programData){
        programData.sort((a,b)=>{
            const yearA=parseInt(String(a.academic_year).split('-')[0]);
            const yearB=parseInt(String(b.academic_year).split('-')[0]);
            return yearA-yearB||parseInt(a.semester)-parseInt(b.semester);
        });

        const labels = programData.map(e=>`${e.academic_year} S${e.semester}`);
        const totals = programData.map(e=>(parseInt(e.male)||0)+(parseInt(e.female)||0));
        const males = programData.map(e=>parseInt(e.male)||0);
        const females = programData.map(e=>parseInt(e.female)||0);

        const ctx = document.getElementById(`chart-prog-${programId}`);
        if(!ctx) return;

        if(this.charts[programId]){
            this.charts[programId].destroy();
        }

        this.charts[programId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total',
                        data: totals,
                        borderColor: '#ed8936',
                        backgroundColor: 'rgba(237, 137, 54, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#ed8936'
                    },
                    {
                        label: 'Male',
                        data: males,
                        borderColor: '#2b6cb0',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#2b6cb0'
                    },
                    {
                        label: 'Female',
                        data: females,
                        borderColor: '#d53f8c',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#d53f8c'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: true,
                        font: {weight: 'bold', size: 10},
                        color: '#2d3748',
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        borderRadius: 4,
                        padding: 4,
                        anchor: 'end',
                        align: 'top'
                    },
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => value.toLocaleString() }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    renderSummary(data){
        const grid = document.getElementById('summaryGrid');

        const total = data.reduce((sum,e) => sum + (parseInt(e.male)||0) + (parseInt(e.female)||0), 0);
        const totalMale = data.reduce((sum,e) => sum + (parseInt(e.male)||0), 0);
        const totalFemale = data.reduce((sum,e) => sum + (parseInt(e.female)||0), 0);

        const validData = data.filter(e => {
            const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
            return (endYear - startYear) === 1;
        });

        const programs = new Set(validData.map(e => e.program_id)).size;

        grid.innerHTML=`
        <div class="summary-card">
            <h3>${total.toLocaleString()}</h3>
            <p>Total Enrollees</p>
        </div>

        <div class="summary-card">
            <h3>${totalMale.toLocaleString()}</h3>
            <p>Male Students</p>
        </div>

        <div class="summary-card">
            <h3>${totalFemale.toLocaleString()}</h3>
            <p>Female Students</p>
        </div>

        <div class="summary-card">
            <h3>${programs}</h3>
            <p>Programs</p>
        </div>
        `;
    }

    async refreshEnrollmentsTable(){
        try{
            const programFilter = document.getElementById('enrollProgramFilter').value;
            const yearFilter = document.getElementById('enrollYearFilter').value;

            let filtered = [...this.allEnrollments].filter(e => {
                const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1;
            });

            if(programFilter){
                filtered = filtered.filter(e => e.program_id == programFilter);
            }
            if(yearFilter){
                filtered = filtered.filter(e => e.academic_year === yearFilter);
            }

            const tbody = document.getElementById('enrollmentsTable');
            
            if(filtered.length === 0){
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No records found</td></tr>';
                return;
            }

            const programMap = {};
            this.allPrograms.forEach(p => {
                programMap[p.id] = p.name;
            });

            const semesterMap = {1:'First',2:'Second',3:'Summer'};

            if(this.sortColumn){
                filtered.sort((a,b)=>{
                    let valA, valB;
                    switch(this.sortColumn){
                        case 'program':
                            valA = programMap[a.program_id] || a.program_id;
                            valB = programMap[b.program_id] || b.program_id;
                            break;
                        case 'academic_year':
                            valA = a.academic_year;
                            valB = b.academic_year;
                            break;
                        case 'semester':
                            valA = parseInt(a.semester);
                            valB = parseInt(b.semester);
                            break;
                        case 'male':
                            valA = parseInt(a.male) || 0;
                            valB = parseInt(b.male) || 0;
                            break;
                        case 'female':
                            valA = parseInt(a.female) || 0;
                            valB = parseInt(b.female) || 0;
                            break;
                        case 'total':
                            valA = (parseInt(a.male)||0) + (parseInt(a.female)||0);
                            valB = (parseInt(b.male)||0) + (parseInt(b.female)||0);
                            break;
                        default:
                            valA = '';
                            valB = '';
                    }
                    if(valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                    if(valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            tbody.innerHTML = filtered.map(e => `
                <tr>
                    <td>${programMap[e.program_id] || e.program_id}</td>
                    <td>${e.academic_year}</td>
                    <td>${semesterMap[e.semester] || e.semester}</td>
                    <td><strong>${e.male}</strong></td>
                    <td><strong>${e.female}</strong></td>
                    <td><strong>${(parseInt(e.male)||0) + (parseInt(e.female)||0)}</strong></td>
                    <td>
                        <button class="btn-warning btn-small" onclick="tracker.openEditModal(${e.id})">✏️ Edit</button>
                        <button class="btn-danger btn-small" onclick="tracker.deleteEnrollment(${e.id})">🗑️ Delete</button>
                    </td>
                </tr>
            `).join('');
        }catch(e){
            this.showStatus('Error loading enrollments: '+e.message,'error');
        }
    }

    openEditModal(recordId){
        const record = this.allEnrollments.find(e => e.id == recordId);
        if(!record) return;

        this.editingRecord = record;

        const programMap = {};
        this.allPrograms.forEach(p => {
            programMap[p.id] = p.name;
        });

        const semesterMap = {1:'First',2:'Second',3:'Summer'};

        document.getElementById('editProgramName').value = programMap[record.program_id] || record.program_id;
        document.getElementById('editYear').value = record.academic_year;
        document.getElementById('editSemester').value = semesterMap[record.semester] || record.semester;
        document.getElementById('editMale').value = record.male;
        document.getElementById('editFemale').value = record.female;
        document.getElementById('editModal').style.display = 'block';
    }

    closeEditModal(){
        document.getElementById('editModal').style.display = 'none';
        this.editingRecord = null;
        document.getElementById('editStatus').style.display = 'none';
    }

    async handleEditEnrollment(event){
        event.preventDefault();

        if(!this.editingRecord) return;

        const formData = new FormData();
        formData.append('id', this.editingRecord.id);
        formData.append('male', document.getElementById('editMale').value);
        formData.append('female', document.getElementById('editFemale').value);

        try{
            const res = await fetch('api/edit-enrollment.php',{
                method:'POST',
                body:formData
            });

            const result = await res.json();
            if(result.success){
                this.showStatus('✅ Enrollment updated','success','editStatus');
                setTimeout(async () => {
                    this.closeEditModal();
                    await this.loadYears();
                    await this.loadPredictionsData();
                    this.loadAllProgramsCharts();
                    this.refreshEnrollmentsTable();
                }, 1000);
            }else{
                this.showStatus('❌ Error: '+result.message,'error','editStatus');
            }
        }catch(e){
            this.showStatus('❌ Error updating enrollment','error','editStatus');
        }
    }

    async deleteEnrollment(id){
        if(!confirm('Are you sure you want to delete this record?')) return;

        try{
            const formData = new FormData();
            formData.append('id',id);

            const res = await fetch('api/delete-enrollment.php',{
                method:'POST',
                body:formData
            });

            const result = await res.json();
            if(result.success){
                this.showStatus('✅ Record deleted','success');
                await this.loadYears();
                await this.loadPredictionsData();
                this.loadAllProgramsCharts();
                this.refreshEnrollmentsTable();
            }else{
                this.showStatus('Error: '+result.message,'error');
            }
        }catch(e){
            this.showStatus('Error deleting record','error');
        }
    }

    async handleAddEnrollment(event){
        event.preventDefault();

        const formData = new FormData();
        formData.append('program_id',document.getElementById('formProgram').value);
        formData.append('academic_year',document.getElementById('formYear').value);
        formData.append('semester',document.getElementById('formSemester').value);
        formData.append('male',document.getElementById('formMale').value);
        formData.append('female',document.getElementById('formFemale').value);

        try{
            const res = await fetch('api/add-enrollment.php',{
                method:'POST',
                body:formData
            });

            const result = await res.json();
            if(result.success){
                this.showStatus('✅ Enrollment added successfully','success','addStatus');
                document.getElementById('addEnrollmentForm').reset();
                await this.loadYears();
                await this.loadPredictionsData();
                this.loadAllProgramsCharts();
                this.refreshEnrollmentsTable();
                this.displayRecentAdded();
            }else{
                this.showStatus('❌ Error: '+result.message,'error','addStatus');
            }
        }catch(e){
            this.showStatus('❌ Error adding enrollment','error','addStatus');
        }
    }

    displayRecentAdded(){
        const recentList = document.getElementById('recentList');
        
        const validData = this.allEnrollments.filter(e => {
            const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
            return (endYear - startYear) === 1;
        });

        const recent = [...validData].slice(-5).reverse();

        const programMap = {};
        this.allPrograms.forEach(p => {
            programMap[p.id] = p.name;
        });

        const semesterMap = {1:'First',2:'Second',3:'Summer'};

        if(recent.length === 0){
            recentList.innerHTML = '<p style="color:#718096;text-align:center;">No recent enrollments</p>';
            return;
        }

        recentList.innerHTML = recent.map(e => `
            <div class="recent-item">
                <div>
                    <strong>${programMap[e.program_id] || e.program_id}</strong>
                    <div class="recent-info">${e.academic_year} - ${semesterMap[e.semester]}</div>
                </div>
                <div style="text-align:right;">
                    <div><strong>${(parseInt(e.male)||0) + (parseInt(e.female)||0)}</strong> students</div>
                    <div class="recent-info">${e.male||0}M / ${e.female||0}F</div>
                </div>
            </div>
        `).join('');
    }

    // ─────────────────────────────────────────────────────────────────
    // PREDICTIONS TAB — Combined chart (historical + predicted, all programs)
    // Exactly mirrors refreshCombinedChart / createCombinedChart in overview
    // ─────────────────────────────────────────────────────────────────
    refreshPredCombinedChart(){
        const semFilter   = document.getElementById('predSemesterFilter')?.value || '';
        const modelFilter = document.getElementById('predModelFilter')?.value    || 'Ensemble';

        // ── Historical data (same logic as refreshCombinedChart) ──
        let filtered = [...this.allEnrollments].filter(e => {
            const [sy, ey] = String(e.academic_year).split('-').map(Number);
            return (ey - sy) === 1;
        });
        if(semFilter){
            if(['1','2','3'].includes(semFilter)){
                filtered = filtered.filter(e => parseInt(e.semester) === parseInt(semFilter));
            } else if(semFilter === '12'){
                filtered = filtered.filter(e => [1,2].includes(parseInt(e.semester)));
            }
        }
        const histAgg = {};
        filtered.forEach(e => {
            const key = `${e.academic_year} S${e.semester}`;
            if(!histAgg[key]) histAgg[key] = { total:0, male:0, female:0 };
            histAgg[key].male   += parseInt(e.male)   || 0;
            histAgg[key].female += parseInt(e.female) || 0;
            histAgg[key].total  += (parseInt(e.male)||0) + (parseInt(e.female)||0);
        });

        // ── Prediction data ──
        let preds = this.allPredictions.filter(p => p.model_name === modelFilter);
        if(semFilter && ['1','2','3'].includes(semFilter)){
            preds = preds.filter(p => parseInt(p.semester) === parseInt(semFilter));
        } else if(semFilter === '12'){
            preds = preds.filter(p => [1,2].includes(parseInt(p.semester)));
        }
        const predAgg = {};
        preds.forEach(p => {
            const key = `${p.academic_year} S${p.semester}`;
            if(!predAgg[key]) predAgg[key] = { total:0, male:0, female:0 };
            predAgg[key].total  += parseInt(p.predicted_total)  || 0;
            predAgg[key].male   += parseInt(p.predicted_male)   || 0;
            predAgg[key].female += parseInt(p.predicted_female) || 0;
        });

        // ── Merge & sort all period keys ──
        const allKeys = [...new Set([...Object.keys(histAgg), ...Object.keys(predAgg)])].sort((a, b) => {
            const [ayA, sA] = a.split(' S');
            const [ayB, sB] = b.split(' S');
            const yA = parseInt(ayA.split('-')[0]);
            const yB = parseInt(ayB.split('-')[0]);
            return yA !== yB ? yA - yB : parseInt(sA) - parseInt(sB);
        });

        if(allKeys.length === 0) return;

        const toVal = (map, key, field) => Object.prototype.hasOwnProperty.call(map, key) ? map[key][field] : null;

        const ctx = document.getElementById('predCombinedChart');
        if(!ctx) return;
        if(this.predCombinedChart) this.predCombinedChart.destroy();

        this.predCombinedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: allKeys,
                datasets: [
                    {
                        label: 'Total Enrollment',
                        data: allKeys.map(k => toVal(histAgg, k, 'total')),
                        borderColor: '#ed8936',
                        backgroundColor: 'rgba(237,137,54,0.15)',
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: '#ed8936',
                        spanGaps: false
                    },
                    {
                        label: 'Male Students',
                        data: allKeys.map(k => toVal(histAgg, k, 'male')),
                        borderColor: '#2b6cb0',
                        backgroundColor: 'rgba(43,108,176,0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#2b6cb0',
                        spanGaps: false
                    },
                    {
                        label: 'Female Students',
                        data: allKeys.map(k => toVal(histAgg, k, 'female')),
                        borderColor: '#d53f8c',
                        backgroundColor: 'rgba(213,63,140,0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#d53f8c',
                        spanGaps: false
                    },
                    {
                        label: 'Predicted Total',
                        data: allKeys.map(k => toVal(predAgg, k, 'total')),
                        borderColor: '#ed8936',
                        backgroundColor: 'rgba(237,137,54,0.08)',
                        borderWidth: 4,
                        borderDash: [8, 4],
                        fill: false,
                        tension: 0.4,
                        pointRadius: 6,
                        pointStyle: 'triangle',
                        pointBackgroundColor: '#ed8936',
                        spanGaps: false
                    },
                    {
                        label: 'Predicted Male',
                        data: allKeys.map(k => toVal(predAgg, k, 'male')),
                        borderColor: '#2b6cb0',
                        borderWidth: 3,
                        borderDash: [8, 4],
                        fill: false,
                        tension: 0.4,
                        pointRadius: 5,
                        pointStyle: 'triangle',
                        pointBackgroundColor: '#2b6cb0',
                        spanGaps: false
                    },
                    {
                        label: 'Predicted Female',
                        data: allKeys.map(k => toVal(predAgg, k, 'female')),
                        borderColor: '#d53f8c',
                        borderWidth: 3,
                        borderDash: [8, 4],
                        fill: false,
                        tension: 0.4,
                        pointRadius: 5,
                        pointStyle: 'triangle',
                        pointBackgroundColor: '#d53f8c',
                        spanGaps: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: true,
                        font: { weight: 'bold', size: 11 },
                        color: '#2d3748',
                        backgroundColor: 'rgba(255,255,255,0.95)',
                        borderRadius: 4,
                        padding: 4,
                        anchor: 'end',
                        align: 'top'
                    },
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => value.toLocaleString() }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    }

    // ─────────────────────────────────────────────────────────────────
    // PREDICTIONS TAB — Per-program charts grid (historical + predicted)
    // Exactly mirrors loadAllProgramsCharts / createProgramChart in overview
    // ─────────────────────────────────────────────────────────────────
    refreshPredByProgramCharts(){
        const modelFilter = document.getElementById('predProgModelFilter')?.value || 'Ensemble';
        const grid = document.getElementById('predProgramsChartsGrid');
        if(!grid) return;

        // Destroy old charts
        if(!this.predProgramCharts) this.predProgramCharts = {};
        Object.values(this.predProgramCharts).forEach(c => { try{ c.destroy(); }catch(e){} });
        this.predProgramCharts = {};
        grid.innerHTML = '';

        const preds = this.allPredictions.filter(p => p.model_name === modelFilter);

        let hasAny = false;
        for(const programId of Object.keys(programNames).map(Number)){

            // Historical for this program
            const histData = this.allEnrollments.filter(e => {
                const [sy, ey] = String(e.academic_year).split('-').map(Number);
                return (ey - sy) === 1 && e.program_id == programId;
            });

            // Predictions for this program
            const predData = preds.filter(p => p.program_id == programId);

            if(histData.length === 0 && predData.length === 0) continue;
            hasAny = true;

            // Sort both
            const sortPeriod = (a, b) => {
                const ya = parseInt(String(a.academic_year).split('-')[0]);
                const yb = parseInt(String(b.academic_year).split('-')[0]);
                return ya !== yb ? ya - yb : parseInt(a.semester) - parseInt(b.semester);
            };
            histData.sort(sortPeriod);
            predData.sort(sortPeriod);

            // Stats for card header (historical totals)
            const totalHist   = histData.reduce((s,e) => s + (parseInt(e.male)||0) + (parseInt(e.female)||0), 0);
            const totalMaleH  = histData.reduce((s,e) => s + (parseInt(e.male)||0), 0);
            const totalFemaleH= histData.reduce((s,e) => s + (parseInt(e.female)||0), 0);
            const totalPred   = predData.reduce((s,p) => s + (parseInt(p.predicted_total)||0), 0);

            // Build merged label list
            const histKeys = histData.map(e => `${e.academic_year} S${e.semester}`);
            const predKeys = predData.map(p => `${p.academic_year} S${p.semester}`);
            const allKeys  = [...new Set([...histKeys, ...predKeys])].sort((a, b) => {
                const [ayA, sA] = a.split(' S');
                const [ayB, sB] = b.split(' S');
                const yA = parseInt(ayA.split('-')[0]);
                const yB = parseInt(ayB.split('-')[0]);
                return yA !== yB ? yA - yB : parseInt(sA) - parseInt(sB);
            });

            const histMap = {};
            histData.forEach(e => {
                histMap[`${e.academic_year} S${e.semester}`] = {
                    total: (parseInt(e.male)||0) + (parseInt(e.female)||0),
                    male:  parseInt(e.male)   || 0,
                    female:parseInt(e.female) || 0
                };
            });
            const predMap = {};
            predData.forEach(p => {
                predMap[`${p.academic_year} S${p.semester}`] = {
                    total: parseInt(p.predicted_total)  || 0,
                    male:  parseInt(p.predicted_male)   || 0,
                    female:parseInt(p.predicted_female) || 0
                };
            });

            const toVal = (map, key, field) => Object.prototype.hasOwnProperty.call(map, key) ? map[key][field] : null;

            const card = document.createElement('div');
            card.className = 'program-chart-card';
            card.innerHTML = `
                <h3>${programNames[programId]}</h3>
                <div class="program-stats">
                    <div class="stat-item">Historical Total: <strong>${totalHist.toLocaleString()}</strong></div>
                    <div class="stat-item">Male: <strong>${totalMaleH.toLocaleString()}</strong></div>
                    <div class="stat-item">Female: <strong>${totalFemaleH.toLocaleString()}</strong></div>
                    <div class="stat-item">Predicted Total: <strong>${totalPred.toLocaleString()}</strong></div>
                </div>
                <div class="chart-container">
                    <canvas id="pred-chart-prog-${programId}"></canvas>
                </div>
            `;
            grid.appendChild(card);

            setTimeout(() => {
                const ctx = document.getElementById(`pred-chart-prog-${programId}`);
                if(!ctx) return;

                this.predProgramCharts[programId] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: allKeys,
                        datasets: [
                            {
                                label: 'Total',
                                data: allKeys.map(k => toVal(histMap, k, 'total')),
                                borderColor: '#ed8936',
                                backgroundColor: 'rgba(237,137,54,0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#ed8936',
                                spanGaps: false
                            },
                            {
                                label: 'Male',
                                data: allKeys.map(k => toVal(histMap, k, 'male')),
                                borderColor: '#2b6cb0',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#2b6cb0',
                                spanGaps: false
                            },
                            {
                                label: 'Female',
                                data: allKeys.map(k => toVal(histMap, k, 'female')),
                                borderColor: '#d53f8c',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#d53f8c',
                                spanGaps: false
                            },
                            {
                                label: 'Predicted Total',
                                data: allKeys.map(k => toVal(predMap, k, 'total')),
                                borderColor: '#ed8936',
                                backgroundColor: 'rgba(237,137,54,0.06)',
                                borderWidth: 3,
                                borderDash: [7, 4],
                                fill: false,
                                tension: 0.4,
                                pointRadius: 5,
                                pointStyle: 'triangle',
                                pointBackgroundColor: '#ed8936',
                                spanGaps: false
                            },
                            {
                                label: 'Predicted Male',
                                data: allKeys.map(k => toVal(predMap, k, 'male')),
                                borderColor: '#2b6cb0',
                                borderWidth: 2,
                                borderDash: [7, 4],
                                fill: false,
                                tension: 0.4,
                                pointRadius: 4,
                                pointStyle: 'triangle',
                                pointBackgroundColor: '#2b6cb0',
                                spanGaps: false
                            },
                            {
                                label: 'Predicted Female',
                                data: allKeys.map(k => toVal(predMap, k, 'female')),
                                borderColor: '#d53f8c',
                                borderWidth: 2,
                                borderDash: [7, 4],
                                fill: false,
                                tension: 0.4,
                                pointRadius: 4,
                                pointStyle: 'triangle',
                                pointBackgroundColor: '#d53f8c',
                                spanGaps: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            datalabels: {
                                display: true,
                                font: { weight: 'bold', size: 10 },
                                color: '#2d3748',
                                backgroundColor: 'rgba(255,255,255,0.9)',
                                borderRadius: 4,
                                padding: 4,
                                anchor: 'end',
                                align: 'top'
                            },
                            legend: { display: true, position: 'top' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: value => value.toLocaleString() }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }, 0);
        }

        if(!hasAny){
            grid.innerHTML = '<div class="text-center" style="padding:40px;grid-column:1/-1;color:#718096;">No prediction data available</div>';
        }
    }

    buildPredictionMap(predictions){
        const modelMap = {
            Prophet: {},
            LSTM: {},
            XGBoost: {},
            Ensemble: {}
        };

        predictions.forEach(pred => {
            const key = `${pred.academic_year} S${pred.semester}`;
            const modelName = pred.model_name || 'Ensemble';
            if (!modelMap[modelName]) modelMap[modelName] = {};
            modelMap[modelName][key] = pred;
        });

        return modelMap;
    }

    renderPredictionLegend(){
        const legend = document.getElementById('predictionLegend');
        if(!legend) return;

        legend.innerHTML = `
            <div class="legend-item"><span class="legend-line" style="background:#2E7D32;"></span>Historical Total</div>
            <div class="legend-item"><span class="legend-line" style="background:#D81B60;"></span>Prophet</div>
            <div class="legend-item"><span class="legend-line" style="background:#FF6F00;"></span>LSTM</div>
            <div class="legend-item"><span class="legend-line" style="background:#6D4C41;"></span>XGBoost</div>
            <div class="legend-item"><span class="legend-line" style="background:#00897B;"></span>Ensemble</div>
        `;
        legend.style.display = 'flex';
    }

    renderAvailableModels(modelMap){
        const container = document.getElementById('availableModels');
        if(!container) return;

        const hasProphet = Object.keys(modelMap.Prophet || {}).length > 0;
        const hasLstm = Object.keys(modelMap.LSTM || {}).length > 0;
        const hasXgboost = Object.keys(modelMap.XGBoost || {}).length > 0;
        const hasEnsemble = Object.keys(modelMap.Ensemble || {}).length > 0;

        container.innerHTML = `
            <span class="model-pill ${hasProphet ? 'prophet' : 'missing'}">${hasProphet ? '✓' : '—'} Prophet</span>
            <span class="model-pill ${hasLstm ? 'lstm' : 'missing'}">${hasLstm ? '✓' : '—'} LSTM</span>
            <span class="model-pill ${hasXgboost ? 'xgboost' : 'missing'}">${hasXgboost ? '✓' : '—'} XGBoost</span>
            <span class="model-pill ${hasEnsemble ? 'ensemble' : 'missing'}">${hasEnsemble ? '✓' : '—'} Ensemble</span>
        `;
        container.style.display = 'flex';
    }

    refreshPredictions(){
        try{
            const programFilter = document.getElementById('predProgramFilter').value;

            if(!programFilter){
                this.showStatus('Please select a program','error','status');
                return;
            }

            const historicalData = this.allEnrollments.filter(e => {
                const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1 && e.program_id == programFilter;
            });

            if(historicalData.length === 0){
                this.showStatus('No historical data for this program','error','status');
                return;
            }

            const predictions = this.allPredictions.filter(p => p.program_id == programFilter);
            this.createPredictionChart(programFilter, historicalData, predictions);
            this.displayPredictionStats(predictions);
            this.displayPredictionComparison(predictions);

        }catch(e){
            this.showStatus('Error loading predictions: '+e.message,'error','status');
        }
    }

    createPredictionChart(programId, historicalData, predictions){
        historicalData.sort((a,b)=>{
            const yearA=parseInt(String(a.academic_year).split('-')[0]);
            const yearB=parseInt(String(b.academic_year).split('-')[0]);
            return yearA-yearB||parseInt(a.semester)-parseInt(b.semester);
        });

        const historicalLabels = historicalData.map(e=>`${e.academic_year} S${e.semester}`);
        const modelMap = this.buildPredictionMap(predictions);
        const predictionPeriods = [...new Set(predictions.map(p => `${p.academic_year} S${p.semester}`))];

        const allLabels = [...historicalLabels];
        predictionPeriods.forEach(label => {
            if (!allLabels.includes(label)) allLabels.push(label);
        });

        const historicalLookup = {};
        historicalData.forEach(e => {
            historicalLookup[`${e.academic_year} S${e.semester}`] = (parseInt(e.male)||0)+(parseInt(e.female)||0);
        });

        const makeSeries = (lookupMap) => allLabels.map(label =>
            Object.prototype.hasOwnProperty.call(lookupMap, label) ? lookupMap[label] : null
        );

        const prophetLookup = {};
        const lstmLookup = {};
        const xgboostLookup = {};
        const ensembleLookup = {};

        Object.entries(modelMap.Prophet || {}).forEach(([k, v]) => prophetLookup[k] = parseInt(v.predicted_total)||0);
        Object.entries(modelMap.LSTM || {}).forEach(([k, v]) => lstmLookup[k] = parseInt(v.predicted_total)||0);
        Object.entries(modelMap.XGBoost || {}).forEach(([k, v]) => xgboostLookup[k] = parseInt(v.predicted_total)||0);
        Object.entries(modelMap.Ensemble || {}).forEach(([k, v]) => ensembleLookup[k] = parseInt(v.predicted_total)||0);

        const ctx = document.getElementById('predictionChart');
        if(!ctx) return;

        if(this.predictionChart){
            this.predictionChart.destroy();
        }

        const programName = programNames[programId] || `Program ${programId}`;
        document.getElementById('predictionChartTitle').textContent = `${programName} - Historical & Algorithm Predictions`;
        this.renderPredictionLegend();
        this.renderAvailableModels(modelMap);

        this.predictionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: allLabels,
                datasets: [
                    {
                        label: 'Historical Total',
                        data: makeSeries(historicalLookup),
                        borderColor: '#2E7D32',
                        backgroundColor: 'rgba(46,125,50,0.10)',
                        borderWidth: 4,
                        fill: false,
                        tension: 0.3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#2E7D32',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        spanGaps: true
                    },
                    {
                        label: 'Prophet',
                        data: makeSeries(prophetLookup),
                        borderColor: '#D81B60',
                        backgroundColor: 'rgba(216,27,96,0.12)',
                        borderWidth: 2,
                        borderDash: [4,4],
                        fill: false,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#D81B60',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        spanGaps: true
                    },
                    {
                        label: 'LSTM',
                        data: makeSeries(lstmLookup),
                        borderColor: '#FF6F00',
                        backgroundColor: 'rgba(255,111,0,0.18)',
                        borderWidth: 5,
                        borderDash: [],
                        fill: false,
                        tension: 0.3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#FF6F00',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        spanGaps: true,
                        order: 1
                    },
                    {
                        label: 'XGBoost',
                        data: makeSeries(xgboostLookup),
                        borderColor: '#6D4C41',
                        backgroundColor: 'rgba(109,76,65,0.14)',
                        borderWidth: 3,
                        borderDash: [3,3],
                        fill: false,
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#6D4C41',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        spanGaps: true
                    },
                    {
                        label: 'Ensemble',
                        data: makeSeries(ensembleLookup),
                        borderColor: '#00897B',
                        backgroundColor: 'rgba(0,137,123,0.12)',
                        borderWidth: 3,
                        borderDash: [10,5],
                        fill: false,
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#00897B',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        spanGaps: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: false
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value.toLocaleString()
                        }
                    }
                }
            }
        });

        document.getElementById('predictionChartContainer').style.display = 'block';
    }

    displayPredictionComparison(predictions){
        const container = document.getElementById('predictionCompareContainer');
        const tbody = document.getElementById('predictionCompareTableBody');
        if(!container || !tbody) return;

        if(!predictions || predictions.length === 0){
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No predictions available</td></tr>';
            container.style.display = 'block';
            return;
        }

        const modelMap = this.buildPredictionMap(predictions);
        const periods = [...new Set(predictions.map(p => `${p.academic_year} S${p.semester}`))];

        tbody.innerHTML = periods.map(period => `
            <tr>
                <td><strong>${period}</strong></td>
                <td>${modelMap.Prophet?.[period]?.predicted_total ?? '—'}</td>
                <td>${modelMap.LSTM?.[period]?.predicted_total ?? '—'}</td>
                <td>${modelMap.XGBoost?.[period]?.predicted_total ?? '—'}</td>
                <td>${modelMap.Ensemble?.[period]?.predicted_total ?? '—'}</td>
            </tr>
        `).join('');

        container.style.display = 'block';
    }

    displayPredictionStats(predictions){
        const container = document.getElementById('predictionStatsContainer');
        const grid = document.getElementById('predictionsGrid');

        if(!predictions || predictions.length === 0){
            grid.innerHTML = '<div class="text-center" style="grid-column:1/-1;">No predictions available</div>';
            container.style.display = 'block';
            return;
        }

        const semesterMap = {1:'First',2:'Second',3:'Summer'};

        grid.innerHTML = predictions.map(p => {
            const modelName = p.model_name || 'Ensemble';
            const modelClass = String(modelName).toLowerCase();

            return `
                <div class="prediction-card ${modelClass}">
                    <h4>${programNames[p.program_id] || p.program_id}</h4>
                    <div class="prediction-item">
                        <span class="prediction-label">Algorithm</span>
                        <span class="prediction-value">${modelName}</span>
                    </div>
                    <div class="prediction-item">
                        <span class="prediction-label">Academic Year</span>
                        <span class="prediction-value">${p.academic_year}</span>
                    </div>
                    <div class="prediction-item">
                        <span class="prediction-label">Semester</span>
                        <span class="prediction-value">${semesterMap[p.semester] || p.semester}</span>
                    </div>
                    <div class="prediction-item">
                        <span class="prediction-label">Predicted Total</span>
                        <span class="prediction-value">${p.predicted_total}</span>
                    </div>
                    <div class="prediction-item">
                        <span class="prediction-label">Male / Female</span>
                        <span class="prediction-value">${p.predicted_male || '—'} / ${p.predicted_female || '—'}</span>
                    </div>
                    <div class="prediction-item">
                        <span class="prediction-label">Confidence</span>
                        <span class="prediction-value">${p.confidence ? (p.confidence * 100).toFixed(0) + '%' : '—'}</span>
                    </div>
                </div>
            `;
        }).join('');

        container.style.display = 'block';
    }

    async loadEvaluationMetrics(){
        const programId = document.getElementById('evaluationProgramFilter').value;
    
        if (!programId) {
            alert('Please select a program');
            return;
        }

        try {
            const res = await fetch('api/model-metrics.php?program_id=' + programId);
            const data = await res.json();
            
            const metrics = Array.isArray(data) ? data : (data.data || []);
            if (!metrics || metrics.length === 0) {
                alert('No metrics found');
                return;
            }

            const byModel = {};
            metrics.forEach(m => {
                if (!byModel[m.model_name]) byModel[m.model_name] = {};
                byModel[m.model_name][m.metric_name] = m.metric_value;
            });

            this.displayCommonMetrics(byModel);
            this.displayModelCards(byModel);
            document.getElementById('comparisonContainer').style.display = 'block';

        } catch(e) {
            alert('Error: ' + e.message);
        }
    }

    displayCommonMetrics(byModel) {
        const commonMetrics = ['MAE', 'RMSE', 'MAPE', 'R²', 'RMSLE', 'Theil_U'];
        const models = ['Prophet', 'LSTM', 'XGBoost'];
        
        let html = '';
        commonMetrics.forEach(metric => {
            const values = models.map(m => byModel[m]?.[metric] ?? 'N/A');
            
            const numeric = values.map(v => isNaN(parseFloat(v)) ? null : parseFloat(v));
            let bestIdx = -1;

            if (metric === 'R²') {
                const filtered = numeric.map((v,i)=>({v,i})).filter(x => x.v !== null);
                if (filtered.length) bestIdx = filtered.reduce((a,b)=>a.v>b.v?a:b).i;
            } else {
                const filtered = numeric.map((v,i)=>({v,i})).filter(x => x.v !== null);
                if (filtered.length) bestIdx = filtered.reduce((a,b)=>a.v<b.v?a:b).i;
            }
            
            html += `<tr>
                <td>${metric}</td>
                <td class="${bestIdx === 0 ? 'best-value' : ''}">${values[0]}</td>
                <td class="${bestIdx === 1 ? 'best-value' : ''}">${values[1]}</td>
                <td class="${bestIdx === 2 ? 'best-value' : ''}">${values[2]}</td>
                <td>${bestIdx >= 0 ? models[bestIdx] : '—'}</td>
            </tr>`;
        });
        
        document.getElementById('commonMetricsTable').innerHTML = html;
    }

    displayModelCards(byModel) {
        let html = '';

        ['Prophet', 'LSTM', 'XGBoost'].forEach(modelName => {
            const modelClass = modelName.toLowerCase();
            const metrics = byModel[modelName] || {};
            
            let metricsHtml = '';
            Object.entries(metrics).forEach(([name, value]) => {
                metricsHtml += `
                    <div class="metric-item ${modelClass}">
                        <span class="metric-label">${name}</span>
                        <span class="metric-value">${value}</span>
                    </div>
                `;
            });

            html += `
                <div class="model-card ${modelClass}">
                    <div class="model-header">
                        <span class="model-badge ${modelClass}">${modelName}</span>
                        <span class="model-name">${modelName}</span>
                    </div>
                    <div class="metrics-grid">
                        ${metricsHtml || '<div class="metric-item"><span class="metric-label">No metrics</span><span class="metric-value">—</span></div>'}
                    </div>
                </div>
            `;
        });

        document.getElementById('modelCardsContainer').innerHTML = html;
    }
}

document.getElementById('login')?.addEventListener('submit',e=>{
    e.preventDefault();
    if(document.getElementById('password').value==='admin123'){
        window.location.href='?login=1';
    }else alert('❌ Wrong password!');
});

if(window.location.search.includes('login=1')){
    var tracker = new EnrollmentTracker();
}
</script>

</body>
</html>