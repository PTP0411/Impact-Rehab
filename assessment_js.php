<?php
// assessment_js.php
function getAssessmentJavaScript() {
    return <<<'JS'
    <script>
    // Real-time score calculation
    const form = document.getElementById('msk-form');
    const allSelects = form.querySelectorAll('select[name^="scores"]');
    const totalTests = 25;

    const categories = {
      movement: { start: 1, end: 16, maxScore: 80 },
      gripStrength: { start: 17, end: 17, maxScore: 5 },
      balanceAndPower: { start: 18, end: 25, maxScore: 40 }
    };

    function calculateScores() {
      let totalScore = 0;
      let completedTests = 0;
      let categoryScores = {
        movement: { score: 0, completed: 0, total: 16 },
        gripStrength: { score: 0, completed: 0, total: 1 },
        balanceAndPower: { score: 0, completed: 0, total: 8 }
      };
      
      allSelects.forEach(select => {
        const exerciseNum = parseInt(select.name.match(/\d+/)[0]);
        const value = parseInt(select.value) || 0;
        
        if (select.value !== '') {
          completedTests++;
          totalScore += value;
          select.classList.add('filled');
          
          if (exerciseNum >= categories.movement.start && exerciseNum <= categories.movement.end) {
            categoryScores.movement.score += value;
            categoryScores.movement.completed++;
          } 
          else if (exerciseNum >= categories.gripStrength.start && exerciseNum <= categories.gripStrength.end) {
            categoryScores.gripStrength.score += value;
            categoryScores.gripStrength.completed++;
          } 
          else if (exerciseNum >= categories.balanceAndPower.start && exerciseNum <= categories.balanceAndPower.end) {
            categoryScores.balanceAndPower.score += value;
            categoryScores.balanceAndPower.completed++;
          }
        } 
        else {
          select.classList.remove('filled');
        }
      });
      
      let overallPercentage = 0;
      if (completedTests > 0) {
        const maxPossibleForCompleted = completedTests * 5;
        overallPercentage = ((totalScore / maxPossibleForCompleted) * 100).toFixed(1);
      }
      
      const movementPercentage = categoryScores.movement.completed > 0 
        ? ((categoryScores.movement.score / (categoryScores.movement.completed * 5)) * 100).toFixed(0)
        : 0;
        
      const gripStrengthPercentage = categoryScores.gripStrength.completed > 0
        ? ((categoryScores.gripStrength.score / (categoryScores.gripStrength.completed * 5)) * 100).toFixed(0)
        : 0;
        
      const balanceAndPowerPercentage = categoryScores.balanceAndPower.completed > 0
        ? ((categoryScores.balanceAndPower.score / (categoryScores.balanceAndPower.completed * 5)) * 100).toFixed(0)
        : 0;
      
      document.getElementById('current-score').textContent = overallPercentage;
      document.getElementById('movement-score').textContent = movementPercentage;
      document.getElementById('grip-strength-score').textContent = gripStrengthPercentage;
      document.getElementById('balance-power-score').textContent = balanceAndPowerPercentage;
      
      const completionPercentage = (completedTests / totalTests) * 100;
      document.getElementById('completion-fill').style.width = completionPercentage + '%';
      document.getElementById('completion-text').textContent = `${completedTests} of ${totalTests} tests completed`;
      
      document.getElementById('score-circle').style.setProperty('--score-height', overallPercentage + '%');
      
      updateTierIndicator(parseFloat(overallPercentage), completedTests);
      
      const submitBtn = document.querySelector('.btn-calculate');
      submitBtn.disabled = false;
      
      if (completedTests === 0) {
        submitBtn.textContent = 'Start Assessment';
      } else if (completedTests === totalTests) {
        submitBtn.textContent = 'Submit Complete Assessment';
      } else {
        submitBtn.textContent = `Submit Assessment (${completedTests}/${totalTests})`;
      }
    }

    function updateTierIndicator(score, completedTests) {
      const tierElement = document.getElementById('tier-indicator');
      
      if (completedTests === 0) {
        tierElement.textContent = 'Fill in tests to see projected tier';
        tierElement.style.background = 'rgba(255,255,255,0.2)';
        return;
      }
      
      let tier, color;
      
      if (score >= 90) {
        tier = 'Elite';
        color = '#2e7d32';
      } else if (score >= 80) {
        tier = 'Competitive';
        color = '#388e3c';
      } else if (score >= 70) {
        tier = 'Athletic';
        color = '#66bb6a';
      } else if (score >= 60) {
        tier = 'Functional';
        color = '#fbc02d';
      } else if (score >= 50) {
        tier = 'Recreational';
        color = '#f57c00';
      } else {
        tier = 'At Risk';
        color = '#d32f2f';
      }
      
      const partial = completedTests < totalTests ? ' (Partial)' : '';
      tierElement.textContent = `Projected Tier: ${tier}${partial}`;
      tierElement.style.background = color;
    }

    allSelects.forEach(select => {
      select.addEventListener('change', calculateScores);
    });

    calculateScores();

    form.addEventListener('submit', function(e) {
      const completedTests = Array.from(allSelects).filter(s => s.value !== '').length;
      
      if (completedTests === 0) {
        e.preventDefault();
        alert('Please complete at least one test before submitting.');
        return;
      }
      
      if (completedTests < totalTests) {
        const confirmSubmit = confirm(
          `You have completed ${completedTests} out of ${totalTests} tests.\n\n` +
          `The MSK score will be calculated based on the ${completedTests} completed test(s).\n\n` +
          `Do you want to submit this partial assessment?`
        );
        
        if (!confirmSubmit) {
          e.preventDefault();
        }
      }
    });
    </script>
JS;
}
?>