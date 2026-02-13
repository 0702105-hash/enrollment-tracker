<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Tracker Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; 
            background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
            min-height: 100vh; padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        .card { 
            background: white; border-radius: 16px; padding: 30px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); margin-bottom: 30px; 
        }
        h1 { color: #2c3e50; margin-bottom: 10px; font-size: 2.5em; }
        .select-container { 
            display: flex; gap: 15px; align-items: center; 
            margin-bottom: 30px; flex-wrap: wrap; 
        }
        select, button { 
            padding: 14px 24px; border: 2px solid #e1e8ed; 
            border-radius: 12px; font-size: 16px; 
        }
        button { 
            background: #4CAF50; color: white; border-color: #4CAF50; 
            cursor: pointer; font-weight: 600; transition: all 0.3s; 
        }
        button:hover { background: #45a049; transform: translateY(-2px); }
        .summary-grid { 
            display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
            gap: 20px; margin: 30px 0; 
        }
        .summary-card { 
            background: linear-gradient(135deg, var(--color) 0%, color-mix(in srgb, var(--color) 50%, #000) 100%); 
            color: white; padding: 25px; border-radius: 16px; text-align: center; 
        }
        .summary-card h3 { font-size: 3em; margin-bottom: 8px; font-weight: 700; }
        .chart-container { 
            height: 500px; position: relative; margin-top: 30px; 
            background: white; border-radius: 12px; padding: 20px; 
        }
        .status { 
            padding: 15px 20px; border-radius: 10px; margin: 15px 0; 
            font-weight: 500; 
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        #loginForm { max-width: 400px; margin: 100px auto; padding: 40px; 
            background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            text-align: center; 
        }
    </style>
</head>
<body>
    <?php if (!isset($_GET['login'])): ?>
    <div id="loginForm">
        <h2>🎓 Enrollment Tracker</h2>
        <form id="login">
            <input type="password" id="password" placeholder="Enter password" required>
            <button type="submit">Login to Dashboard</button>
        </form>
    </div>
    <?php else: ?>
    <div class="container" id="dashboard" style="display:none;">
        <div class="card">
            <h1>🎓 Enrollment Tracker System</h1>
            <p>Predictive analytics for CAS programs</p>
            
            <div class="select-container">
                <select id="programSelect">
                    <option value="">Loading programs...</option>
                </select>
                <button id="refreshBtn">🔄 Refresh</button>
            </div>

            <div id="status" class="status" style="display: none;"></div>
            
            <div class="summary-grid" id="summaryGrid"></div>
            
            <div class="chart-container">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
    class EnrollmentTracker {
        constructor() {
            this.chart = null;
            this.init();
        }
        
        init() {
            <?php if (isset($_GET['login'])): ?>
            document.getElementById('dashboard').style.display = 'block';
            this.loadPrograms();
            this.bindEvents();
            <?php endif; ?>
        }
        
        showStatus(msg, type = 'success') {
            const status = document.getElementById('status');
            status.textContent = msg;
            status.className = `status ${type}`;
            status.style.display = 'block';
            setTimeout(() => status.style.display = 'none', 5000);
        }
        
        async loadPrograms() {
            try {
                const res = await fetch('/enrollment-tracker/api/programs.php');
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const programs = await res.json();
                const select = document.getElementById('programSelect');
                select.innerHTML = '<option value="">All Programs</option>' + 
                    programs.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
                this.showStatus('✅ Programs loaded!');
            } catch(e) {
                console.error('Programs error:', e);
                this.showStatus('❌ Failed to load programs: ' + e.message, 'error');
            }
        }
        
        bindEvents() {
            document.getElementById('programSelect').addEventListener('change', 
                (e) => this.loadData(e.target.value));
            document.getElementById('refreshBtn').addEventListener('click', 
                () => this.loadData(document.getElementById('programSelect').value));
        }
        
        async loadData(programId) {
            try {
                this.showStatus('Loading data...');
                
                // Get enrollment data
                const enrollRes = await fetch(
                    `/enrollment-tracker/api/enrollments.php${programId ? '?program_id=' + programId : ''}`
                );
                if (!enrollRes.ok) throw new Error(`Enrollments HTTP ${enrollRes.status}`);
                const data = await enrollRes.json();
                
                if (!data || data.length === 0) {
                    this.showStatus('No data for selected program', 'error');
                    return;
                }
                
                // Get predictions
                let predTotal = null;
                try {
                    const predRes = await fetch(`/enrollment-tracker/api/predictions.php?program_id=${programId}`);
                    if (predRes.ok) {
                        const predData = await predRes.json();
                        predTotal = predData[0]?.predicted_total || null;
                    }
                } catch(e) {
                    console.warn('Predictions unavailable:', e);
                }
                
                this.renderSummary(data);
                this.renderChart(data, predTotal, programId);
                this.showStatus(`✅ Chart updated (${data.length} data points)`);
                
            } catch(e) {
                console.error('Load error:', e);
                this.showStatus('❌ Load failed: ' + e.message, 'error');
            }
        }
        
        renderSummary(data) {
            const grid = document.getElementById('summaryGrid');
            const latest = data[data.length - 1];
            const total = data.reduce((sum, e) => sum + (e.total || e.male + e.female || 0), 0);
            
            grid.innerHTML = `
                <div class="summary-card" style="--color: #4CAF50;">
                    <h3>${total.toLocaleString()}</h3><p>Total Enrollees</p>
                </div>
                <div class="summary-card" style="--color: #2196F3;">
                    <h3>${(latest.total || latest.male + latest.female || 0).toLocaleString()}</h3><p>Latest Semester</p>
                </div>
                <div class="summary-card" style="--color: #FF9800;">
                    <h3>${data.length}</h3><p>Semesters</p>
                </div>
            `;
        }
        
        renderChart(data, predTotal, programId) {
            const canvas = document.getElementById('enrollmentChart');
            if (!canvas) return console.error('Canvas missing!');
            
            const ctx = canvas.getContext('2d');
            if (this.chart) this.chart.destroy();
            
            // Sort data chronologically
            data.sort((a, b) => {
                const yearA = parseInt(a.academic_year.split('-')[0]);
                const yearB = parseInt(b.academic_year.split('-')[0]);
                return yearA - yearB || a.semester - b.semester;
            });
            
            const labels = data.map(e => `${e.academic_year} S${e.semester}`);
            const totals = data.map(e => e.total || (e.male || 0) + (e.female || 0));
            const males = data.map(e => e.male || 0);
            const females = data.map(e => e.female || 0);
            
            const finalLabels = predTotal ? [...labels, '2026-2027 PRED'] : labels;
            const finalTotals = predTotal ? [...totals, predTotal] : totals;
            
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: finalLabels,
                    datasets: [
                        {
                            label: 'Total Enrollment',
                            data: finalTotals,
                            borderColor: '#4CAF50',
                            backgroundColor: 'rgba(76,175,80,0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3
                        },
                        {
                            label: 'Male',
                            data: predTotal ? [...males, null] : males,
                            borderColor: '#FF9800',
                            backgroundColor: 'rgba(255,152,0,0.1)',
                            borderDash: [5, 5],
                            tension: 0.2,
                            borderWidth: 2
                        },
                        {
                            label: 'Female', 
                            data: predTotal ? [...females, null] : females,
                            borderColor: '#E91E63',
                            backgroundColor: 'rgba(233,30,99,0.1)',
                            tension: 0.2,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        title: { 
                            display: true, 
                            text: `Enrollment Trends ${programId ? `- Program ${programId}` : ''}`,
                            font: { size: 20, weight: 'bold' }
                        },
                        legend: { position: 'top' }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true,
                            ticks: { callback: value => value.toLocaleString() }
                        }
                    }
                }
            });
        }
    }
    
    // Login handler
    document.getElementById('loginForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        if (document.getElementById('password').value === 'admin123') {
            window.location.href = '?login=1';
        } else {
            alert('Wrong password!');
        }
    });
    
    // Initialize
    if (window.location.search.includes('login=1')) {
        new EnrollmentTracker();
    }
    </script>
</body>
</html>
