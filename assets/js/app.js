class EnrollmentTracker {
    constructor() {
        this.programs = [];
        this.enrollments = [];
        this.predictions = [];
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
        console.log('Programs loaded:', this.programs);
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
            const enrollmentsRes = await fetch(
                `/enrollment-tracker/api/enrollments.php${this.currentProgramId ? '?program_id=' + this.currentProgramId : ''}`
            );
            const predictionsRes = await fetch(
                `/enrollment-tracker/api/predictions.php${this.currentProgramId ? '?program_id=' + this.currentProgramId : ''}`
            );

            const enrollmentsData = await enrollmentsRes.json();
            const predictionsData = await predictionsRes.json();

            this.enrollments = Array.isArray(enrollmentsData) ? enrollmentsData : [];
            this.predictions = Array.isArray(predictionsData?.data) ? predictionsData.data : [];

            console.log('Enrollments loaded:', this.enrollments);
            console.log('Predictions loaded:', this.predictions);

            this.renderSummary();
            this.renderChart();
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
            if (!modelMap[modelName]) {
                modelMap[modelName] = {};
            }
            modelMap[modelName][key] = pred.predicted_total || 0;
        });

        return modelMap;
    }

    renderChart() {
        const canvas = document.getElementById('enrollmentChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        if (window.enrollmentChart) {
            window.enrollmentChart.destroy();
        }

        const historicalLabels = this.enrollments.map(e => `${e.academic_year} S${e.semester}`);
        const historicalTotals = this.enrollments.map(e => e.total || 0);

        const predictionLabels = [...new Set(this.predictions.map(p => `${p.academic_year} S${p.semester}`))];
        const allLabels = [...historicalLabels];

        predictionLabels.forEach(label => {
            if (!allLabels.includes(label)) {
                allLabels.push(label);
            }
        });

        const historicalMap = {};
        this.enrollments.forEach(e => {
            historicalMap[`${e.academic_year} S${e.semester}`] = e.total || 0;
        });

        const predictionMap = this.buildPredictionMap();

        const makeSeries = (sourceMap) => allLabels.map(label => {
            return Object.prototype.hasOwnProperty.call(sourceMap, label) ? sourceMap[label] : null;
        });

        const datasets = [
            {
                label: 'Historical Total',
                data: makeSeries(historicalMap),
                borderColor: '#2E7D32',
                backgroundColor: 'rgba(46,125,50,0.12)',
                fill: false,
                tension: 0.3,
                borderWidth: 3
            },
            {
                label: 'SARMAX Prediction',
                data: makeSeries(predictionMap.SARMAX || {}),
                borderColor: '#5C6BC0',
                backgroundColor: 'rgba(92,107,192,0.12)',
                fill: false,
                tension: 0.3,
                borderDash: [6, 4]
            },
            {
                label: 'Prophet Prediction',
                data: makeSeries(predictionMap.Prophet || {}),
                borderColor: '#D81B60',
                backgroundColor: 'rgba(216,27,96,0.12)',
                fill: false,
                tension: 0.3,
                borderDash: [6, 4]
            },
            {
                label: 'LSTM Prediction',
                data: makeSeries(predictionMap.LSTM || {}),
                borderColor: '#FB8C00',
                backgroundColor: 'rgba(251,140,0,0.12)',
                fill: false,
                tension: 0.3,
                borderDash: [6, 4]
            },
            {
                label: 'Ensemble Prediction',
                data: makeSeries(predictionMap.Ensemble || {}),
                borderColor: '#00897B',
                backgroundColor: 'rgba(0,137,123,0.12)',
                fill: false,
                tension: 0.3,
                borderWidth: 3,
                borderDash: [10, 5]
            }
        ];

        window.enrollmentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: allLabels,
                datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
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