<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrollment Tracker Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box}

body{
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    background:#f5f7fb;
    color:#2d3748;
}

/* ===== LAYOUT ===== */

.container{
    max-width:1200px;
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
}

.header-banner p{
    opacity:.9;
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

/* ===== CONTROLS ===== */

.select-container{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    align-items: center;
}

#customYearRange{
    display: flex;
    gap: 8px;
}

select,button{
    padding:12px 18px;
    border-radius:12px;
    border:1px solid #e2e8f0;
    font-size:15px;
}

button{
    background:#3182ce;
    color:white;
    border:none;
    cursor:pointer;
    font-weight:600;
}

button:hover{opacity:.9}

/* ===== STATUS ===== */

.status{
    margin-top:15px;
    padding:14px;
    border-radius:10px;
}

.success{background:#e6fffa;color:#065f46}
.error{background:#fff5f5;color:#742a2a}

/* ===== SUMMARY CARDS ===== */

.summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-top:25px;
}

.summary-card{
    background:#f8fafc;
    border-radius:16px;
    padding:22px;
}

.summary-card h3{
    font-size:34px;
    margin-bottom:5px;
}

.summary-card p{
    color:#718096;
}

/* ===== CHART ===== */

.chart-container{
    height:450px;
    margin-top:20px;
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

#loginForm input{
    width:100%;
    margin:15px 0;
    padding:12px;
}
</style>
</head>

<body>

<?php if (!isset($_GET['login'])): ?>

<!-- LOGIN -->
<div id="loginForm">
    <h2>Enrollment Tracker</h2>
    <form id="login">
        <input type="password" id="password" placeholder="Enter password" required>
        <button type="submit">Login to Dashboard</button>
    </form>
</div>

<?php else: ?>

<!-- DASHBOARD -->
<div class="container" id="dashboard" style="display:none;">

    <!-- NOTICE -->
    <div class="notice">
        <strong>Notice:</strong> System is currently in testing. Some features may not yet be available.
    </div>

    <!-- HEADER -->
    <div class="header-banner">
        <h1>Enrollment Tracker System</h1>
        <p>Predictive analytics for CAS programs</p>
    </div>

    <!-- CONTROLS -->
    <div class="card">
        <div class="select-container">
            <select id="programSelect">
                <option value="">Loading programs...</option>
            </select>
            <select id="yearFilter">
                <option value="all">All Years</option>
                <option value="1year">Last 1 Year</option>
                <option value="3years">Last 3 Years</option>
                <option value="5years">Last 5 Years</option>
                <option value="custom">Custom Range</option>
            </select>
            <div id="customYearRange" style="display: none;">
                <select id="startYear">
                    <option value="">Start Year</option>
                </select>
                <select id="endYear">
                    <option value="">End Year</option>
                </select>
            </div>
            <select id="semesterFilter">
                <option value="all">All Semesters</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
                <option value="3">Summer Class</option>
                <option value="1and2">Semesters 1 & 2</option>
            </select>
            <button id="refreshBtn">Refresh</button>
        </div>

        <div id="status" class="status" style="display:none"></div>
    </div>

    <!-- SUMMARY -->
    <div class="summary-grid" id="summaryGrid"></div>

    <!-- CHART -->
    <div class="card">
        <h2 style="margin-bottom:10px">Enrollment Trends</h2>
        <div class="chart-container">
            <canvas id="enrollmentChart"></canvas>
        </div>
    </div>

</div>

<?php endif; ?>

<script>
class EnrollmentTracker{
constructor(){
    this.chart=null;
    this.init();
}

init(){
<?php if (isset($_GET['login'])): ?>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('dashboard').style.display='block';
        this.loadPrograms();
        this.bindEvents();
    });
<?php endif; ?>
}

showStatus(msg,type='success'){
    const status=document.getElementById('status');
    status.textContent=msg;
    status.className=`status ${type}`;
    status.style.display='block';
    setTimeout(()=>status.style.display='none',5000);
}

