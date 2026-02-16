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
    document.getElementById('dashboard').style.display='block';
    this.loadPrograms();
    this.bindEvents();
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

    this.showStatus('Programs loaded');
}catch(e){
    this.showStatus('Failed to load programs: '+e.message,'error');
}
}

bindEvents(){
document.getElementById('programSelect')
.addEventListener('change',e=>this.loadData(e.target.value));

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
    try{
        const predRes=await fetch(`api/predictions.php?program_id=${programId}`);
        if(predRes.ok){
            const predData=await predRes.json();
            predTotal=predData[0]?.predicted_total||null;
        }
    }catch{}

    this.renderSummary(data);
    this.renderChart(data,predTotal,programId);
    this.showStatus('Dashboard updated');

}catch(e){
    this.showStatus('Load failed: '+e.message,'error');
}
}

renderSummary(data){
const grid=document.getElementById('summaryGrid');
const latest=data[data.length-1];

const total=data.reduce((sum,e)=>
sum+(e.total||e.male+e.female||0),0);

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
<h3>${data.length}</h3>
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
borderColor:'#2b6cb0',
backgroundColor:'rgba(43,108,176,.1)',
fill:true,
tension:.4
},
{
label:'Male',
data:predTotal?[...males,null]:males,
borderColor:'#ed8936',
borderDash:[5,5]
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

if(window.location.search.includes('login=1')){
new EnrollmentTracker();
}
</script>

</body>
</html>
