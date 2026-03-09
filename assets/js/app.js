class EnrollmentTracker {
    constructor() {
        this.programs = [];
        this.enrollments = [];
        this.predictions = [];
        this.metrics = [];
        this.currentProgramId = null;
        this.init();
    }

    async init() {
        await this.loadPrograms();
        this.renderPrograms();
        await this.loadDashboard();
    }

    async loadPrograms() {
        const res = await fetch('/enrollment-tracker/api/programs.php');
        this.programs = await res.json();
    }

    renderPrograms() {
        const select = document.getElementById('programSelect');
        if (!select) return;

        select.innerHTML = '<option value="">Select Program...</option>' +
            this.programs.map(p =>
                `<option value="${p.id}">${p.department} - ${p.name}</option>`
            ).join('');
    }

    async loadDashboard(programId = null) {
        if (programId !== null) {
            this.currentProgramId = programId || null;
        }

        const chartContainer = document.querySelector('.chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = '<div style="text-align:center;padding:40px;">Loading...</div>';
        }

        try {
            const query = this.currentProgramId ? `?program_id=${this.currentProgramId}` : '';

            const [enrollmentsRes, predictionsRes, metricsRes] = await Promise.all([
                fetch(`/enrollment-tracker/api/enrollments.php${query}`),
                fetch(`/enrollment-tracker/api/predictions.php${query}`),
                fetch(`/enrollment-tracker/api/model-metrics.php${query}`)
            ]);

            const enrollmentsData = await enrollmentsRes.json();
            const predictionsData = await predictionsRes.json();
            const metricsData = await metricsRes.json();

            this.enrollments = Array.isArray(enrollmentsData) ? enrollmentsData : [];
            this.predictions = Array.isArray(predictionsData?.data) ? predictionsData.data : [];
            this.metrics = Array.isArray(metricsData?.data) ? metricsData.data : [];

            this.renderSummary();
            this.renderChart();
            this.renderMetrics();
        } catch (error) {
            console.error('Load error:', error);
            if (chartContainer) {
                chartContainer.innerHTML = '<div style="color:red;text-align:center;padding:40px;">Error loading data. Check Console (F12).</div>';
            }
        }
    }

    renderSummary() {
        const summaryGrid = document.querySelector('.summary-grid');
        if (!summaryGrid || !this.enrollments.length) return;

        const latest = this.enrollments[this.enrollments.length - 1];
        const totalPrograms = [...new Set(this.enrollments.map(e => e.program_id))].length;

        summaryGrid.innerHTML = `
            <div class="summary-card" style="background:linear-gradient(135deg,#4CAF50,#45a049);color:white;padding:20px;border-radius:12px;text-align:center;">
                <h3>${this.enrollments.reduce((sum, e) => sum + (parseInt(e.total, 10) || 0), 0)}</h3>
                <p>Total Enrollees</p>
            </div>
            <div class="summary-card" style="background:linear-gradient(135deg,#2196F3,#1976D2);color:white;padding:20px;border-radius:12px;text-align:center;">
                <h3>${latest.total || 0}</h3>
                <p>Latest Semester</p>
            </div>
            <div class="summary-card" style="background:linear-gradient(135deg,#FF9800,#F57C00);color:white;padding:20px;border-radius:12px;text-align:center;">
                <h3>${totalPrograms}</h3>
                <p>Programs Tracked</p>
            </div>
        `;
    }

    buildPredictionMap() {
        const modelMap = {
            SARMAX: {},
            Prophet: {},
            LSTM: {},
            Ensemble: {}
        };

        this.predictions.forEach(pred => {
            const key = `${pred.academic_year} S${pred.semester}`;
            const modelName = pred.model_name || 'Ensemble';
            if (!modelMap[modelName]) modelMap[modelName] = {};
            modelMap[modelName][key] = pred.predicted_total || 0;
        });

        return modelMap;
    }

    renderChart() {
        const canvas = document.getElementById('enrollmentChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (window.enrollmentChart) window.enrollmentChart.destroy();

        const historicalLabels = this.enrollments.map(e => `${e.academic_year} S${e.semester}`);
        const predictionLabels = [...new Set(this.predictions.map(p => `${p.academic_year} S${p.semester}`))];

        const allLabels = [...historicalLabels];
        predictionLabels.forEach(label => {
            if (!allLabels.includes(label)) allLabels.push(label);
        });

        const historicalMap = {};
        this.enrollments.forEach(e => {
            historicalMap[`${e.academic_year} S${e.semester}`] = e.total || 0;
        });

        const predictionMap = this.buildPredictionMap();

        const makeSeries = sourceMap => allLabels.map(label =>
            Object.prototype.hasOwnProperty.call(sourceMap, label) ? sourceMap[label] : null
        );

        window.enrollmentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: allLabels,
                datasets: [
                    {
                        label: 'Historical Total',
                        data: makeSeries(historicalMap),
                        borderColor: '#2E7D32',
                        backgroundColor: 'rgba(46,125,50,0.12)',
                        fill: false,
                        borderWidth: 3,
                        tension: 0.3
                    },
                    {
                        label: 'SARMAX',
                        data: makeSeries(predictionMap.SARMAX || {}),
                        borderColor: '#5C6BC0',
                        fill: false,
                        borderDash: [6, 4],
                        tension: 0.3
                    },
                    {
                        label: 'Prophet',
                        data: makeSeries(predictionMap.Prophet || {}),
                        borderColor: '#D81B60',
                        fill: false,
                        borderDash: [6, 4],
                        tension: 0.3
                    },
                    {
                        label: 'LSTM',
                        data: makeSeries(predictionMap.LSTM || {}),
                        borderColor: '#FB8C00',
                        fill: false,
                        borderDash: [6, 4],
                        tension: 0.3
                    },
                    {
                        label: 'Ensemble',
                        data: makeSeries(predictionMap.Ensemble || {}),
                        borderColor: '#00897B',
                        fill: false,
                        borderWidth: 3,
                        borderDash: [10, 5],
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    title: {
                        display: true,
                        text: 'Enrollment Trends and Multi-Model Predictions',
                        font: { size: 18 }
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    groupMetricsByModel() {
        const byModel = {};
        this.metrics.forEach(item => {
            if (!byModel[item.model_name]) byModel[item.model_name] = {};
            byModel[item.model_name][item.metric_name] = item.metric_value;
        });
        return byModel;
    }

    getBestModelSummary(byModel) {
        const compareMetrics = [
            { key: 'MAE', better: 'min' },
            { key: 'RMSE', better: 'min' },
            { key: 'MAPE', better: 'min' },
            { key: 'R²', better: 'max' }
        ];

        const models = ['SARMAX', 'Prophet', 'LSTM'];
        const scores = { SARMAX: 0, Prophet: 0, LSTM: 0 };

        compareMetrics.forEach(metric => {
            const values = models
                .map(model => ({
                    model,
                    value: byModel[model]?.[metric.key]
                }))
                .filter(item => typeof item.value === 'number' && !Number.isNaN(item.value));

            if (!values.length) return;

            let winner;
            if (metric.better === 'min') {
                winner = values.reduce((best, cur) => cur.value < best.value ? cur : best);
            } else {
                winner = values.reduce((best, cur) => cur.value > best.value ? cur : best);
            }

            scores[winner.model] += 1;
        });

        let bestModel = 'N/A';
        let bestScore = -1;
        Object.entries(scores).forEach(([model, score]) => {
            if (score > bestScore) {
                bestModel = model;
                bestScore = score;
            }
        });

        return { bestModel, scores };
    }

    renderMetrics() {
        const chartContainer = document.querySelector('.chart-container');
        if (!chartContainer) return;

        const existing = document.getElementById('modelMetricsPanel');
        if (existing) existing.remove();

        if (!this.currentProgramId || !this.metrics.length) return;

        const byModel = this.groupMetricsByModel();
        const summary = this.getBestModelSummary(byModel);

        const panel = document.createElement('div');
        panel.id = 'modelMetricsPanel';
        panel.style.marginTop = '24px';

        const metricKeys = ['MAE', 'RMSE', 'MAPE', 'R²', 'MSE', 'RMSLE', 'Theil_U'];

        const buildMetricRows = (modelName, accent) => {
            const metrics = byModel[modelName] || {};
            const rows = metricKeys.map(key => `
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eee;">
                    <span>${key}</span>
                    <strong>${metrics[key] ?? 'N/A'}</strong>
                </div>
            `).join('');

            return `
                <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 16px rgba(0,0,0,0.08);border-top:5px solid ${accent};">
                    <h3 style="margin:0 0 12px 0;color:${accent};">${modelName}</h3>
                    ${rows}
                </div>
            `;
        };

        panel.innerHTML = `
            <div style="margin-top:16px;padding:18px;background:#f7fafc;border-radius:14px;border-left:5px solid #2b6cb0;">
                <h2 style="margin:0 0 10px 0;">Algorithm Accuracy Comparison</h2>
                <p style="margin:0;color:#4a5568;">
                    Best overall model for this program: <strong>${summary.bestModel}</strong>
                </p>
                <p style="margin:8px 0 0 0;color:#718096;font-size:14px;">
                    Scorecard — SARMAX: ${summary.scores.SARMAX}, Prophet: ${summary.scores.Prophet}, LSTM: ${summary.scores.LSTM}
                </p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin-top:18px;">
                ${buildMetricRows('SARMAX', '#5C6BC0')}
                ${buildMetricRows('Prophet', '#D81B60')}
                ${buildMetricRows('LSTM', '#FB8C00')}
            </div>
        `;

        chartContainer.appendChild(panel);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.tracker = new EnrollmentTracker();

    const select = document.getElementById('programSelect');
    if (select) {
        select.addEventListener('change', (e) => {
            window.tracker.loadDashboard(e.target.value || null);
        });
    }
});