async loadPrograms(){
try{
    const res=await fetch('api/programs.php');
    if(!res.ok) throw new Error(`HTTP ${res.status}`);

    const programs=await res.json();

    const select=document.getElementById('programSelect');
    select.innerHTML='<option value="">All Programs</option>'+
        programs.map(p=>`<option value="${p.id}">${p.name}</option>`).join('');

    // Load years for custom range
    await this.loadYears();

    this.showStatus('Programs loaded');
}catch(e){
    this.showStatus('Failed to load programs: '+e.message,'error');
}
}

async loadYears(){
try{
    const res=await fetch('api/enrollments.php');
    if(!res.ok) throw new Error(`HTTP ${res.status}`);

    const data=await res.json();
    // Filter out prediction-like data (same start year with different end years)
    const validData = data.filter(e => {
        const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
        return (endYear - startYear) === 1;
    });
    
    const years = [...new Set(validData.map(e => parseInt(e.academic_year.split('-')[1])))].sort((a,b)=>b-a);

    const startYearSelect = document.getElementById('startYear');
    const endYearSelect = document.getElementById('endYear');

    startYearSelect.innerHTML = '<option value="">Start Year</option>' +
        years.map(y => `<option value="${y}">${y}</option>`).join('');
    endYearSelect.innerHTML = '<option value="">End Year</option>' +
        years.map(y => `<option value="${y}">${y}</option>`).join('');
}catch(e){
    console.error('Failed to load years:', e);
}
}

bindEvents(){
document.getElementById('programSelect')
.addEventListener('change',e=>this.loadData(e.target.value));

document.getElementById('yearFilter')
.addEventListener('change',e=>{
    const customRange = document.getElementById('customYearRange');
    if(e.target.value === 'custom'){
        customRange.style.display = 'flex';
    }else{
        customRange.style.display = 'none';
        this.loadData(document.getElementById('programSelect').value);
    }
});

document.getElementById('startYear')
.addEventListener('change',()=>this.loadData(document.getElementById('programSelect').value));

document.getElementById('endYear')
.addEventListener('change',()=>this.loadData(document.getElementById('programSelect').value));

document.getElementById('semesterFilter')
.addEventListener('change',()=>this.loadData(document.getElementById('programSelect').value));

document.getElementById('refreshBtn')
.addEventListener('click',()=>this.loadData(
document.getElementById('programSelect').value));
}

async loadData(programId){
try{
    this.showStatus('Loading data...');

    const enrollRes=await fetch(
`api/enrollments.php${programId?'?program_id='+programId:''}`);

    if(!enrollRes.ok) throw new Error(`HTTP ${enrollRes.status}`);
    const data=await enrollRes.json();

    if(!data||data.length===0){
        this.showStatus('No data found','error');
        return;
    }

    let predTotal=null;
    if(programId){
        try{
            const predRes=await fetch(`api/predictions.php?program_id=${programId}`);
            if(predRes.ok){
                const predData=await predRes.json();
                predTotal=predData[0]?.predicted_total||null;
            }
        }catch{}
    }

    const filteredData = this.filterData(data);

    this.renderSummary(filteredData);
    this.renderChart(filteredData,predTotal,programId);
    this.showStatus('Dashboard updated');

}catch(e){
    this.showStatus('Load failed: '+e.message,'error');
}
}

