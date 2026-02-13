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
        this.loadDashboard();
    }

    async loadPrograms() {
        const res = await fetch('/api/programs.php');
        this.programs = await res.json();
    }

    renderPrograms() {
        const select = document.getElementById('programSelect');
        select.innerHTML = '<option value="">Select Program...</option>' + 
            this.programs.map(p => 
                `<option value="${p.id}">${p.department} - ${p.name}</option>`
            ).join('');
    }

    async loadDashboard(programId = null) {
        if (programId) this.currentProgramId = programId;
        
        const chartContainer = document.querySelector('.chart-container');
        chartContainer.innerHTML = '<div class="loading">Loading enrollment data...</div>';

        try {
            const [enrollmentsRes, predictionsRes] = await Promise.all([
                fetch(`/api/enrollments.php${this.currentProgramId ? '?program_id=' + this.currentProgramId : ''}`),
                fetch(`/api/predictions.php${this.currentProgramId ? '?program_id=' + this.currentProgramId : ''}`)
            ]);

            this.enrollments = await enrollmentsRes.json();
            this.predictions = await predictionsRes.json();

            this.renderSummary();
            this.renderChart();
        } catch (error) {
            chartContainer.innerHTML = '<div class="error">Failed to load data. Please run the prediction script first.</div>';
        }
    }

    renderSummary() {
        const summaryGrid = document.querySelector('.summary-grid');
        if (!this.enrollments.length) return;

        const latest = this.enrollments[this.enrollments.length - 1];
        const totalPrograms = [...new Set(this.enrollments.map(e => e.program_id))].length;
        const avgGrowth = this.calculateGrowthRate();

        summaryGrid.innerHTML = `
            <div class="summary-card">
                <h3>${this.enrollments.reduce((sum, e) => sum + e.total, 0)}</h3>
                <p>Total Enrollees (All Time)</p>
            </div>
            <div class="summary-card">
                <h3>${latest.total}</h3>
                <p>Latest Semester</p>
            </div>
            <div class="summary-card">
                <h3>${totalPrograms}</h3>
                <p>Programs Tracked</p>
            </div>
            <div class="summary-card">
                <h3>${avgGrowth.toFixed(1)}%</h3>
                <p>Avg Growth Rate</p>
            </div>
        `;
    }

    calculateGrowthRate() {
        if (this.enrollments.length < 2) return 0;
        const totals = this.enrollments.map(e => e.total);
        const growths = [];
        for (let i = 1; i < totals.length; i++) {
            growths.push((totals[i] - totals[i-1]) / totals[i-1] * 100);
        }
        return growths.reduce((a, b) => a + b, 0) / growths.length;
    }

    renderChart() {
        const ctx = document.getElementById('enrollmentChart').getContext('2d');
        
        if (window.enrollmentChart) window.enrollmentChart.destroy();

        const labels = this.enrollments.map(e => `${e.academic_year} S${e.semester}`);
        const totals = this.enrollments.map(e => e.total);
        const males = this.enrollments.map(e => e.male);
        const females = this.enrollments.map(e => e.female);

        // Add prediction
        const latestPred = this.predictions[0];
        if (latestPred) {
            labels.push('PREDICTED');
            totals.push(latestPred.predicted_total);
            males.push(latestPred.predicted_male);
            females.push(latestPred.predicted_female);
        }

        window.enrollmentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total Enrollees',
                        data: totals,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Male',
                        data: males,
                        borderColor: '#2196F3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        fill: false
                    },
                    {
                        label: 'Female', 
                        data: females,
                        borderColor: '#E91E63',
                        backgroundColor: 'rgba(233, 30, 99, 0.1)',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: `Enrollment Trends ${this.currentProgramId ? '- ' + this.programs.find(p => p.id == this.currentProgramId)?.name : '(All Programs)'}`,
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

// Initialize when DOM loads
document.addEventListener('DOMContentLoaded', () => {
    window.tracker = new EnrollmentTracker();
    
    document.getElementById('programSelect').addEventListener('change', (e) => {
        window.tracker.loadDashboard(e.target.value || null);
    });
});
