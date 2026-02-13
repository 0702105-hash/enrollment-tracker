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
        // FIXED PATH - Add /enrollment-tracker/
        const res = await fetch('/enrollment-tracker/api/programs.php');
        this.programs = await res.json();
        console.log('Programs loaded:', this.programs); // DEBUG
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
        chartContainer.innerHTML = '<div style="text-align:center;padding:40px;">Loading...</div>';

        try {
            // FIXED PATHS
            const enrollmentsRes = await fetch(`/enrollment-tracker/api/enrollments.php${this.currentProgramId ? '?program_id=' + this.currentProgramId : ''}`);
            const predictionsRes = await fetch(`/enrollment-tracker/api/predictions.php${this.currentProgramId ? '?program_id=' + this.currentProgramId : ''}`);
            
            this.enrollments = await enrollmentsRes.json();
            this.predictions = await predictionsRes.json();
            
            console.log('Data loaded:', this.enrollments); // DEBUG
            
            this.renderSummary();
            this.renderChart();
        } catch (error) {
            console.error('Load error:', error);
            chartContainer.innerHTML = '<div style="color:red;text-align:center;padding:40px;">Error loading data. Check Console (F12).</div>';
        }
    }

    renderSummary() {
        const summaryGrid = document.querySelector('.summary-grid');
        if (!this.enrollments.length) return;

        const latest = this.enrollments[this.enrollments.length - 1];
        const totalPrograms = [...new Set(this.enrollments.map(e => e.program_id))].length;

        summaryGrid.innerHTML = `
            <div class="summary-card" style="background:linear-gradient(135deg,#4CAF50,#45a049);color:white;padding:20px;border-radius:12px;text-align:center;">
                <h3>${this.enrollments.reduce((sum, e) => sum + e.total, 0)}</h3>
                <p>Total Enrollees</p>
            </div>
            <div class="summary-card" style="background:linear-gradient(135deg,#2196F3,#1976D2);color:white;padding:20px;border-radius:12px;text-align:center;">
                <h3>${latest.total}</h3>
                <p>Latest Semester</p>
            </div>
            <div class="summary-card" style="background:linear-gradient(135deg,#FF9800,#F57C00);color:white;padding:20px;border-radius:12px;text-align:center;">
                <h3>${totalPrograms}</h3>
                <p>Programs Tracked</p>
            </div>
        `;
    }

    renderChart() {
        const ctx = document.getElementById('enrollmentChart').getContext('2d');
        
        if (window.enrollmentChart) window.enrollmentChart.destroy();

        const labels = this.enrollments.map(e => `${e.academic_year} S${e.semester}`);
        const totals = this.enrollments.map(e => e.total || 0);
        const males = this.enrollments.map(e => e.male || 0);
        const females = this.enrollments.map(e => e.female || 0);

        window.enrollmentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total',
                        data: totals,
                        borderColor: '#4CAF50',
                        backgroundColor: 'rgba(76,175,80,0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Male',
                        data: males,
                        borderColor: '#2196F3',
                        backgroundColor: 'rgba(33,150,243,0.1)'
                    },
                    {
                        label: 'Female', 
                        data: females,
                        borderColor: '#E91E63',
                        backgroundColor: 'rgba(233,30,99,0.1)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: 'Enrollment Trends', font: { size: 18 } }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.tracker = new EnrollmentTracker();
    
    document.getElementById('programSelect').addEventListener('change', (e) => {
        window.tracker.loadDashboard(e.target.value || null);
    });
});
