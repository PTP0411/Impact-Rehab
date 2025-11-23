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
      humanTrak: { start: 1, end: 16, maxScore: 80 },
      dynamo: { start: 17, end: 17, maxScore: 5 },
      forceDecks: { start: 18, end: 25, maxScore: 40 }
    };

    function calculateScores() {
      let totalScore = 0;
      let completedTests = 0;
      let categoryScores = {
        humanTrak: { score: 0, completed: 0, total: 16 },
        dynamo: { score: 0, completed: 0, total: 1 },
        forceDecks: { score: 0, completed: 0, total: 8 }
      };
      
      allSelects.forEach(select => {
        const exerciseNum = parseInt(select.name.match(/\d+/)[0]);
        const value = parseInt(select.value) || 0;
        
        if (select.value !== '') {
          completedTests++;
          totalScore += value;
          select.classList.add('filled');
          
          if (exerciseNum >= categories.humanTrak.start && exerciseNum <= categories.humanTrak.end) {
            categoryScores.humanTrak.score += value;
            categoryScores.humanTrak.completed++;
          } 
          else if (exerciseNum >= categories.dynamo.start && exerciseNum <= categories.dynamo.end) {
            categoryScores.dynamo.score += value;
            categoryScores.dynamo.completed++;
          } 
          else if (exerciseNum >= categories.forceDecks.start && exerciseNum <= categories.forceDecks.end) {
            categoryScores.forceDecks.score += value;
            categoryScores.forceDecks.completed++;
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
      
      const humanTrakPercentage = categoryScores.humanTrak.completed > 0 
        ? ((categoryScores.humanTrak.score / (categoryScores.humanTrak.completed * 5)) * 100).toFixed(0)
        : 0;
        
      const dynamoPercentage = categoryScores.dynamo.completed > 0
        ? ((categoryScores.dynamo.score / (categoryScores.dynamo.completed * 5)) * 100).toFixed(0)
        : 0;
        
      const forceDecksPercentage = categoryScores.forceDecks.completed > 0
        ? ((categoryScores.forceDecks.score / (categoryScores.forceDecks.completed * 5)) * 100).toFixed(0)
        : 0;
      
      document.getElementById('current-score').textContent = overallPercentage;
      document.getElementById('humantrak-score').textContent = humanTrakPercentage;
      document.getElementById('dynamo-score').textContent = dynamoPercentage;
      document.getElementById('forcedecks-score').textContent = forceDecksPercentage;
      
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
        tier = 'Elite (0.1+ Handicap)';
        color = '#2e7d32';
      } else if (score >= 80) {
        tier = 'Competitive (0-5 Handicap)';
        color = '#388e3c';
      } else if (score >= 70) {
        tier = 'Athletic (6-10 Handicap)';
        color = '#66bb6a';
      } else if (score >= 60) {
        tier = 'Functional (11-15 Handicap)';
        color = '#fbc02d';
      } else if (score >= 50) {
        tier = 'Recreational (16-20 Handicap)';
        color = '#f57c00';
      } else {
        tier = 'At Risk (20+ Handicap)';
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