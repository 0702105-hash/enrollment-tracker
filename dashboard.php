<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrollment Tracker Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box}

body{
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    background:#f5f7fb;
    color:#2d3748;
}

.container{
    max-width:1800px;
    margin:auto;
    padding:30px 20px;
}

.header-banner{
    background:linear-gradient(135deg,#2f855a,#2c5282);
    color:white;
    padding:30px;
    border-radius:18px;
    margin-bottom:25px;
}

.header-banner h1{
    font-size:28px;
    font-weight:700;
    margin-bottom:5px;
}

.header-banner p{
    opacity:.95;
    font-size:14px;
}

.card{
    background:white;
    border-radius:18px;
    padding:25px;
    box-shadow:0 8px 25px rgba(0,0,0,0.06);
    margin-bottom:25px;
}

.card h2{
    margin-bottom:20px;
    color:#2d3748;
}

.tabs{
    display:flex;
    gap:10px;
    margin-bottom:20px;
    border-bottom:2px solid #e2e8f0;
    overflow-x:auto;
}

.tab-btn{
    padding:12px 20px;
    background:none;
    border:none;
    border-bottom:3px solid transparent;
    cursor:pointer;
    font-weight:600;
    color:#718096;
    transition:all 0.3s;
    white-space:nowrap;
}

.tab-btn:hover{
    color:#2d3748;
}

.tab-btn.active{
    color:#2b6cb0;
    border-bottom-color:#2b6cb0;
}

.tab-content{
    display:none;
}

.tab-content.active{
    display:block;
}

.select-container{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    align-items:center;
    margin-bottom:20px;
}

select,input,button,textarea{
    padding:12px 18px;
    border-radius:12px;
    border:1px solid #e2e8f0;
    font-size:15px;
    font-family:inherit;
}

select:focus,input:focus,textarea:focus{
    outline:none;
    border-color:#2b6cb0;
    box-shadow:0 0 0 3px rgba(43,108,176,0.1);
}

button{
    background:#3182ce;
    color:white;
    border:none;
    cursor:pointer;
    font-weight:600;
    transition:all 0.3s;
    padding:12px 18px;
}

button:hover{
    background:#2c5282;
    transform:translateY(-2px);
}

button.btn-success{
    background:#2f855a;
}

button.btn-success:hover{
    background:#22543d;
}

button.btn-danger{
    background:#e53e3e;
}

button.btn-danger:hover{
    background:#c53030;
}

button.btn-small{
    padding:8px 12px;
    font-size:13px;
}

button.btn-warning{
    background:#f6ad55;
}

button.btn-warning:hover{
    background:#ed8936;
}

.status{
    margin-top:15px;
    padding:14px;
    border-radius:10px;
    display:none;
}

.success{
    background:#e6fffa;
    color:#065f46;
    border:1px solid #81e6d9;
}

.error{
    background:#fff5f5;
    color:#742a2a;
    border:1px solid #fc8181;
}

.summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:25px;
}

.summary-card{
    background:#f8fafc;
    border-radius:16px;
    padding:22px;
    border-left:4px solid #2b6cb0;
}

.summary-card h3{
    font-size:34px;
    margin-bottom:5px;
    font-weight:700;
}

.summary-card p{
    color:#718096;
    font-size:14px;
}

.programs-charts-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(600px,1fr));
    gap:25px;
    margin-bottom:25px;
}

.program-chart-card{
    background:white;
    border-radius:18px;
    padding:25px;
    box-shadow:0 8px 25px rgba(0,0,0,0.06);
}

.program-chart-card h3{
    margin-bottom:5px;
    color:#2d3748;
    font-size:18px;
}

.program-stats{
    display:flex;
    gap:15px;
    margin-bottom:15px;
    font-size:13px;
    color:#718096;
    flex-wrap:wrap;
}

.stat-item{
    display:flex;
    gap:5px;
}

.stat-item strong{
    color:#2d3748;
}

.chart-container{
    height:300px;
    margin-top:15px;
}

.table-container{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

thead{
    background:#f8fafc;
}

th,td{
    padding:15px;
    text-align:left;
    border-bottom:1px solid #e2e8f0;
}

th{
    font-weight:600;
    color:#2d3748;
}

th.sortable{
    cursor:pointer;
}

th.sortable .sort-indicator{
    margin-left:4px;
    font-size:0.8em;
    opacity:0.6;
}

tbody tr:hover{
    background:#f8fafc;
}

.text-center{
    text-align:center;
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#2d3748;
}

.form-row{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:15px;
    margin-bottom:15px;
}

.form-actions{
    display:flex;
    gap:10px;
    margin-top:25px;
    flex-wrap:wrap;
}

.predictions-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:20px;
    margin-top:20px;
}

.prediction-card{
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    color:white;
    border-radius:16px;
    padding:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}

