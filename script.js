// Pie Chart
new Chart(document.getElementById("statusChart"), {
    type: "doughnut",
    data: {
        labels: ["Open", "On Progress", "Waiting", "Closed"],
        datasets: [{
            data: [24, 56, 16, 48],
            backgroundColor: [
                "#ef4444",
                "#3b82f6",
                "#f59e0b",
                "#22c55e"
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: "right"
            }
        }
    }
});


// Bar Chart
new Chart(document.getElementById("areaChart"), {
    type: "bar",
    data: {
        labels: ["JKT", "BKS", "TNG", "SBY"],
        datasets: [{
            label: "Task",
            data: [62, 32, 18, 16],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});