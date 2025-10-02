document.getElementById("sandbox-form").addEventListener("submit", function(e) {
  e.preventDefault();

  const formData = new FormData(e.target);
  let totalScore = 0;
  let count = 0;

  for (let [key, value] of formData.entries()) {
    if (value !== "" && !isNaN(value)) {
      totalScore += parseFloat(value);
      count++;
    }
  }

  // Calculate normalized final score
  let finalScore = count > 0 ? (totalScore / count).toFixed(2) : 0;

  // Clear previous chart if exists
  const chartContainer = document.getElementById("resultsChart");
  if (Chart.getChart(chartContainer)) {
    Chart.getChart(chartContainer).destroy();
  }

  // Display as a pie chart: score vs remaining
  const ctx = chartContainer.getContext("2d");
  new Chart(ctx, {
    type: "pie",
    data: {
      labels: ["Score"],
      datasets: [{
        data: [finalScore, 100 - finalScore],
        backgroundColor: ["#7ab92f", "rgba(0,0,0,0.1)"],
        borderColor: ["#7ab92f", "#ccc"],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "bottom"
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `${context.label}: ${context.parsed}%`;
            }
          }
        }
      }
    }
  });

  // Also show numeric score
  const existingScore = document.getElementById("numeric-score");
  if (existingScore) existingScore.remove();
  document.getElementById("results").insertAdjacentHTML(
    "beforeend",
    `<p id="numeric-score"><strong>Final Score:</strong> ${finalScore}</p>`
  );
});

//
document.getElementById("doctor-login").addEventListener("click", function() {
  window.location.href = "login.html"; // or the path to your doctor login page
});
