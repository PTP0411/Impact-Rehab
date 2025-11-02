<?php
// assessment_styles.php
function getAssessmentStyles() {
    return <<<CSS
    <style>
    .assessment-form {
      max-width: 1200px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .form-section {
      margin-bottom: 2rem;
      padding: 1.5rem;
      background: #f9f9f9;
      border-radius: 8px;
      border-left: 4px solid #7ab92f;
    }
    
    .form-section h3 {
      color: #7ab92f;
      margin-bottom: 1rem;
      font-size: 1.3rem;
    }
    
    .test-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .test-item {
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
    }
    
    .test-item label {
      font-weight: 600;
      color: #333;
      font-size: 0.95rem;
    }
    
    .test-item select {
      padding: 0.6rem;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      background: white;
    }
    
    .test-item select:focus {
      outline: none;
      border-color: #7ab92f;
      box-shadow: 0 0 0 2px rgba(122, 185, 47, 0.1);
    }
    
    .test-item select.filled {
      border-color: #7ab92f;
      background: #f0f9eb;
      transition: all 0.3s;
    }
    
    .button-group {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      justify-content: center;
    }
    
    .btn-calculate {
      background: #7ab92f;
      color: white;
      border: none;
      padding: 1rem 3rem;
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
    
    .btn-calculate:hover {
      background: #5d8e24;
      transform: translateY(-2px);
    }
    
    .btn-calculate:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
    }
    
    .btn-cancel {
      background: #6c757d;
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
    }
    
    .btn-cancel:hover {
      background: #5a6268;
    }
    
    .patient-info {
      background: #e8f5e9;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 2rem;
    }
    
    .patient-info h2 {
      color: #7ab92f;
      margin: 0 0 0.5rem 0;
    }

    /* Real-time Score Display */
    .score-display {
      position: sticky;
      top: 20px;
      background: linear-gradient(135deg, #7ab92f 0%, #5d8e24 100%);
      color: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      margin-bottom: 2rem;
      text-align: center;
      z-index: 100;
    }
    
    .score-display h3 {
      margin: 0 0 1rem 0;
      font-size: 1.2rem;
      color: white;
    }
    
    .score-circle {
      width: 150px;
      height: 150px;
      margin: 0 auto 1rem;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      border: 4px solid rgba(255,255,255,0.4);
      position: relative;
      overflow: hidden;
    }
    
    .score-circle::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: var(--score-height, 0%);
      background: rgba(255,255,255,0.3);
      transition: height 0.5s ease;
    }
    
    .score-value {
      font-size: 3rem;
      font-weight: bold;
      line-height: 1;
      position: relative;
      z-index: 1;
    }
    
    .score-label {
      font-size: 0.9rem;
      opacity: 0.9;
      position: relative;
      z-index: 1;
    }
    
    .score-breakdown {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .score-category {
      background: rgba(255,255,255,0.15);
      padding: 0.8rem;
      border-radius: 8px;
    }
    
    .score-category-label {
      font-size: 0.8rem;
      opacity: 0.9;
      margin-bottom: 0.3rem;
    }
    
    .score-category-value {
      font-size: 1.5rem;
      font-weight: bold;
    }
    
    .completion-bar {
      margin-top: 1rem;
      background: rgba(255,255,255,0.2);
      height: 8px;
      border-radius: 4px;
      overflow: hidden;
    }
    
    .completion-fill {
      height: 100%;
      background: white;
      width: 0%;
      transition: width 0.3s ease;
    }
    
    .completion-text {
      font-size: 0.85rem;
      margin-top: 0.5rem;
      opacity: 0.9;
    }
    
    .tier-indicator {
      margin-top: 1rem;
      padding: 0.5rem;
      background: rgba(255,255,255,0.2);
      border-radius: 6px;
      font-size: 0.9rem;
      font-weight: 600;
    }
    </style>
CSS;
}
?>