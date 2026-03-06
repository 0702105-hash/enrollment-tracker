<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrollment Tracker Dashboard - Multi-Model Predictions</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box}

body{
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    background:#f5f7fb;
    color:#2d3748;
}

/* ===== LAYOUT ===== */

.container{
    max-width:1600px;
    margin:auto;
    padding:30px 20px;
}

/* ===== HEADER BANNER ===== */

.header-banner{
    background:linear-gradient(135deg,#2f855a,#2c5282);
    color:white;
    padding:30px;
    border-radius:18px;
    margin-bottom:25px;
    position:relative;
    background-size:cover;
    background-position:center center;
    transition:background-image 400ms ease-in-out, background-color 400ms ease-in-out;
    overflow:hidden;
}

.header-banner::before{
    content:'';
    position:absolute;
    left:0;right:0;top:0;bottom:0;
    background:linear-gradient(180deg,rgba(0,0,0,0.18),rgba(0,0,0,0.18));
    pointer-events:none;
    border-radius:18px;
}

.header-banner h1,
.header-banner p{
    position:relative;
    z-index:1;
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

/* ===== NOTICE ===== */

.notice{
    background:#e6f4ff;
    border:1px solid #b6e0fe;
    padding:18px;
    border-radius:14px;
    margin-bottom:20px;
}

/* ===== CARD ===== */

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

/* ===== TABS ===== */

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

/* ===== CONTROLS ===== */

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

button.btn-info{
    background:#3182ce;
    padding:10px 16px;
    font-size:13px;
}

button.btn-info:hover{
    background:#2c5282;
}

/* ===== STATUS ===== */

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

/* ===== SUMMARY CARDS ===== */

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

/* ===== PROGRAM CHARTS GRID ===== */

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

/* ===== MULTI-MODEL PREDICTION DISPLAY ===== */

.model-metrics-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:20px;
    margin:20px 0;
}

.model-card{
    background:white;
    border-radius:12px;
    padding:20px;
    border-left:5px solid #2b6cb0;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

.model-card.sarmax{border-left-color:#667eea;}
.model-card.prophet{border-left-color:#f093fb;}
.model-card.lstm{border-left-color:#ed8936;}

.model-card h4{
    margin-bottom:15px;
    color:#2d3748;
    font-size:16px;
    display:flex;
    align-items:center;
    gap:8px;
}

.model-badge{
    display:inline-block;
    padding:3px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:600;
}

.model-badge.sarmax{background:#e6e6ff;color:#667eea;}
.model-badge.prophet{background:#ffe6f5;color:#f093fb;}
.model-badge.lstm{background:#fff5e6;color:#ed8936;}

.metric-item{
    display:flex;
    justify-content:space-between;
    padding:8px 0;
    border-bottom:1px solid #e2e8f0;
    font-size:14px;
}

.metric-item:last-child{
    border-bottom:none;
}

.metric-label{
    color:#718096;
    font-weight:500;
}

.metric-value{
    font-weight:600;
    color:#2d3748;
}

.prediction-comparison-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
    margin:20px 0;
}

.prediction-comparison-card{
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    color:white;
    border-radius:16px;
    padding:20px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}

.prediction-comparison-card h4{
    margin-bottom:15px;
    font-size:16px;
}

.prediction-row{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid rgba(255,255,255,0.2);
}

.prediction-row:last-child{
    border-bottom:none;
}

.prediction-row-label{
    font-size:13px;
    opacity:0.9;
}

.prediction-row-value{
    font-weight:600;
    font-size:14px;
}

/* ===== TABLE ===== */

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

/* ===== FORM ===== */

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
}

/* ===== MODAL ===== */

.modal{
    display:none;
    position:fixed;
    z-index:1000;
    left:0;
    top:0;
    width:100%;
    height:100%;
    overflow:auto;
    background-color:rgba(0,0,0,0.4);
}

.modal-content{
    background-color:#fefefe;
    margin:10% auto;
    padding:30px;
    border:1px solid #888;
    border-radius:18px;
    width:90%;
    max-width:600px;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    max-height:90vh;
    overflow-y:auto;
}

.modal-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.modal-header h2{
    margin:0;
}

.close-modal{
    font-size:28px;
    font-weight:bold;
    color:#aaa;
    cursor:pointer;
}

.close-modal:hover,
.close-modal:focus{
    color:#000;
}

/* ===== PREDICTIONS GRID ===== */

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

/* ===== RECENT ===== */

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

/* ===== LOGIN ===== */

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

/* ===== RESPONSIVE ===== */

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

    .model-metrics-grid{
        grid-template-columns:1fr;
    }

    .modal-content{
        width:95%;
        margin:20% auto;
    }

    .tabs{
        overflow-x:auto;
    }
}
</style>
</head>

<body>

<?php if (!isset($_GET['login'])): ?>

<!-- LOGIN -->
<div id="loginForm">
    <h2>🎓 Enrollment Tracker</h2>
    <p>CAS Department Management System</p>
    <form id="login">
        <input type="password" id="password" placeholder="Enter admin password" required autofocus>
        <button type="submit">Login to Dashboard</button>
    </form>
</div>

<?php else: ?>

<!-- DASHBOARD -->
<div class="container" id="dashboard" style="display:none;">

    <!-- HEADER -->
    <div class="header-banner">
        <h1>📊 Enrollment Tracker System</h1>
        <p>Predictive Analytics Dashboard - SARMAX | Prophet | LSTM Ensemble</p>
    </div>

    <!-- TABS -->
    <div class="card">
        <div class="tabs">
            <button class="tab-btn active" data-tab="overview">📊 Overview</button>
            <button class="tab-btn" data-tab="enrollments">👥 Enrollments</button>
            <button class="tab-btn" data-tab="add-enrollment">➕ Add Enrollment</button>
            <button class="tab-btn" data-tab="predictions">🔮 Single-Year Predictions</button>
            <button class="tab-btn" data-tab="multi-predictions">📈 Multi-Year Predictions</button>
            <button class="tab-btn" data-tab="model-details">🤖 Model Metrics</button>
        </div>
    </div>

    <!-- ===== OVERVIEW TAB ===== -->
    <div id="overview" class="tab-content active">

        <!-- SUMMARY -->
        <div class="summary-grid" id="summaryGrid"></div>

        <!-- COMBINED ALL PROGRAMS CHART -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
                <h2>📊 All Programs Combined Enrollment Trend</h2>
                <div class="select-container" style="margin:0;">
                    <select id="overviewYearFilter" style="flex:1;max-width:180px;">
                        <option value="">All Years</option>
                    </select>
                    <select id="overviewSemesterFilter" style="flex:1;max-width:160px;">
                        <option value="">All Semesters</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                    </select>
                    <button onclick="tracker.refreshCombinedChart()" style="margin:0;">🔄 Refresh</button>
                </div>
            </div>
            <div class="chart-container" style="height:400px;">
                <canvas id="combinedChart"></canvas>
            </div>
        </div>

        <!-- ALL PROGRAMS CHARTS -->
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

    <!-- ===== ENROLLMENTS TAB ===== -->
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

    <!-- ===== ADD ENROLLMENT TAB ===== -->
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

    <!-- ===== SINGLE-YEAR PREDICTIONS TAB ===== -->
    <div id="predictions" class="tab-content">
        <div class="card">
            <h2>🔮 Single-Year Predictions (2026-2027)</h2>

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

            <!-- Model Comparison -->
            <div id="modelComparisonContainer" style="margin-top:20px;display:none;">
                <h3 style="margin-bottom:20px;">📊 Model Predictions Comparison</h3>
                <div class="prediction-comparison-grid" id="modelComparisonGrid"></div>
            </div>

            <!-- Model Metrics -->
            <div id="modelMetricsContainer" style="margin-top:20px;display:none;">
                <h3 style="margin-bottom:20px;">📈 Model Performance Metrics</h3>
                <div class="model-metrics-grid" id="modelMetricsGrid"></div>
            </div>

            <div id="predictionStatsContainer" style="margin-top:20px;display:none;">
                <h3 style="margin-bottom:20px;">🔮 Ensemble Predictions</h3>
                <div class="predictions-grid" id="predictionsGrid"></div>
            </div>
        </div>
    </div>

    <!-- ===== MULTI-YEAR PREDICTIONS TAB ===== -->
    <div id="multi-predictions" class="tab-content">
        <div class="card">
            <h2>📈 Multi-Year Forecast</h2>
            <p style="color:#718096;margin-bottom:20px;">Select a program and number of years to forecast enrollment trends</p>

            <div class="form-row">
                <div class="form-group">
                    <label>Select Program <span style="color:red">*</span></label>
                    <select id="multiPredProgramFilter" style="width:100%;">
                        <option value="">Choose a program</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Forecast Years <span style="color:red">*</span></label>
                    <select id="multiPredYearsFilter" style="width:100%;">
                        <option value="1">1 Year Ahead (2026-2027)</option>
                        <option value="2">2 Years Ahead (2026-2028)</option>
                        <option value="3">3 Years Ahead (2026-2029)</option>
                        <option value="4">4 Years Ahead (2026-2030)</option>
                        <option value="5">5 Years Ahead (2026-2031)</option>
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end;gap:10px;">
                    <button onclick="tracker.loadMultiYearPredictions()">📊 Generate Forecast</button>
                    <button onclick="tracker.downloadForecastCSV()" class="btn-info">⬇️ Export CSV</button>
                </div>
            </div>

            <div id="multiPredChartContainer" style="margin-top:30px;display:none;">
                <div class="chart-container" style="height:450px;">
                    <canvas id="multiYearChart"></canvas>
                </div>
            </div>

            <div id="multiPredTableContainer" style="margin-top:30px;display:none;">
                <h3>📋 Detailed Forecast Data</h3>
                <div class="table-container">
                    <table id="multiPredTable">
                        <thead>
                            <tr>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th>SARMAX</th>
                                <th>Prophet</th>
                                <th>LSTM</th>
                                <th>Ensemble Avg</th>
                                <th>Confidence</th>
                            </tr>
                        </thead>
                        <tbody id="multiPredTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODEL DETAILS TAB ===== -->
    <div id="model-details" class="tab-content">
        <div class="card">
            <h2>🤖 Model Architecture & Performance</h2>
            <p style="color:#718096;margin-bottom:20px;">Detailed information about each forecasting model</p>

            <!-- SARMAX Model -->
            <div style="margin-bottom:30px;">
                <h3 style="margin-bottom:15px;color:#667eea;">📊 SARMAX (Seasonal ARIMA) Model</h3>
                <div class="model-metrics-grid">
                    <div class="model-card sarmax">
                        <h4><span class="model-badge sarmax">SARMAX</span></h4>
                        <div class="metric-item">
                            <span class="metric-label">Type:</span>
                            <span class="metric-value">Time Series</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Order (p,d,q):</span>
                            <span class="metric-value">(1,1,1)</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Seasonal (P,D,Q,s):</span>
                            <span class="metric-value">(1,1,1,3)</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Seasonality:</span>
                            <span class="metric-value">3 Semesters/Year</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Confidence Int:</span>
                            <span class="metric-value">95%</span>
                        </div>
                    </div>

                    <div class="model-card sarmax">
                        <h4>Key Metrics Explained</h4>
                        <div class="metric-item">
                            <span class="metric-label">MAE:</span>
                            <span class="metric-value">Mean Absolute Error</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">RMSE:</span>
                            <span class="metric-value">Root Mean Squared Error</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">MAPE:</span>
                            <span class="metric-value">Mean Absolute % Error</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">R²:</span>
                            <span class="metric-value">Coefficient Determination</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">RMSLE:</span>
                            <span class="metric-value">Root Mean Squared Log Error</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Theil-U:</span>
                            <span class="metric-value">Inequality Coefficient</span>
                        </div>
                    </div>
                </div>
                <p style="margin-top:15px;padding:15px;background:#f0f4ff;border-radius:8px;color:#667eea;font-size:13px;">
                    <strong>💡 Best For:</strong> Clear seasonal patterns, interpretable coefficients, fast training. 
                    <strong>Limitation:</strong> Assumes stationarity, struggles with complex non-linear patterns.
                </p>
            </div>

            <!-- Facebook Prophet Model -->
            <div style="margin-bottom:30px;">
                <h3 style="margin-bottom:15px;color:#f093fb;">📊 Facebook Prophet Model</h3>
                <div class="model-metrics-grid">
                    <div class="model-card prophet">
                        <h4><span class="model-badge prophet">PROPHET</span></h4>
                        <div class="metric-item">
                            <span class="metric-label">Type:</span>
                            <span class="metric-value">Trend + Seasonality</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Yearly Seasonality:</span>
                            <span class="metric-value">Disabled</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Weekly Seasonality:</span>
                            <span class="metric-value">Disabled</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Changepoint Detection:</span>
                            <span class="metric-value">Enabled</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Confidence Int:</span>
                            <span class="metric-value">95%</span>
                        </div>
                    </div>

                    <div class="model-card prophet">
                        <h4>Strengths & Applications</h4>
                        <div class="metric-item">
                            <span class="metric-label">✓ Robust:</span>
                            <span class="metric-value">Handles missing data</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">✓ Trend Detection:</span>
                            <span class="metric-value">Auto changepoints</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">✓ Decomposition:</span>
                            <span class="metric-value">Trend + Seasonality</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">✗ Limitation:</span>
                            <span class="metric-value">Slower training</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">✗ Limitation:</span>
                            <span class="metric-value">May underestimate CI</span>
                        </div>
                    </div>
                </div>
                <p style="margin-top:15px;padding:15px;background:#ffe6f5;border-radius:8px;color:#f093fb;font-size:13px;">
                    <strong>💡 Best For:</strong> Trend changes, business data, automatic decomposition. 
                    <strong>Training:</strong> 1-3 seconds per program using Stan sampling.
                </p>
            </div>

            <!-- LSTM Model -->
            <div style="margin-bottom:30px;">
                <h3 style="margin-bottom:15px;color:#ed8936;">📊 LSTM (Deep Learning) Model</h3>
                <div class="model-metrics-grid">
                    <div class="model-card lstm">
                        <h4><span class="model-badge lstm">LSTM</span></h4>
                        <div class="metric-item">
                            <span class="metric-label">Type:</span>
                            <span class="metric-value">Neural Network</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Architecture:</span>
                            <span class="metric-value">LSTM + Dense</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">LSTM Units:</span>
                            <span class="metric-value">32 Cells</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Sequence Length:</span>
                            <span class="metric-value">4 Timesteps</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">Dropout:</span>
                            <span class="metric-value">20%</span>
                        </div>
                    </div>

                    <div class="model-card lstm">
                        <h4>Deep Learning Features</h4>
                        <div class="metric-item">
                            <span class="metric-label">✓ Complex:</span>
                            <span class="metric-value">Learns dependencies</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">✓ Flexible:</span>
                            <span class="metric-value">No stationarity needed</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">✓ Powerful:</span>
                            <span class="metric-value">Non-linear patterns</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">✗ Data Hungry:</span>
                            <span class="metric-value">Needs 20+ observations</span>
                        </div>
                        <div class="metric-item">
                            <span class="metric-label">✗ Training Time:</span>
                            <span class="metric-value">10-30 sec/program</span>
                        </div>
                    </div>
                </div>
                <p style="margin-top:15px;padding:15px;background:#fff5e6;border-radius:8px;color:#ed8936;font-size:13px;">
                    <strong>💡 Best For:</strong> Complex patterns, large datasets, non-linear relationships. 
                    <strong>Trade-off:</strong> Less interpretable ("black box"), requires more data.
                </p>
            </div>

            <!-- Ensemble Approach -->
            <div>
                <h3 style="margin-bottom:15px;color:#2f855a;">📊 Ensemble Average (Recommended)</h3>
                <div style="background:#f0fdf4;border:2px solid #86efac;border-radius:12px;padding:20px;">
                    <p style="margin-bottom:15px;">
                        <strong>🎯 Strategy:</strong> Combines predictions from all three models using simple averaging.
                    </p>
                    <p style="margin-bottom:15px;">
                        <strong>✅ Benefits:</strong>
                    </p>
                    <ul style="margin-left:20px;margin-bottom:15px;">
                        <li>Reduces variance from individual model errors</li>
                        <li>More robust to model-specific failures</li>
                        <li>Captures strengths of all approaches</li>
                        <li>Better generalization than single model</li>
                    </ul>
                    <p style="margin-bottom:0;">
                        <strong>📊 Confidence Scoring:</strong> Average R² from successful models indicates reliability
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- EDIT ENROLLMENT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>✏️ Edit Enrollment</h2>
            <span class="close-modal" onclick="tracker.closeEditModal()">&times;</span>
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
                <button type="button" onclick="tracker.closeEditModal()" class="btn-secondary">Cancel</button>
            </div>
        </form>

        <div id="editStatus" class="status"></div>
    </div>
</div>

<?php endif; ?>

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
        this.multiYearChart = null;
        this.combinedChart = null;
        this.allEnrollments = [];
        this.allPrograms = [];
        this.allPredictions = [];
        this.allModelMetrics = [];
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
                    document.getElementById('modelComparisonContainer').style.display = 'none';
                    document.getElementById('modelMetricsContainer').style.display = 'none';
                    document.getElementById('predictionStatsContainer').style.display = 'none';
                }else if(tabId === 'multi-predictions'){
                    document.getElementById('multiPredChartContainer').style.display = 'none';
                    document.getElementById('multiPredTableContainer').style.display = 'none';
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

            const selects=['formProgram','enrollProgramFilter','predProgramFilter','multiPredProgramFilter'];
            selects.forEach(selectId => {
                const select=document.getElementById(selectId);
                if(select){
                    const html='<option value="">All Programs</option>'+
                        this.allPrograms.map(p=>`<option value="${p.id}">${p.name}</option>`).join('');
                    select.innerHTML=html;
                }
            });

            await this.loadYears();
            await this.loadPredictions();
            await this.loadModelMetrics();
            this.loadAllProgramsCharts();
            this.refreshCombinedChart();

        }catch(e){
            this.showStatus('Failed to load programs: '+e.message,'error');
        }
    }

    async loadYears(){
        try{
            const res=await fetch('api/enrollments.php');
            if(!res.ok) throw new Error(`HTTP ${res.status}`);

            this.allEnrollments=await res.json();
            
            const validData = this.allEnrollments.filter(e => {
                const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1;
            });
            
            const years = [...new Set(validData.map(e => e.academic_year))].sort().reverse();

            const enrollYearFilter = document.getElementById('enrollYearFilter');
            const yearHtml = years.map(y => `<option value="${y}">${y}</option>`).join('');
            
            if(enrollYearFilter) enrollYearFilter.innerHTML = '<option value="">All Years</option>' + yearHtml;

            const overviewYearFilter = document.getElementById('overviewYearFilter');
            if(overviewYearFilter) overviewYearFilter.innerHTML = '<option value="">All Years</option>' + yearHtml;

        }catch(e){
            console.error('Failed to load years:', e);
        }
    }

    async loadPredictions(){
        try{
            const res = await fetch('api/predictions.php');
            if(res.ok){
                this.allPredictions = await res.json();
            }
        }catch(e){
            console.error('Failed to load predictions:', e);
        }
    }

    async loadModelMetrics(){
        try{
            const res = await fetch('api/model-metrics.php');
            if(res.ok){
                this.allModelMetrics = await res.json();
            }
        }catch(e){
            console.error('Failed to load model metrics:', e);
        }
    }

    bindEvents(){
        document.getElementById('enrollProgramFilter')
            .addEventListener('change',()=>this.refreshEnrollmentsTable());

        document.getElementById('enrollYearFilter')
            .addEventListener('change',()=>this.refreshEnrollmentsTable());

        document.getElementById('overviewYearFilter')
            .addEventListener('change',()=>this.refreshCombinedChart());

        document.getElementById('overviewSemesterFilter')
            .addEventListener('change',()=>this.refreshCombinedChart());

        document.getElementById('predProgramFilter')
            .addEventListener('change',()=>{
                document.getElementById('predictionChartContainer').style.display = 'none';
                document.getElementById('modelComparisonContainer').style.display = 'none';
                document.getElementById('modelMetricsContainer').style.display = 'none';
                document.getElementById('predictionStatsContainer').style.display = 'none';
            });

        document.getElementById('logoutBtn')
            .addEventListener('click',()=>{
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
                const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
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
                const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1;
            });

            if(yearFilter){
                filtered = filtered.filter(e => e.academic_year === yearFilter);
            }

            if(semFilter){
                if(semFilter === '1' || semFilter === '2' || semFilter === '3'){
                    filtered = filtered.filter(e => parseInt(e.semester) === parseInt(semFilter));
                }
            }

            const aggregate = {};
            filtered.forEach(e => {
                let key = e.academic_year;
                if(!semFilter){
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
                    semester = parts[1];
                }
                return {
                    academic_year,
                    semester,
                    ...aggregate[key]
                };
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

        const labels = chartData.map(e=>{
            if(e.semester != null){
                return `${e.academic_year} S${e.semester}`;
            }
            return `${e.academic_year}`;
        });
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
                labels: labels,
                datasets: [
                    {
                        label: 'Total Enrollment',
                        data: totals,
                        borderColor: '#ed8936',
                        backgroundColor: 'rgba(237, 137, 54, 0.15)',
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 8,
                        pointBackgroundColor: '#ed8936',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Male Students',
                        data: males,
                        borderColor: '#2b6cb0',
                        backgroundColor: 'rgba(43, 108, 176, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
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
                        pointRadius: 6,
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
                        font: {weight: 'bold', size: 12},
                        color: '#2d3748',
                        backgroundColor: 'rgba(255,255,255,0.95)',
                        borderRadius: 4,
                        padding: 6,
                        anchor: 'end',
                        align: 'top'
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value){
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    async loadAllProgramsCharts(){
        try{
            this.showStatus('Loading all programs charts...');

            const res = await fetch('api/enrollments.php');
            if(!res.ok) throw new Error(`HTTP ${res.status}`);
            
            const allData = await res.json();

            this.renderSummary(allData);

            const grid = document.getElementById('programsChartsGrid');
            grid.innerHTML = '';

            for(let programId of Object.keys(programNames).map(Number)){
                const programData = allData.filter(e => {
                    const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
                    return (endYear - startYear) === 1 && e.program_id == programId;
                });

                if(programData.length === 0) continue;

                const card = document.createElement('div');
                card.className = 'program-chart-card';
                
                const total = programData.reduce((sum,e) => sum + (e.male||0) + (e.female||0), 0);
                const totalMale = programData.reduce((sum,e) => sum + (e.male||0), 0);
                const totalFemale = programData.reduce((sum,e) => sum + (e.female||0), 0);

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
                }, 100);
            }

            this.showStatus('All programs charts loaded');

        }catch(e){
            this.showStatus('Error loading charts: '+e.message,'error');
        }
    }

    createProgramChart(programId, programData){
        programData.sort((a,b)=>{
            const yearA=parseInt(a.academic_year.split('-')[0]);
            const yearB=parseInt(b.academic_year.split('-')[0]);
            return yearA-yearB||a.semester-b.semester;
        });

        const labels = programData.map(e=>`${e.academic_year} S${e.semester}`);
        const totals = programData.map(e=>(e.male||0)+(e.female||0));
        const males = programData.map(e=>e.male||0);
        const females = programData.map(e=>e.female||0);

        const ctx = document.getElementById(`chart-prog-${programId}`);
        if(!ctx) return;

        if(this.charts[programId]){
            this.charts[programId].destroy();
        }

        this.charts[programId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total',
                        data: totals,
                        borderColor: '#ed8936',
                        backgroundColor: 'rgba(237, 137, 54, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointBackgroundColor: '#ed8936',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Male',
                        data: males,
                        borderColor: '#2b6cb0',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#2b6cb0'
                    },
                    {
                        label: 'Female',
                        data: females,
                        borderColor: '#d53f8c',
                        borderWidth: 2,
                        fill: false,
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
                        font: {weight: 'bold', size: 12},
                        color: '#2d3748',
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        borderRadius: 4,
                        padding: 6,
                        anchor: 'end',
                        align: 'top'
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value){
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    renderSummary(data){
        const grid = document.getElementById('summaryGrid');

        const total = data.reduce((sum,e) => sum + (e.male||0) + (e.female||0), 0);
        const totalMale = data.reduce((sum,e) => sum + (e.male||0), 0);
        const totalFemale = data.reduce((sum,e) => sum + (e.female||0), 0);

        const validData = data.filter(e => {
            const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
            return (endYear - startYear) === 1;
        });

        const years = new Set(validData.map(e => e.academic_year)).size;
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
                const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
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
                            valA = a.semester;
                            valB = b.semester;
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
                    <td><strong>${parseInt(e.male) + parseInt(e.female)}</strong></td>
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

        if(!this.editingRecord){
            return;
        }

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
                setTimeout(() => {
                    this.closeEditModal();
                    this.loadYears();
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
            const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
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
                    <div><strong>${parseInt(e.male||0) + parseInt(e.female||0)}</strong> students</div>
                    <div class="recent-info">${e.male||0}M / ${e.female||0}F</div>
                </div>
            </div>
        `).join('');
    }

    async refreshPredictions(){
        try{
            const programFilter = document.getElementById('predProgramFilter').value;

            if(!programFilter){
                this.showStatus('Please select a program','error','status');
                return;
            }

            const allData = this.allEnrollments.filter(e => {
                const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
                return (endYear - startYear) === 1 && e.program_id == programFilter;
            });

            if(allData.length === 0){
                this.showStatus('No historical data for this program','error','status');
                return;
            }

            const predictions = this.allPredictions.filter(p => p.program_id == programFilter);
            const metrics = this.allModelMetrics.filter(m => m.program_id == programFilter);

            this.createPredictionChart(programFilter, allData, predictions);
            this.displayModelComparison(programFilter, predictions, metrics);
            this.displayModelMetrics(metrics);
            this.displayPredictionStats(predictions);

        }catch(e){
            this.showStatus('Error loading predictions: '+e.message,'error','status');
        }
    }

    createPredictionChart(programId, historicalData, predictions){
        historicalData.sort((a,b)=>{
            const yearA=parseInt(a.academic_year.split('-')[0]);
            const yearB=parseInt(b.academic_year.split('-')[0]);
            return yearA-yearB||a.semester-b.semester;
        });

        const labels = historicalData.map(e=>`${e.academic_year} S${e.semester}`);
        const totals = historicalData.map(e=>(e.male||0)+(e.female||0));
        const males = historicalData.map(e=>e.male||0);
        const females = historicalData.map(e=>e.female||0);

        let allLabels = [...labels];
        let allTotals = [...totals];
        let allMales = [...males];
        let allFemales = [...females];

        if(predictions && predictions.length > 0){
            const pred = predictions[0];
            allLabels.push(`${pred.academic_year} (Pred)`);
            allTotals.push(pred.predicted_total);
            allMales.push(pred.predicted_male || 0);
            allFemales.push(pred.predicted_female || 0);
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
                        pointRadius: 6,
                        pointBackgroundColor: '#ed8936',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Male',
                        data: allMales,
                        borderColor: '#2b6cb0',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#2b6cb0'
                    },
                    {
                        label: 'Female',
                        data: allFemales,
                        borderColor: '#d53f8c',
                        borderWidth: 2,
                        fill: false,
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
                        font: {weight: 'bold', size: 12},
                        color: '#2d3748',
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        borderRadius: 4,
                        padding: 6,
                        anchor: 'end',
                        align: 'top'
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value){
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        document.getElementById('predictionChartContainer').style.display = 'block';
    }

    displayModelComparison(programId, predictions, metrics){
        const container = document.getElementById('modelComparisonContainer');
        const grid = document.getElementById('modelComparisonGrid');

        if(predictions.length === 0){
            grid.innerHTML = '<div class="text-center" style="grid-column:1/-1;">No predictions available</div>';
            container.style.display = 'block';
            return;
        }

        // Group predictions by model
        const modelPredictions = {
            sarmax: predictions.find(p => p.model_name === 'SARMAX')?.predicted_total,
            prophet: predictions.find(p => p.model_name === 'Prophet')?.predicted_total,
            lstm: predictions.find(p => p.model_name === 'LSTM')?.predicted_total,
            ensemble: predictions.find(p => p.model_name === 'Ensemble')?.predicted_total
        };

        const semesterMap = {1:'First',2:'Second',3:'Summer'};
        const firstPred = predictions[0];

        grid.innerHTML = `
            <div class="prediction-comparison-card">
                <h4>📊 SARMAX Model</h4>
                <div class="prediction-row">
                    <span class="prediction-row-label">Prediction (${firstPred.academic_year})</span>
                    <span class="prediction-row-value">${modelPredictions.sarmax || '—'} students</span>
                </div>
            </div>

            <div class="prediction-comparison-card">
                <h4>📊 Prophet Model</h4>
                <div class="prediction-row">
                    <span class="prediction-row-label">Prediction (${firstPred.academic_year})</span>
                    <span class="prediction-row-value">${modelPredictions.prophet || '—'} students</span>
                </div>
            </div>

            <div class="prediction-comparison-card">
                <h4>📊 LSTM Model</h4>
                <div class="prediction-row">
                    <span class="prediction-row-label">Prediction (${firstPred.academic_year})</span>
                    <span class="prediction-row-value">${modelPredictions.lstm || '—'} students</span>
                </div>
            </div>

            <div class="prediction-comparison-card" style="background:linear-gradient(135deg,#2f855a 0%,#22543d 100%);">
                <h4>✨ Ensemble Average</h4>
                <div class="prediction-row">
                    <span class="prediction-row-label">Consensus Prediction</span>
                    <span class="prediction-row-value">${modelPredictions.ensemble || '—'} students</span>
                </div>
                <div class="prediction-row">
                    <span class="prediction-row-label">Confidence</span>
                    <span class="prediction-row-value">${(firstPred.confidence * 100).toFixed(0)}%</span>
                </div>
            </div>
        `;

        container.style.display = 'block';
    }

    displayModelMetrics(metrics){
        const container = document.getElementById('modelMetricsContainer');
        const grid = document.getElementById('modelMetricsGrid');

        if(metrics.length === 0){
            grid.innerHTML = '<div class="text-center" style="grid-column:1/-1;">No metrics available</div>';
            container.style.display = 'block';
            return;
        }

        // Group metrics by model
        const sarmaxMetrics = metrics.filter(m => m.model_name === 'SARMAX');
        const prophetMetrics = metrics.filter(m => m.model_name === 'Prophet');
        const lstmMetrics = metrics.filter(m => m.model_name === 'LSTM');

        const createMetricsCard = (modelName, modelMetrics, modelClass) => {
            const metricsMap = {};
            modelMetrics.forEach(m => {
                metricsMap[m.metric_name] = m.metric_value;
            });

            return `
                <div class="model-card ${modelClass}">
                    <h4><span class="model-badge ${modelClass}">${modelName}</span></h4>
                    <div class="metric-item">
                        <span class="metric-label">MAE:</span>
                        <span class="metric-value">${metricsMap['MAE'] || '—'}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">RMSE:</span>
                        <span class="metric-value">${metricsMap['RMSE'] || '—'}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">MAPE:</span>
                        <span class="metric-value">${metricsMap['MAPE'] || '—'}%</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">R²:</span>
                        <span class="metric-value">${metricsMap['R²'] || '—'}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">RMSLE:</span>
                        <span class="metric-value">${metricsMap['RMSLE'] || '—'}</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Theil-U:</span>
                        <span class="metric-value">${metricsMap['Theil_U'] || '—'}</span>
                    </div>
                </div>
            `;
        };

        grid.innerHTML = `
            ${createMetricsCard('SARMAX', sarmaxMetrics, 'sarmax')}
            ${createMetricsCard('Prophet', prophetMetrics, 'prophet')}
            ${createMetricsCard('LSTM', lstmMetrics, 'lstm')}
        `;

        container.style.display = 'block';
    }

    displayPredictionStats(predictions){
        const container = document.getElementById('predictionStatsContainer');
        const grid = document.getElementById('predictionsGrid');

        if(predictions.length === 0){
            grid.innerHTML = '<div class="text-center" style="grid-column:1/-1;">No predictions available</div>';
            container.style.display = 'block';
            return;
        }

        const programMap = {};
        this.allPrograms.forEach(p => {
            programMap[p.id] = p.name;
        });

        const semesterMap = {1:'First',2:'Second',3:'Summer'};

        grid.innerHTML = predictions.map(p => `
            <div class="prediction-card">
                <h4>📊 ${semesterMap[p.semester] || p.semester}</h4>
                <div class="prediction-item">
                    <span class="prediction-label">Academic Year</span>
                    <span class="prediction-value">${p.academic_year}</span>
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
                    <span class="prediction-value">${(p.confidence * 100).toFixed(0)}%</span>
                </div>
                <div class="prediction-item">
                    <span class="prediction-label">Model Ensemble</span>
                    <span class="prediction-value" style="font-size:11px;">${p.model_ensemble || 'SARMAX+Prophet+LSTM'}</span>
                </div>
            </div>
        `).join('');

        container.style.display = 'block';
    }

    async loadMultiYearPredictions(){
        try{
            const programId = document.getElementById('multiPredProgramFilter').value;
            const yearsAhead = document.getElementById('multiPredYearsFilter').value;

            if(!programId){
                this.showStatus('Please select a program','error');
                return;
            }

            const predictions = this.allPredictions.filter(p => 
                p.program_id == programId && 
                this.isInForecastRange(p.academic_year, yearsAhead)
            );

            if(predictions.length === 0){
                this.showStatus('No predictions available for selected range','error');
                return;
            }

            this.createMultiYearChart(programId, predictions);
            this.displayMultiYearTable(predictions);

        }catch(e){
            this.showStatus('Error loading multi-year predictions: '+e.message,'error');
        }
    }

    isInForecastRange(academicYear, yearsAhead){
        const [startYear] = academicYear.split('-').map(y => parseInt(y));
        const baseYear = 2026;
        const maxYear = baseYear + parseInt(yearsAhead) - 1;
        return startYear >= baseYear && startYear <= maxYear;
    }

    createMultiYearChart(programId, predictions){
        predictions.sort((a,b)=>{
            const yearA = parseInt(a.academic_year.split('-')[0]);
            const yearB = parseInt(b.academic_year.split('-')[0]);
            return yearA - yearB || a.semester - b.semester;
        });

        const labels = predictions.map(p => `${p.academic_year} S${p.semester}`);
        const ensemble = predictions.map(p => p.predicted_total);
        const males = predictions.map(p => p.predicted_male || 0);
        const females = predictions.map(p => p.predicted_female || 0);

        const ctx = document.getElementById('multiYearChart');
        if(!ctx) return;

        if(this.multiYearChart){
            this.multiYearChart.destroy();
        }

        const programName = programNames[programId] || `Program ${programId}`;
        
        this.multiYearChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ensemble Forecast',
                        data: ensemble,
                        borderColor: '#2f855a',
                        backgroundColor: 'rgba(47, 133, 90, 0.15)',
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 8,
                        pointBackgroundColor: '#2f855a',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Male Students',
                        data: males,
                        borderColor: '#2b6cb0',
                        backgroundColor: 'rgba(43, 108, 176, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
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
                        pointRadius: 6,
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
                        font: {weight: 'bold', size: 12},
                        color: '#2d3748',
                        backgroundColor: 'rgba(255,255,255,0.95)',
                        borderRadius: 4,
                        padding: 6,
                        anchor: 'end',
                        align: 'top'
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value){
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        document.getElementById('multiPredChartContainer').style.display = 'block';
    }

    displayMultiYearTable(predictions){
        const tbody = document.getElementById('multiPredTableBody');

        if(predictions.length === 0){
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No data</td></tr>';
            document.getElementById('multiPredTableContainer').style.display = 'block';
            return;
        }

        // Group by year for better visibility
        predictions.sort((a,b)=>{
            const yearA = parseInt(a.academic_year.split('-')[0]);
            const yearB = parseInt(b.academic_year.split('-')[0]);
            return yearA - yearB || a.semester - b.semester;
        });

        tbody.innerHTML = predictions.map(p => `
            <tr>
                <td><strong>${p.academic_year}</strong></td>
                <td>Semester ${p.semester}</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td><strong>${p.predicted_total}</strong></td>
                <td>${(p.confidence * 100).toFixed(0)}%</td>
            </tr>
        `).join('');

        document.getElementById('multiPredTableContainer').style.display = 'block';
    }

    downloadForecastCSV(){
        const programId = document.getElementById('multiPredProgramFilter').value;
        const yearsAhead = document.getElementById('multiPredYearsFilter').value;

        if(!programId){
            this.showStatus('Please select a program','error');
            return;
        }

        const predictions = this.allPredictions.filter(p => 
            p.program_id == programId && 
            this.isInForecastRange(p.academic_year, yearsAhead)
        );

        if(predictions.length === 0){
            this.showStatus('No data to export','error');
            return;
        }

        let csv = 'Academic Year,Semester,Program,Predicted Total,Male,Female,Confidence\n';
        
        const programMap = {};
        this.allPrograms.forEach(p => {
            programMap[p.id] = p.name;
        });

        predictions.forEach(p => {
            csv += `${p.academic_year},${p.semester},${programMap[p.program_id]},${p.predicted_total},${p.predicted_male},${p.predicted_female},${(p.confidence*100).toFixed(0)}%\n`;
        });

        const blob = new Blob([csv], {type: 'text/csv'});
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `predictions-${programId}-${yearsAhead}years.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }
}

/* LOGIN */
document.getElementById('loginForm')?.addEventListener('submit',e=>{
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