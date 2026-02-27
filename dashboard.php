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
}

.header-banner h1{
    font-size:28px;
    font-weight:700;
    margin-bottom:5px;
}

.header-banner p{
    opacity:.9;
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

select,input,button{
    padding:12px 18px;
    border-radius:12px;
    border:1px solid #e2e8f0;
    font-size:15px;
    font-family:inherit;
}

select:focus,input:focus{
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
}

.form-actions{
    display:flex;
    gap:10px;
    margin-top:25px;
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

    <!-- NOTICE -->
    <div class="notice">
        <strong>📢 Notice:</strong> Dashboard v2 - All programs overview with predictions
    </div>

    <!-- HEADER -->
    <div class="header-banner">
        <h1>📊 Enrollment Tracker System</h1>
        <p>Predictive analytics for CAS programs | All Programs Dashboard</p>
    </div>

    <!-- TABS -->
    <div class="card">
        <div class="tabs">
            <button class="tab-btn active" data-tab="overview">📊 Overview (All Programs)</button>
            <button class="tab-btn" data-tab="enrollments">👥 Enrollments</button>
            <button class="tab-btn" data-tab="add-enrollment">➕ Add Enrollment</button>
            <button class="tab-btn" data-tab="predictions">🔮 Predictions</button>
        </div>
    </div>

    <!-- ===== OVERVIEW TAB ===== -->
    <div id="overview" class="tab-content active">

        <!-- SUMMARY -->
        <div class="summary-grid" id="summaryGrid"></div>

        <!-- ALL PROGRAMS CHARTS -->
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h2>📈 Enrollment Overview by Program</h2>
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
                            <th>Program</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Total</th>
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
            <h2>➕ Add New Enrollment</h2>
            
            <form id="addEnrollmentForm" onsubmit="tracker.handleAddEnrollment(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Program <span style="color:red">*</span></label>
                        <select id="formProgram" required>
                            <option value="">Select a program</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Academic Year <span style="color:red">*</span></label>
                        <input type="text" id="formYear" placeholder="e.g., 2024-2025" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Semester <span style="color:red">*</span></label>
                        <select id="formSemester" required>
                            <option value="">Select semester</option>
                            <option value="1">First</option>
                            <option value="2">Second</option>
                            <option value="3">Summer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Male Students <span style="color:red">*</span></label>
                        <input type="number" id="formMale" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Female Students <span style="color:red">*</span></label>
                        <input type="number" id="formFemale" min="0" required>
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

    <!-- ===== PREDICTIONS TAB ===== -->
    <div id="predictions" class="tab-content">
        <div class="card">
            <h2>🔮 Enrollment Predictions</h2>

            <div class="select-container">
                <select id="predProgramFilter">
                    <option value="">All Programs</option>
                </select>
                <select id="predYearFilter">
                    <option value="">All Years</option>
                </select>
                <button onclick="tracker.refreshPredictions()">🔄 Refresh</button>
            </div>

            <div class="predictions-grid" id="predictionsGrid">
                <div class="text-center" style="padding:40px;grid-column:1/-1;">Loading predictions...</div>
            </div>
        </div>
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
        this.allEnrollments = [];
        this.allPrograms = [];
        this.allPredictions = [];
        this.init();
    }

    init(){
<?php if (isset($_GET['login'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('dashboard').style.display='block';
            this.setupTabs();
            this.loadPrograms();
            this.bindEvents();
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
                    this.refreshPredictions();
                }else if(tabId === 'overview'){
                    this.loadAllProgramsCharts();
                }
            });
        });
    }

    showStatus(msg,type='success',elementId='status'){
        const status=document.getElementById(elementId);
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

            const selects=['formProgram','enrollProgramFilter','predProgramFilter'];
            selects.forEach(selectId => {
                const select=document.getElementById(selectId);
                if(select){
                    const html='<option value="">All Programs</option>'+
                        this.allPrograms.map(p=>`<option value="${p.id}">${p.name}</option>`).join('');
                    select.innerHTML=html;
                }
            });

            await this.loadYears();
            this.loadAllProgramsCharts();

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
            const predYearFilter = document.getElementById('predYearFilter');

            const yearHtml = years.map(y => `<option value="${y}">${y}</option>`).join('');
            
            if(enrollYearFilter) enrollYearFilter.innerHTML = '<option value="">All Years</option>' + yearHtml;
            if(predYearFilter) predYearFilter.innerHTML = '<option value="">All Years</option>' + yearHtml;

        }catch(e){
            console.error('Failed to load years:', e);
        }
    }

    bindEvents(){
        document.getElementById('enrollProgramFilter')
            .addEventListener('change',()=>this.refreshEnrollmentsTable());

        document.getElementById('enrollYearFilter')
            .addEventListener('change',()=>this.refreshEnrollmentsTable());

        document.getElementById('predProgramFilter')
            .addEventListener('change',()=>this.refreshPredictions());

        document.getElementById('predYearFilter')
            .addEventListener('change',()=>this.refreshPredictions());

        document.getElementById('logoutBtn')
            .addEventListener('click',()=>{
                if(confirm('Are you sure you want to logout?')){
                    window.location.href = 'api/logout.php';
                }
            });
    }

    async loadAllProgramsCharts(){
        try{
            this.showStatus('Loading all programs charts...');

            const res = await fetch('api/enrollments.php');
            if(!res.ok) throw new Error(`HTTP ${res.status}`);
            
            const allData = await res.json();

            // Fetch predictions
            const predRes = await fetch('api/predictions.php');
            if(predRes.ok){
                this.allPredictions = await predRes.json();
            }

            // Calculate summary
            this.renderSummary(allData);

            // Create charts for each program
            const grid = document.getElementById('programsChartsGrid');
            grid.innerHTML = '';

            for(let programId of Object.keys(programNames).map(Number)){
                const programData = allData.filter(e => {
                    const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
                    return (endYear - startYear) === 1 && e.program_id == programId;
                });

                if(programData.length === 0) continue;

                // Create card for this program
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

                // Create chart
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
        const males = programData.map(e=>e.male||0);
        const females = programData.map(e=>e.female||0);
        const totals = programData.map(e=>(e.male||0)+(e.female||0));

        const ctx = document.getElementById(`chart-prog-${programId}`);
        if(!ctx) return;

        if(this.charts[programId]){
            this.charts[programId].destroy();
        }

        this.charts[programId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Male',
                        data: males,
                        backgroundColor: 'rgba(43, 108, 176, 0.8)',
                        borderColor: 'rgba(43, 108, 176, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Female',
                        data: females,
                        backgroundColor: 'rgba(213, 63, 140, 0.8)',
                        borderColor: 'rgba(213, 63, 140, 1)',
                        borderWidth: 1
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
                        color: 'white'
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

            tbody.innerHTML = filtered.map(e => `
                <tr>
                    <td>${programMap[e.program_id] || e.program_id}</td>
                    <td>${e.academic_year}</td>
                    <td>${semesterMap[e.semester] || e.semester}</td>
                    <td><strong>${e.male}</strong></td>
                    <td><strong>${e.female}</strong></td>
                    <td><strong>${parseInt(e.male) + parseInt(e.female)}</strong></td>
                    <td>
                        <button class="btn-danger btn-small" onclick="tracker.deleteEnrollment(${e.id})">🗑️ Delete</button>
                    </td>
                </tr>
            `).join('');
        }catch(e){
            this.showStatus('Error loading enrollments: '+e.message,'error');
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
            const yearFilter = document.getElementById('predYearFilter').value;

            let filtered = [...this.allPredictions];

            if(programFilter){
                filtered = filtered.filter(p => p.program_id == programFilter);
            }
            if(yearFilter){
                filtered = filtered.filter(p => p.academic_year === yearFilter);
            }

            const grid = document.getElementById('predictionsGrid');
            
            if(filtered.length === 0){
                grid.innerHTML = '<div class="text-center" style="padding:40px;grid-column:1/-1;">No predictions found</div>';
                return;
            }

            const programMap = {};
            this.allPrograms.forEach(p => {
                programMap[p.id] = p.name;
            });

            const semesterMap = {1:'First',2:'Second',3:'Summer'};

            grid.innerHTML = filtered.map(p => `
                <div class="prediction-card">
                    <h4>${programMap[p.program_id] || p.program_id}</h4>
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
                        <span class="prediction-value">${(p.confidence * 100).toFixed(0)}%</span>
                    </div>
                </div>
            `).join('');
        }catch(e){
            this.showStatus('Error loading predictions','error');
        }
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