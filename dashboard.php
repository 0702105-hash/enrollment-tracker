<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrollment Tracker - Advanced Metrics Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
}

.card{
    background:white;
    border-radius:18px;
    padding:25px;
    box-shadow:0 8px 25px rgba(0,0,0,0.06);
    margin-bottom:25px;
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

select, button{
    padding:12px 18px;
    border-radius:12px;
    border:1px solid #e2e8f0;
    font-size:15px;
    cursor:pointer;
}

button{
    background:#3182ce;
    color:white;
    border:none;
    font-weight:600;
}

.select-container{
    display:flex;
    gap:12px;
    margin-bottom:20px;
    flex-wrap:wrap;
}

/* ===== METRICS DISPLAY ===== */

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

/* ===== COMPARISON TABLE ===== */

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

/* ===== METRIC SECTION ===== */

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

/* ===== RESPONSIVE ===== */

@media(max-width:768px){
    .metrics-container{
        grid-template-columns:1fr;
    }
    
    .select-container{
        flex-direction:column;
    }
    
    select, button{
        width:100%;
    }
}
</style>
</head>

<body>

<?php if (!isset($_GET['login'])): ?>

<div style="max-width:400px;margin:120px auto;padding:40px;background:white;border-radius:20px;box-shadow:0 20px 40px rgba(0,0,0,.1);text-align:center;">
    <h2>🎓 Enrollment Tracker</h2>
    <p style="color:#718096;margin-bottom:25px;">Advanced Metrics Dashboard</p>
    <form id="login">
        <input type="password" id="password" placeholder="Password" required autofocus style="width:100%;margin:15px 0;padding:12px;">
        <button type="submit" style="width:100%;">Login</button>
    </form>
</div>

<?php else: ?>

<div class="container" id="dashboard" style="display:none;">

    <div class="header-banner">
        <h1>📊 Advanced Multi-Model Metrics Dashboard</h1>
        <p>Algorithm-Specific Metrics: SARMAX | Prophet | LSTM</p>
    </div>

    <div class="card">
        <div class="tabs">
            <button class="tab-btn active" data-tab="metrics-comparison">📊 Metrics Comparison</button>
            <button class="tab-btn" data-tab="sarmax-details">📈 SARMAX Details</button>
            <button class="tab-btn" data-tab="prophet-details">🔮 Prophet Details</button>
            <button class="tab-btn" data-tab="lstm-details">🤖 LSTM Details</button>
        </div>
    </div>

    <!-- ===== METRICS COMPARISON TAB ===== -->
    <div id="metrics-comparison" class="tab-content active">
        <div class="card">
            <h2>📊 Algorithm Comparison</h2>
            
            <div class="select-container">
                <label>Select Program:</label>
                <select id="programFilter" style="flex:1;max-width:400px;">
                    <option value="">Choose a program</option>
                </select>
                <button onclick="loadMetrics()">🔄 Load Metrics</button>
            </div>

            <div id="comparisonContainer" style="display:none;">
                
                <!-- COMPARISON TABLE -->
                <h3 style="margin:30px 0 15px 0;">Common Metrics (MAE, RMSE, MAPE, MSE)</h3>
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

                <!-- ALGORITHM-SPECIFIC METRICS -->
                <h3 style="margin:30px 0 15px 0;">Algorithm-Specific Metrics</h3>
                <div class="metrics-container" id="specificMetricsContainer"></div>

                <!-- MODEL CARDS -->
                <h3 style="margin:30px 0 15px 0;">Detailed Model Metrics</h3>
                <div class="metrics-container" id="modelCardsContainer"></div>
            </div>
        </div>
    </div>

    <!-- ===== SARMAX DETAILS TAB ===== -->
    <div id="sarmax-details" class="tab-content">
        <div class="card">
            <h2>📈 SARMAX Model Details</h2>
            <p style="color:#718096;margin-bottom:20px;">Seasonal AutoRegressive Integrated Moving Average with eXogenous variables</p>
            
            <div class="metric-section" style="border-left-color:#667eea;">
                <h3>Common Metrics</h3>
                <div class="metric-description">
                    <strong>MAE (Mean Absolute Error):</strong> Average error in student count. Lower is better.<br>
                    <strong>RMSE (Root Mean Squared Error):</strong> Penalizes large errors. Lower is better.<br>
                    <strong>MAPE (Mean Absolute Percentage Error):</strong> Relative error %. <10%=Excellent, <20%=Good<br>
                    <strong>MSE (Mean Squared Error):</strong> Average squared errors. Lower is better.
                </div>
            </div>

            <div class="metric-section" style="border-left-color:#667eea;">
                <h3>SARMAX-Specific Metrics</h3>
                
                <div style="margin:15px 0;">
                    <strong style="color:#667eea;">AIC (Akaike Information Criterion)</strong>
                    <div class="metric-description">
                        Measure of model fit balancing goodness-of-fit with model complexity.
                        <div class="metric-scale">Lower values indicate better fit. Used for model selection.</div>
                    </div>
                </div>

                <div style="margin:15px 0;">
                    <strong style="color:#667eea;">BIC (Bayesian Information Criterion)</strong>
                    <div class="metric-description">
                        Similar to AIC but penalizes model complexity more heavily.
                        <div class="metric-scale">Lower values indicate better fit. Often preferred for parsimony.</div>
                    </div>
                </div>

                <div style="margin:15px 0;">
                    <strong style="color:#667eea;">Ljung-Box Test (p-value)</strong>
                    <div class="metric-description">
                        Tests for residual autocorrelation (whether residuals are independent).
                        <div class="metric-scale">p-value > 0.05 = Good (residuals are independent). p-value < 0.05 = Model may be missing patterns.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== PROPHET DETAILS TAB ===== -->
    <div id="prophet-details" class="tab-content">
        <div class="card">
            <h2>🔮 Prophet Model Details</h2>
            <p style="color:#718096;margin-bottom:20px;">Time series forecasting with trend and seasonality components</p>
            
            <div class="metric-section" style="border-left-color:#f093fb;">
                <h3>Common Metrics</h3>
                <div class="metric-description">
                    <strong>MAE, RMSE, MAPE, MSE:</strong> Same as SARMAX (see above)
                </div>
            </div>

            <div class="metric-section" style="border-left-color:#f093fb;">
                <h3>Prophet-Specific Metrics</h3>
                
                <div style="margin:15px 0;">
                    <strong style="color:#f093fb;">MdAPE (Median Absolute Percentage Error)</strong>
                    <div class="metric-description">
                        Median of absolute percentage errors rather than mean. More robust to outliers.
                        <div class="metric-scale">Lower is better. <10%=Excellent, <15%=Good, <25%=Acceptable</div>
                    </div>
                </div>

                <div style="margin:15px 0;padding:15px;background:white;border-radius:8px;border-left:4px solid #f093fb;">
                    <strong>Why MdAPE instead of MAPE?</strong>
                    <p style="margin:8px 0 0 0;font-size:13px;color:#718096;">
                        If one program has a huge error spike, MAPE gets skewed. MdAPE (median) is more stable and better represents typical forecast accuracy.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== LSTM DETAILS TAB ===== -->
    <div id="lstm-details" class="tab-content">
        <div class="card">
            <h2>🤖 LSTM Model Details</h2>
            <p style="color:#718096;margin-bottom:20px;">Long Short-Term Memory Neural Network for sequence prediction</p>
            
            <div class="metric-section" style="border-left-color:#ed8936;">
                <h3>Common Metrics</h3>
                <div class="metric-description">
                    <strong>MAE, RMSE, MAPE, MSE:</strong> Same as SARMAX (see above)
                </div>
            </div>

            <div class="metric-section" style="border-left-color:#ed8936;">
                <h3>LSTM-Specific Metrics</h3>
                
                <div style="margin:15px 0;">
                    <strong style="color:#ed8936;">Training Loss</strong>
                    <div class="metric-description">
                        Mean Squared Error on training data during each epoch.
                        <div class="metric-scale">Should decrease during training. Indicates model is learning.</div>
                    </div>
                </div>

                <div style="margin:15px 0;">
                    <strong style="color:#ed8936;">Validation Loss</strong>
                    <div class="metric-description">
                        Mean Squared Error on validation data (held-out during training).
                        <div class="metric-scale">Indicates generalization ability. If much higher than training loss = overfitting.</div>
                    </div>
                </div>

                <div style="margin:15px 0;">
                    <strong style="color:#ed8936;">R² (Coefficient of Determination)</strong>
                    <div class="metric-description">
                        Proportion of variance explained on test data.
                        <div class="metric-scale">>0.85 = Excellent, 0.7-0.85 = Good, 0.5-0.7 = Fair, <0.5 = Poor</div>
                    </div>
                </div>

                <div style="margin:15px 0;padding:15px;background:white;border-radius:8px;border-left:4px solid #ed8936;">
                    <strong>Interpreting Loss Curves:</strong>
                    <p style="margin:8px 0 0 0;font-size:13px;color:#718096;">
                        • Both losses decreasing = Good training<br>
                        • Training loss low, validation loss high = Overfitting (model memorized training data)<br>
                        • Both losses high = Underfitting (model too simple)<br>
                        • Validation loss increasing = Early stopping triggered (prevents overfitting)
                    </p>
                </div>
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

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('dashboard').style.display = 'block';
    setupTabs();
    loadPrograms();
});

function setupTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.getAttribute('data-tab');
            
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            btn.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
}

async function loadPrograms() {
    try {
        const res = await fetch('api/programs.php');
        const programs = await res.json();
        
        const html = '<option value="">Choose a program</option>' +
            programs.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        
        document.getElementById('programFilter').innerHTML = html;
    } catch(e) {
        console.error('Error:', e);
    }
}

async function loadMetrics() {
    const programId = document.getElementById('programFilter').value;
    
    if (!programId) {
        alert('Please select a program');
        return;
    }

    try {
        const res = await fetch('api/model-metrics.php?program_id=' + programId);
        const data = await res.json();
        
        if (!data.data || data.data.length === 0) {
            alert('No metrics found');
            return;
        }

        // Group by model
        const byModel = {};
        data.data.forEach(m => {
            if (!byModel[m.model_name]) byModel[m.model_name] = {};
            byModel[m.model_name][m.metric_name] = m.metric_value;
        });

        displayCommonMetrics(byModel);
        displaySpecificMetrics(byModel);
        displayModelCards(byModel);
        
        document.getElementById('comparisonContainer').style.display = 'block';

    } catch(e) {
        alert('Error: ' + e.message);
    }
}

function displayCommonMetrics(byModel) {
    const commonMetrics = ['MAE', 'RMSE', 'MAPE', 'MSE'];
    const models = ['SARMAX', 'Prophet', 'LSTM'];
    
    let html = '';
    commonMetrics.forEach(metric => {
        const values = models.map(m => byModel[m]?.[metric] || 'N/A');
        
        let bestIdx = -1;
        if (['MAE', 'RMSE', 'MAPE', 'MSE'].includes(metric)) {
            const nums = values.map(v => typeof v === 'number' ? v : Infinity);
            bestIdx = nums.indexOf(Math.min(...nums));
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

function displaySpecificMetrics(byModel) {
    let html = '';

    // SARMAX specific
    html += `
        <div class="model-card sarmax">
            <div class="model-header">
                <span class="model-badge sarmax">SARMAX</span>
                <span class="model-name">Specific Metrics</span>
            </div>
            <div class="metrics-grid">
                <div class="metric-item sarmax">
                    <div><span class="metric-label">AIC</span><span class="metric-category">Model Fit</span></div>
                    <div class="metric-value">${byModel['SARMAX']?.['AIC'] || 'N/A'}</div>
                </div>
                <div class="metric-item sarmax">
                    <div><span class="metric-label">BIC</span><span class="metric-category">Model Complexity</span></div>
                    <div class="metric-value">${byModel['SARMAX']?.['BIC'] || 'N/A'}</div>
                </div>
                <div class="metric-item sarmax">
                    <div><span class="metric-label">Ljung-Box p-val</span><span class="metric-category">Residual Independence</span></div>
                    <div class="metric-value">${byModel['SARMAX']?.['Ljung_Box_Pvalue'] || 'N/A'}</div>
                </div>
            </div>
        </div>
    `;

    // Prophet specific
    html += `
        <div class="model-card prophet">
            <div class="model-header">
                <span class="model-badge prophet">PROPHET</span>
                <span class="model-name">Specific Metrics</span>
            </div>
            <div class="metrics-grid">
                <div class="metric-item prophet">
                    <div><span class="metric-label">MdAPE</span><span class="metric-category">Median % Error</span></div>
                    <div class="metric-value">${byModel['Prophet']?.['MdAPE'] || 'N/A'}%</div>
                </div>
            </div>
        </div>
    `;

    // LSTM specific
    html += `
        <div class="model-card lstm">
            <div class="model-header">
                <span class="model-badge lstm">LSTM</span>
                <span class="model-name">Specific Metrics</span>
            </div>
            <div class="metrics-grid">
                <div class="metric-item lstm">
                    <div><span class="metric-label">Training Loss</span><span class="metric-category">MSE on Train Data</span></div>
                    <div class="metric-value">${byModel['LSTM']?.['Training_Loss'] || 'N/A'}</div>
                </div>
                <div class="metric-item lstm">
                    <div><span class="metric-label">Validation Loss</span><span class="metric-category">MSE on Val Data</span></div>
                    <div class="metric-value">${byModel['LSTM']?.['Validation_Loss'] || 'N/A'}</div>
                </div>
                <div class="metric-item lstm">
                    <div><span class="metric-label">R²</span><span class="metric-category">Variance Explained</span></div>
                    <div class="metric-value">${byModel['LSTM']?.['R²'] || 'N/A'}</div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('specificMetricsContainer').innerHTML = html;
}

function displayModelCards(byModel) {
    let html = '';

    ['SARMAX', 'Prophet', 'LSTM'].forEach(modelName => {
        const modelClass = modelName.toLowerCase().replace('exponentialsmoothing', 'exp').replace('linearregression', 'lr').replace('movingaverage', 'ma');
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
                    ${metricsHtml}
                </div>
            </div>
        `;
    });

    document.getElementById('modelCardsContainer').innerHTML = html;
}
</script>

<?php if (isset($_GET['login'])): ?>
<script>
document.getElementById('login')?.addEventListener('submit', e => {
    e.preventDefault();
    if (document.getElementById('password').value === 'admin123') {
        window.location.href = '?login=1';
    } else alert('Wrong password!');
});
<?php else: ?>
document.getElementById('login')?.addEventListener('submit', e => {
    e.preventDefault();
    if (document.getElementById('password').value === 'admin123') {
        window.location.href = '?login=1';
    } else alert('Wrong password!');
});
<?php endif; ?>
</script>

</body>
</html>