// assets/js/dashboard.js

function loadProgramData(programId) {
    fetch(`/enrollment-tracker/api/enrollments.php?program_id=${programId}`)
        .then(res => res.json())
        .then(data => {
            const labels = data
                .map(d => `${d.academic_year} S${d.semester}`);
            const total  = data.map(d => parseInt(d.total) || null);
            const male   = data.map(d => parseInt(d.male) || null);
            const female = data.map(d => parseInt(d.female) || null);

            fetch(`/enrollment-tracker/api/predictions.php?program_id=${programId}`)
                .then(res => res.json())
                .then(predData => {
                    const pred = predData[0] || {};

                    new Chart(
                        document.getElementById('enrollmentChart').getContext('2d'),
                        {
                            type: 'line',
                            data: {
                                labels: labels.concat(['PRED next sem']),
                                datasets: [
                                    {
                                        label: 'Total',
                                        data: total.concat([pred.predicted_total || null]),
                                        borderColor: '#1976d2',
                                        backgroundColor: 'rgba(25,118,210,0.1)',
                                        tension: 0.2,
                                        fill: true
                                    },
                                    {
                                        label: 'Male',
                                        data: male.concat([pred.predicted_male || null]),
                                        borderColor: '#ff9800',
                                        backgroundColor: 'rgba(255,152,0,0.1)',
                                        borderDash: [5, 5],
                                        tension: 0.2
                                    },
                                    {
                                        label: 'Female',
                                        data: female.concat([pred.predicted_female || null]),
                                        borderColor: '#e91e63',
                                        backgroundColor: 'rgba(233,30,99,0.1)',
                                        tension: 0.2
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    tooltip: { mode: 'index' },
                                    legend: { position: 'top' }
                                }
                            }
                        }
                    );
                });
        })
        .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('programSelect');

    if (select.value) {
        loadProgramData(select.value);
    }

    select.addEventListener('change', () => {
        if (select.value) {
            const url = new URL(window.location.href);
            url.searchParams.set('program_id', select.value);
            history.pushState({}, '', url);
            loadProgramData(select.value);
        }
    });
});