.prediction-card h4{
    margin-bottom:15px;
    font-size:16px;
}

.prediction-item{
    display:flex;
    justify-content:space-between;
    margin:10px 0;
    padding:8px 0;
    border-bottom:1px solid rgba(255,255,255,0.2);
}

.prediction-item:last-child{
    border-bottom:none;
}

.prediction-label{
    font-size:13px;
    opacity:0.8;
}

.prediction-value{
    font-weight:600;
    font-size:14px;
}

.recent-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 0;
    border-bottom:1px solid #e2e8f0;
}

.recent-item:last-child{
    border-bottom:none;
}

.recent-info{
    color:#718096;
    font-size:13px;
}

#loginForm{
    max-width:400px;
    margin:120px auto;
    padding:40px;
    background:white;
    border-radius:20px;
    box-shadow:0 20px 40px rgba(0,0,0,.1);
    text-align:center;
}

#loginForm h2{
    margin-bottom:10px;
    color:#2d3748;
}

#loginForm p{
    color:#718096;
    margin-bottom:25px;
}

#loginForm input{
    width:100%;
    margin:15px 0;
    padding:12px;
}

#loginForm button{
    width:100%;
}

/* ===== EVALUATION TAB ===== */

.metrics-container{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(350px,1fr));
    gap:25px;
    margin-top:30px;
}

.model-card{
    background:white;
    border-radius:16px;
    padding:25px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
    border-top:5px solid #2b6cb0;
}

