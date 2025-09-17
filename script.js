document.getElementById("sandbox-form").addEventListener("submit", function(e) {
  e.preventDefault();

  // Collect all inputs
  const formData = new FormData(e.target);
  const scores = [];
  const labels = [];

  formData.forEach((value, key) => {
    labels.push(key);
    scores.push(Number(value) || 0);
  });

  // Destroy old chart if exists
  if (window.resultsChart) {
    window.resultsChart.destroy();
  }

  // Create chart
  const ctx = document.getElementById("resultsChart").getContext("2d");
  window.resultsChart = new Chart(ctx, {
    type: "radar",
    data: {
      labels: labels,
      datasets: [{
        label: "Your Scores",
        data: scores,
        backgroundColor: "rgba(0, 74, 173, 0.2)",
        borderColor: "#004aad",
        borderWidth: 2,
        pointBackgroundColor: "#004aad"
      }]
    },
    options: {
      responsive: true,
      scales: {
        r: {
          angleLines: { color: "#ccc" },
          suggestedMin: 0,
          suggestedMax: 100
        }
      }
    }
  });
});

document.getElementById("doctor-login").addEventListener("click", () => {
  window.location.href = "login.html";
});

