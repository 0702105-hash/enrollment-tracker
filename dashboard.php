<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Tracker Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🎓 Enrollment Tracker System</h1>
            <p>AI-Powered enrollment prediction for next semester</p>
            
            <div class="select-container">
                <label for="programSelect">
                    <strong>Select Program:</strong>
                </label>
                <select id="programSelect">
                    <option value="">All Programs</option>
                </select>
            </div>

            <div class="summary-grid"></div>
            
            <div class="chart-container">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>🚀 Quick Actions</h3>
            <p>
                <strong>1. Import Excel:</strong> Put your Excel file in <code>uploads/</code> and run <code>python ../python/import_excel.py</code><br>
                <strong>2. Generate Predictions:</strong> Automatically runs ARIMA model per program<br>
                <strong>3. View Charts:</strong> Select program above to see trends + predictions
            </p>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