filterData(data){
    let filtered = [...data];
    const yearFilter = document.getElementById('yearFilter').value;
    const semesterFilter = document.getElementById('semesterFilter').value;

    // First, filter out prediction-like data (same start year with different end years)
    filtered = filtered.filter(e => {
        const [startYear, endYear] = e.academic_year.split('-').map(y => parseInt(y));
        // Exclude data where start year and end year are not consecutive (likely predictions)
        return (endYear - startYear) === 1;
    });

    // Apply year filter
    if(yearFilter !== 'all'){
        const currentYear = new Date().getFullYear();
        let startYear, endYear;

        switch(yearFilter){
            case '1year':
                startYear = currentYear - 1;
                endYear = currentYear;
                break;
            case '3years':
                startYear = currentYear - 3;
                endYear = currentYear;
                break;
            case '5years':
                startYear = currentYear - 5;
                endYear = currentYear;
                break;
            case 'custom':
                startYear = parseInt(document.getElementById('startYear').value);
                endYear = parseInt(document.getElementById('endYear').value);
                if(!startYear || !endYear) return filtered; // Don't filter if custom years not selected
                break;
        }

        filtered = filtered.filter(e => {
            const academicEndYear = parseInt(e.academic_year.split('-')[1]);
            return academicEndYear >= startYear && academicEndYear <= endYear;
        });
    }

    // Apply semester filter
    if(semesterFilter !== 'all'){
        filtered = filtered.filter(e => {
            switch(semesterFilter){
                case '1':
                    return e.semester == 1;
                case '2':
                    return e.semester == 2;
                case '3':
                    return e.semester == 3;
                case '1and2':
                    return e.semester == 1 || e.semester == 2;
                default:
                    return true;
            }
        });
    }

    // Aggregate data by academic year and semester when all programs are selected
    const programFilter = document.getElementById('programSelect').value;
    if(programFilter === ''){
        const aggregated = {};
        filtered.forEach(e => {
            const key = `${e.academic_year}_${e.semester}`;
            if(!aggregated[key]){
                aggregated[key] = {
                    academic_year: e.academic_year,
                    semester: e.semester,
                    male: 0,
                    female: 0,
                    total: 0
                };
            }
            aggregated[key].male += e.male || 0;
            aggregated[key].female += e.female || 0;
            aggregated[key].total += e.total || (e.male || 0) + (e.female || 0);
        });
        filtered = Object.values(aggregated);
    }

    return filtered;
}

renderSummary(data){
const grid=document.getElementById('summaryGrid');
const latest=data[data.length-1];

const total=data.reduce((sum,e)=>
sum+(e.total||e.male+e.female||0),0);

// Count semesters (unique academic_year + semester combinations)
const semesterCount = new Set(data.map(e => `${e.academic_year}_${e.semester}`)).size;

grid.innerHTML=`
<div class="summary-card">
<h3>${total.toLocaleString()}</h3>
<p>Total Enrollees</p>
</div>

<div class="summary-card">
<h3>${(latest.total||latest.male+latest.female||0).toLocaleString()}</h3>
<p>Latest Semester</p>
</div>

<div class="summary-card">
<h3>${semesterCount}</h3>
<p>Semesters Recorded</p>
</div>
`;
}

renderChart(data,predTotal,programId){
const ctx=document.getElementById('enrollmentChart').getContext('2d');
if(this.chart) this.chart.destroy();

data.sort((a,b)=>{
const yearA=parseInt(a.academic_year.split('-')[0]);
const yearB=parseInt(b.academic_year.split('-')[0]);
return yearA-yearB||a.semester-b.semester;
});

const labels=data.map(e=>`${e.academic_year} S${e.semester}`);
const totals=data.map(e=>e.total||(e.male||0)+(e.female||0));
const males=data.map(e=>e.male||0);
const females=data.map(e=>e.female||0);

const finalLabels=predTotal?[...labels,'Prediction']:labels;
const finalTotals=predTotal?[...totals,predTotal]:totals;

this.chart=new Chart(ctx,{
type:'line',
data:{
labels:finalLabels,
datasets:[
{
label:'Total',
data:finalTotals,
borderColor:'#ed8936',
backgroundColor:'rgba(212, 170, 34, 0.1)',
borderDash:[5,5],
fill:true,
tension:.4
},
{
label:'Male',
data:predTotal?[...males,null]:males,
borderColor:'#2b6cb0'
},
{
label:'Female',
data:predTotal?[...females,null]:females,
borderColor:'#d53f8c'
}
]
},
options:{
responsive:true,
maintainAspectRatio:false
}
});
}
}

/* LOGIN */
document.getElementById('loginForm')?.addEventListener('submit',e=>{
e.preventDefault();
if(document.getElementById('password').value==='admin123'){
window.location.href='?login=1';
}else alert('Wrong password!');
});

if(window.location.search.includes('login=1') || window.location.href.includes('login=1')){
new EnrollmentTracker();
}
</script>

</body>
</html>