.model-card.sarmax{border-top-color:#667eea;}
.model-card.prophet{border-top-color:#f093fb;}
.model-card.lstm{border-top-color:#ed8936;}

.model-header{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:20px;
    padding-bottom:15px;
    border-bottom:2px solid #e2e8f0;
}

.model-badge{
    display:inline-block;
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
}

.model-badge.sarmax{background:#e6e6ff;color:#667eea;}
.model-badge.prophet{background:#ffe6f5;color:#f093fb;}
.model-badge.lstm{background:#fff5e6;color:#ed8936;}

.model-name{
    font-size:18px;
    font-weight:700;
    color:#2d3748;
}

.metrics-grid{
    display:grid;
    grid-template-columns:1fr;
    gap:12px;
}

.metric-item{
    display:flex;
    justify-content:space-between;
    padding:12px;
    background:#f8fafc;
    border-radius:10px;
    border-left:4px solid #e2e8f0;
    gap:12px;
}

.metric-item.sarmax{border-left-color:#667eea;}
.metric-item.prophet{border-left-color:#f093fb;}
.metric-item.lstm{border-left-color:#ed8936;}

.metric-label{
    font-weight:600;
    color:#2d3748;
    font-size:13px;
}

.metric-value{
    font-weight:700;
    font-size:14px;
}

.metric-category{
    font-size:11px;
    color:#718096;
    margin-top:2px;
}

.comparison-table{
    width:100%;
    border-collapse:collapse;
    margin:30px 0;
    background:white;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

.comparison-table thead{
    background:#f8fafc;
    border-bottom:2px solid #e2e8f0;
}

.comparison-table th{
    padding:15px;
    text-align:left;
    font-weight:600;
    color:#2d3748;
}

.comparison-table td{
    padding:12px 15px;
    border-bottom:1px solid #e2e8f0;
}

.comparison-table tbody tr:hover{
    background:#f8fafc;
}

.best-value{
    background:#e6f7ff;
    font-weight:700;
    color:#2b6cb0;
}

.metric-section{
    background:#f8fafc;
    border-radius:12px;
    padding:20px;
    margin:20px 0;
    border-left:4px solid #2b6cb0;
}

.metric-section h3{
    margin-bottom:15px;
    color:#2d3748;
}

.metric-description{
    font-size:13px;
    color:#718096;
    line-height:1.6;
    margin:10px 0;
}

.metric-scale{
    font-size:12px;
    color:#718096;
    margin-top:8px;
    padding:8px;
    background:white;
    border-radius:6px;
}

@media(max-width:768px){
    .programs-charts-grid{
        grid-template-columns:1fr;
    }
    
    .select-container{
        flex-direction:column;
        align-items:stretch;
    }
    
    select,input,button{
        width:100%;
    }
    
    .form-row{
        grid-template-columns:1fr;
    }
    
    .predictions-grid{
        grid-template-columns:1fr;
    }

    .metrics-container{
        grid-template-columns:1fr;
    }
}
</style>
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

    <div class="header-banner">
        <h1>📊 Enrollment Tracker System</h1>
        <p>Charts, predictions, and evaluation metrics</p>
    </div>

    <div class="card">
        <div class="tabs">
            <button class="tab-btn active" data-tab="overview">📊 Overview</button>
            <button class="tab-btn" data-tab="enrollments">👥 Enrollments</button>
            <button class="tab-btn" data-tab="add-enrollment">➕ Add Enrollment</button>
            <button class="tab-btn" data-tab="predictions">🔮 Predictions</button>
            <button class="tab-btn" data-tab="evaluation">📈 Evaluation</button>
        </div>
    </div>

    <!-- OVERVIEW -->
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

    <!-- ENROLLMENTS -->
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

    <!-- ADD -->
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

    <!-- PREDICTIONS -->
    <div id="predictions" class="tab-content">
        <div class="card">
            <h2>🔮 Enrollment Predictions with Historical Data</h2>

            <div class="select-container">
                <label style="margin-right:10px;font-weight:600;">Select Program:</label>
                <select id="predProgramFilter" style="flex:1;max-width:400px;">
                    <option value="">Choose a program</option>
                </select>
                <button onclick="tracker.refreshPredictions()">🔄 Load Chart</button>
            </div>

            <div id="predictionChartContainer" class="card" style="margin-top:20px;display:none;">
                <h3 id="predictionChartTitle"></h3>
                <div class="chart-container">
                    <canvas id="predictionChart"></canvas>
                </div>
            </div>

            <div id="predictionStatsContainer" style="margin-top:20px;display:none;">
                <div class="predictions-grid" id="predictionsGrid"></div>
            </div>
        </div>
    </div>

    <!-- EVALUATION -->
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
                            <th style="color:#667eea;">SARMAX</th>
                            <th style="color:#f093fb;">Prophet</th>
                            <th style="color:#ed8936;">LSTM</th>
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
        this.allEnrollments = [];
        this.allPrograms = [];
        this.allPredictions = [];
        this.allMetrics = [];
        this.editingRecord = null;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.init();
    }

    init(){
<?php if (isset($_GET['login'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('dashboard').style.display='block';
            this.setupTabs();
            this.loadPrograms();
            this.bindEvents();
            this.setupEnrollmentsSorting();
        });
<?php endif; ?>
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
                }else if(tabId === 'overview'){
                    this.loadAllProgramsCharts();
                    this.refreshCombinedChart();
                }
            });
        });
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

    refreshPredictions(){
        try{
            const programFilter = document.getElementById('predProgramFilter').value;

            if(!programFilter){
                this.showStatus('Please select a program','error','status');
                return;
            }

            const allData = this.allEnrollments.filter(e => {
                const [startYear, endYear] = String(e.academic_year).split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1 && e.program_id == programFilter;
            });

            if(allData.length === 0){
                this.showStatus('No historical data for this program','error','status');
                return;
            }

            const predictions = this.allPredictions.filter(p => p.program_id == programFilter);
            this.createPredictionChart(programFilter, allData, predictions);
            this.displayPredictionStats(predictions);

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

        const labels = historicalData.map(e=>`${e.academic_year} S${e.semester}`);
        const totals = historicalData.map(e=>(parseInt(e.male)||0)+(parseInt(e.female)||0));
        const males = historicalData.map(e=>parseInt(e.male)||0);
        const females = historicalData.map(e=>parseInt(e.female)||0);

        let allLabels = [...labels];
        let allTotals = [...totals];
        let allMales = [...males];
        let allFemales = [...females];

        if(predictions && predictions.length > 0){
            predictions.sort((a,b)=>{
                const yearA=parseInt(String(a.academic_year).split('-')[0]);
                const yearB=parseInt(String(b.academic_year).split('-')[0]);
                return yearA-yearB||parseInt(a.semester)-parseInt(b.semester);
            });

            predictions.forEach(pred => {
                allLabels.push(`${pred.academic_year} S${pred.semester} (Pred)`);
                allTotals.push(parseInt(pred.predicted_total)||0);
                allMales.push(parseInt(pred.predicted_male)||0);
                allFemales.push(parseInt(pred.predicted_female)||0);
            });
        }

        const ctx = document.getElementById('predictionChart');
        if(!ctx) return;

        if(this.predictionChart){
            this.predictionChart.destroy();
        }

        const programName = programNames[programId] || `Program ${programId}`;
        document.getElementById('predictionChartTitle').textContent = `${programName} - Historical & Predicted Enrollment`;

        this.predictionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: allLabels,
                datasets: [
                    {
                        label: 'Total',
                        data: allTotals,
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
                        data: allMales,
                        borderColor: '#2b6cb0',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#2b6cb0'
                    },
                    {
                        label: 'Female',
                        data: allFemales,
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
                        ticks: {
                            callback: value => value.toLocaleString()
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        document.getElementById('predictionChartContainer').style.display = 'block';
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

        grid.innerHTML = predictions.map(p => `
            <div class="prediction-card">
                <h4>${programNames[p.program_id] || p.program_id}</h4>
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
        `).join('');

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
        const models = ['SARMAX', 'Prophet', 'LSTM'];
        
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

        ['SARMAX', 'Prophet', 'LSTM'].forEach(modelName => {
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