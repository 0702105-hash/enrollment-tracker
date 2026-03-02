// Program names mapping
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

const semesterNames = {
    1: 'First',
    2: 'Second',
    3: 'Summer'
};

let allEnrollments = [];
let allPredictions = [];
let trendChart = null;
let genderChart = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    updateTimestamp();
    setInterval(updateTimestamp, 1000);
    loadDashboardData();
    populateSelectOptions();
});

// ========== NAVIGATION ==========
function initializeNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const tabContents = document.querySelectorAll('.tab-content');

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const tabId = item.getAttribute('data-tab');

            // Remove active class from all
            navItems.forEach(n => n.classList.remove('active'));
            tabContents.forEach(t => t.classList.remove('active'));

            // Add active class to clicked
            item.classList.add('active');
            document.getElementById(tabId).classList.add('active');

            // Update page title
            const titles = {
                'overview': '📊 Dashboard Overview',
                'enrollments': '👥 Enrollment Records',
                'add-enrollment': '➕ Add New Enrollment',
                'predictions': '🔮 Enrollment Predictions'
            };
            document.getElementById('page-title').textContent = titles[tabId];

            // Load data if needed
            if (tabId === 'enrollments') refreshEnrollments();
            if (tabId === 'predictions') refreshPredictions();
            if (tabId === 'overview') loadDashboardData();
        });
    });
}

function updateTimestamp() {
    const now = new Date();
    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    document.getElementById('timestamp').textContent = now.toLocaleDateString('en-US', options);
}

// ========== DATA LOADING ==========
async function loadDashboardData() {
    try {
        // Load enrollments
        const enrollRes = await fetch('api/enrollments.php');
        allEnrollments = await enrollRes.json();

        // Load predictions
        const predRes = await fetch('api/predictions.php');
        allPredictions = await predRes.json();

        // Update stats
        updateStats();
        updateCharts();
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

function updateStats() {
    if (!Array.isArray(allEnrollments)) return;

    // Total enrollees
    const totalEnrollees = allEnrollments.reduce((sum, e) => {
        return sum + (parseInt(e.male) || 0) + (parseInt(e.female) || 0);
    }, 0);
    document.getElementById('total-enrollees').textContent = totalEnrollees.toLocaleString();

    // Total programs
    const programs = new Set(allEnrollments.map(e => e.program_id));
    document.getElementById('total-programs').textContent = programs.size;

    // Total years
    const years = new Set(allEnrollments.map(e => e.academic_year));
    document.getElementById('total-years').textContent = years.size;

    // Total predictions
    document.getElementById('total-predictions').textContent = (Array.isArray(allPredictions) ? allPredictions.length : 0);
}

// ========== CHARTS ==========
function updateCharts() {
    updateTrendChart();
    updateGenderChart();
}

function updateTrendChart() {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    const programTotals = {};

    if (Array.isArray(allEnrollments)) {
        allEnrollments.forEach(e => {
            if (!programTotals[e.program_id]) {
                programTotals[e.program_id] = 0;
            }
            programTotals[e.program_id] += (parseInt(e.male) || 0) + (parseInt(e.female) || 0);
        });
    }

    const labels = Object.keys(programTotals).map(pid => programNames[pid] || `Program ${pid}`);
    const data = Object.values(programTotals);

    if (trendChart) trendChart.destroy();

    trendChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Enrollees by Program',
                data: data,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                datalabels: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function updateGenderChart() {
    const ctx = document.getElementById('genderChart');
    if (!ctx) return;

    let totalMale = 0, totalFemale = 0;

    if (Array.isArray(allEnrollments)) {
        allEnrollments.forEach(e => {
            totalMale += parseInt(e.male) || 0;
            totalFemale += parseInt(e.female) || 0;
        });
    }

    if (genderChart) genderChart.destroy();

    genderChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: [totalMale, totalFemale],
                backgroundColor: ['rgba(52, 152, 219, 0.8)', 'rgba(240, 147, 251, 0.8)'],
                borderColor: ['rgba(52, 152, 219, 1)', 'rgba(240, 147, 251, 1)'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = totalMale + totalFemale;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// ========== ENROLLMENTS TAB ==========
async function refreshEnrollments() {
    try {
        const programFilter = document.getElementById('enrollProgram')?.value || '';
        const yearFilter = document.getElementById('enrollYear')?.value || '';

        let filtered = [...allEnrollments];

        if (programFilter) {
            filtered = filtered.filter(e => e.program_id == programFilter);
        }
        if (yearFilter) {
            filtered = filtered.filter(e => e.academic_year === yearFilter);
        }

        const tbody = document.getElementById('enrollmentsTable');
        if (!tbody) return;

        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No records found</td></tr>';
            return;
        }

        tbody.innerHTML = filtered.map(e => `
            <tr>
                <td>${programNames[e.program_id] || e.program_id}</td>
                <td>${e.academic_year}</td>
                <td>${semesterNames[e.semester] || e.semester}</td>
                <td><strong>${e.male}</strong></td>
                <td><strong>${e.female}</strong></td>
                <td><strong>${parseInt(e.male) + parseInt(e.female)}</strong></td>
                <td>
                    <button class="btn btn-danger" onclick="deleteEnrollment(${e.id})">Delete</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        console.error('Error refreshing enrollments:', error);
    }
}

async function deleteEnrollment(id) {
    if (!confirm('Are you sure you want to delete this record?')) return;

    try {
        const response = await fetch('api/delete-enrollment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${id}`
        });

        const result = await response.json();
        if (result.success) {
            alert('✅ Record deleted successfully');
            loadDashboardData();
            refreshEnrollments();
        } else {
            alert('❌ Error deleting record');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// ========== ADD ENROLLMENT ==========
async function handleAddEnrollment(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append('program_id', document.getElementById('formProgram').value);
    formData.append('academic_year', document.getElementById('formYear').value);
    formData.append('semester', document.getElementById('formSemester').value);
    formData.append('male', document.getElementById('formMale').value);
    formData.append('female', document.getElementById('formFemale').value);

    try {
        const response = await fetch('api/add-enrollment.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            alert('✅ Enrollment added successfully');
            document.getElementById('addEnrollmentForm').reset();
            loadDashboardData();
            refreshEnrollments();
            displayRecentAdded();
        } else {
            alert(`❌ Error: ${result.message}`);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error adding enrollment');
    }
}

function displayRecentAdded() {
    const recentList = document.getElementById('recentList');
    if (!recentList) return;

    const recent = allEnrollments.slice(-5).reverse();
    recentList.innerHTML = recent.map(e => `
        <div class="recent-item">
            <div>
                <strong>${programNames[e.program_id]}</strong>
                <div class="recent-info">${e.academic_year} - ${semesterNames[e.semester]}</div>
            </div>
            <div style="text-align: right;">
                <div><strong>${parseInt(e.male) + parseInt(e.female)}</strong> students</div>
                <div class="recent-info">${e.male}M / ${e.female}F</div>
            </div>
        </div>
    `).join('');
}

// ========== PREDICTIONS TAB ==========
async function refreshPredictions() {
    try {
        const programFilter = document.getElementById('predProgram')?.value || '';
        const yearFilter = document.getElementById('predYear')?.value || '';

        let filtered = [...allPredictions];

        if (programFilter) {
            filtered = filtered.filter(p => p.program_id == programFilter);
        }
        if (yearFilter) {
            filtered = filtered.filter(p => p.academic_year === yearFilter);
        }

        const grid = document.getElementById('predictionsGrid');
        if (!grid) return;

        if (filtered.length === 0) {
            grid.innerHTML = '<div class="loading">No predictions found</div>';
            return;
        }

        grid.innerHTML = filtered.map(p => `
            <div class="prediction-card">
                <h4>${programNames[p.program_id] || `Program ${p.program_id}`}</h4>
                <div class="prediction-item">
                    <span class="prediction-label">Academic Year</span>
                    <span class="prediction-value">${p.academic_year}</span>
                </div>
                <div class="prediction-item">
                    <span class="prediction-label">Semester</span>
                    <span class="prediction-value">${semesterNames[p.semester] || p.semester}</span>
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
    } catch (error) {
        console.error('Error loading predictions:', error);
    }
}

// ========== POPULATE SELECT OPTIONS ==========
async function populateSelectOptions() {
    try {
        const programRes = await fetch('api/programs.php');
        const programs = await programRes.json();

        // Populate program dropdowns
        const selects = ['formProgram', 'enrollProgram', 'predProgram'];
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                programs.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = p.name;
                    select.appendChild(option);
                });
            }
        });

        // Populate year dropdowns
        loadDashboardData().then(() => {
            const years = [...new Set(allEnrollments.map(e => e.academic_year))].sort().reverse();
            const yearSelects = ['enrollYear', 'predYear'];
            yearSelects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        select.appendChild(option);
                    });
                }
            });
        });

    } catch (error) {
        console.error('Error populating options:', error);
    }
}