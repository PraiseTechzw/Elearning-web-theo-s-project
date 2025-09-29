// Hardware Support JavaScript
let diagnosticData = {
  currentDevice: 'desktop',
  answers: {},
  isRunning: false
};

let supportStats = {
  totalTickets: 0,
  resolvedTickets: 0,
  avgResponseTime: 0,
  satisfactionRate: 0
};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
  loadSupportStats();
  updateStatsDisplay();
  loadDiagnosticQuestions();
});

function loadSupportStats() {
  const stored = localStorage.getItem('supportStats');
  if (stored) {
    supportStats = { ...supportStats, ...JSON.parse(stored) };
  } else {
    // Initialize with demo data
    supportStats = {
      totalTickets: 156,
      resolvedTickets: 142,
      avgResponseTime: 2.5,
      satisfactionRate: 94
    };
  }
}

function saveSupportStats() {
  localStorage.setItem('supportStats', JSON.stringify(supportStats));
}

function updateStatsDisplay() {
  document.getElementById('totalTickets').textContent = supportStats.totalTickets;
  document.getElementById('resolvedTickets').textContent = supportStats.resolvedTickets;
  document.getElementById('avgResponseTime').textContent = supportStats.avgResponseTime + 'h';
  document.getElementById('satisfactionRate').textContent = supportStats.satisfactionRate + '%';
}

function selectDevice(deviceType) {
  diagnosticData.currentDevice = deviceType;
  
  // Update active button
  document.querySelectorAll('.device-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector(`[data-device="${deviceType}"]`).classList.add('active');
  
  // Reset answers and load new questions
  diagnosticData.answers = {};
  loadDiagnosticQuestions();
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification(`Selected ${deviceType} for diagnostic`, 'info');
  }
}

function loadDiagnosticQuestions() {
  const questions = getDiagnosticQuestions(diagnosticData.currentDevice);
  const form = document.getElementById('diagnosticForm');
  
  form.innerHTML = '';
  
  questions.forEach((question, index) => {
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question-item';
    questionDiv.innerHTML = `
      <h4>${question.question}</h4>
      <div class="question-options">
        ${question.options.map((option, optionIndex) => `
          <button class="option-btn" onclick="selectAnswer(${index}, ${optionIndex})" data-question="${index}" data-option="${optionIndex}">
            ${option}
          </button>
        `).join('')}
      </div>
    `;
    form.appendChild(questionDiv);
  });
  
  // Enable/disable run button
  updateRunButton();
}

function getDiagnosticQuestions(deviceType) {
  const questionSets = {
    desktop: [
      {
        question: "What is the main issue you're experiencing?",
        options: [
          "Computer won't turn on",
          "Slow performance",
          "Blue screen errors",
          "No display",
          "Loud noises/fan issues"
        ]
      },
      {
        question: "When did the problem first occur?",
        options: [
          "Today",
          "This week",
          "This month",
          "Several months ago",
          "Not sure"
        ]
      },
      {
        question: "Have you tried any troubleshooting steps?",
        options: [
          "Restarted the computer",
          "Checked cables and connections",
          "Ran antivirus scan",
          "Updated drivers",
          "No troubleshooting attempted"
        ]
      },
      {
        question: "What type of work do you primarily do on this computer?",
        options: [
          "Office applications",
          "Gaming",
          "Video editing",
          "Programming/development",
          "General web browsing"
        ]
      }
    ],
    laptop: [
      {
        question: "What is the main issue with your laptop?",
        options: [
          "Battery not charging",
          "Overheating",
          "Screen problems",
          "Keyboard issues",
          "Performance issues"
        ]
      },
      {
        question: "How old is your laptop?",
        options: [
          "Less than 1 year",
          "1-2 years",
          "2-4 years",
          "4-6 years",
          "More than 6 years"
        ]
      },
      {
        question: "Does the laptop work when plugged in?",
        options: [
          "Yes, works fine when plugged in",
          "Sometimes works when plugged in",
          "No, doesn't work even when plugged in",
          "Not sure",
          "Haven't tried"
        ]
      }
    ],
    printer: [
      {
        question: "What type of printer issue are you experiencing?",
        options: [
          "Printer won't print",
          "Poor print quality",
          "Paper jams",
          "Connection problems",
          "Error messages"
        ]
      },
      {
        question: "Is the printer connected via:",
        options: [
          "USB cable",
          "Wi-Fi/Network",
          "Ethernet cable",
          "Bluetooth",
          "Not sure"
        ]
      },
      {
        question: "When did the printer last work properly?",
        options: [
          "Today",
          "This week",
          "This month",
          "Several months ago",
          "Never worked properly"
        ]
      }
    ],
    projector: [
      {
        question: "What is the main issue with the projector?",
        options: [
          "No image displayed",
          "Poor image quality",
          "Connection problems",
          "Remote control not working",
          "Lamp/bulb issues"
        ]
      },
      {
        question: "How is the projector connected?",
        options: [
          "HDMI cable",
          "VGA cable",
          "Wireless connection",
          "USB connection",
          "Not sure"
        ]
      },
      {
        question: "Is the projector powering on?",
        options: [
          "Yes, powers on normally",
          "Powers on but no image",
          "Won't power on at all",
          "Powers on and off repeatedly",
          "Not sure"
        ]
      }
    ]
  };
  
  return questionSets[deviceType] || questionSets.desktop;
}

function selectAnswer(questionIndex, optionIndex) {
  diagnosticData.answers[questionIndex] = optionIndex;
  
  // Update button states
  const questionDiv = document.querySelectorAll('.question-item')[questionIndex];
  questionDiv.querySelectorAll('.option-btn').forEach(btn => {
    btn.classList.remove('selected');
  });
  questionDiv.querySelector(`[data-option="${optionIndex}"]`).classList.add('selected');
  
  updateRunButton();
}

function updateRunButton() {
  const totalQuestions = getDiagnosticQuestions(diagnosticData.currentDevice).length;
  const answeredQuestions = Object.keys(diagnosticData.answers).length;
  const runBtn = document.getElementById('runDiagnosticBtn');
  
  runBtn.disabled = answeredQuestions < totalQuestions;
  
  if (answeredQuestions < totalQuestions) {
    runBtn.innerHTML = `<i class="fas fa-play"></i> Answer ${totalQuestions - answeredQuestions} more questions`;
  } else {
    runBtn.innerHTML = '<i class="fas fa-play"></i> Run Diagnostic';
  }
}

function runDiagnostic() {
  if (diagnosticData.isRunning) return;
  
  diagnosticData.isRunning = true;
  const runBtn = document.getElementById('runDiagnosticBtn');
  runBtn.innerHTML = '<div class="loading"></div> Running Diagnostic...';
  runBtn.disabled = true;
  
  // Simulate diagnostic process
  setTimeout(() => {
    const results = generateDiagnosticResults();
    displayDiagnosticResults(results);
    diagnosticData.isRunning = false;
    
    // Update support stats
    supportStats.totalTickets++;
    saveSupportStats();
    updateStatsDisplay();
  }, 3000);
}

function generateDiagnosticResults() {
  const deviceType = diagnosticData.currentDevice;
  const answers = diagnosticData.answers;
  
  // Generate results based on answers
  let healthScore = 85;
  let issues = [];
  let recommendations = [];
  
  // Analyze answers and generate issues/recommendations
  Object.keys(answers).forEach(questionIndex => {
    const answer = answers[questionIndex];
    const question = getDiagnosticQuestions(deviceType)[questionIndex];
    
    // Simple logic to generate issues based on answers
    if (question.question.includes('issue') || question.question.includes('problem')) {
      if (answer === 0) { // First option usually indicates serious issues
        healthScore -= 30;
        issues.push({
          type: 'Critical',
          description: 'Critical hardware issue detected',
          solution: 'Immediate professional repair required'
        });
      } else if (answer === 1) {
        healthScore -= 15;
        issues.push({
          type: 'Warning',
          description: 'Performance degradation detected',
          solution: 'System optimization recommended'
        });
      }
    }
    
    if (question.question.includes('troubleshooting')) {
      if (answer === 4) { // No troubleshooting attempted
        recommendations.push({
          type: 'Info',
          description: 'Try basic troubleshooting steps first',
          action: 'Restart device and check connections'
        });
      }
    }
  });
  
  // Add some random issues/recommendations for demo
  if (Math.random() < 0.3) {
    issues.push({
      type: 'Warning',
      description: 'Outdated drivers detected',
      solution: 'Update device drivers'
    });
    healthScore -= 10;
  }
  
  if (Math.random() < 0.4) {
    recommendations.push({
      type: 'Maintenance',
      description: 'Regular maintenance recommended',
      action: 'Schedule routine checkup'
    });
  }
  
  // Ensure health score is within bounds
  healthScore = Math.max(0, Math.min(100, healthScore));
  
  return {
    healthScore,
    issues,
    recommendations
  };
}

function displayDiagnosticResults(results) {
  const resultsDiv = document.getElementById('diagnosticResults');
  const healthScore = document.getElementById('healthScore');
  const issuesCount = document.getElementById('issuesCount');
  const recommendationsCount = document.getElementById('recommendationsCount');
  const resultDetails = document.getElementById('resultDetails');
  
  // Update summary
  healthScore.textContent = results.healthScore + '%';
  healthScore.className = 'health-score ' + getHealthClass(results.healthScore);
  issuesCount.textContent = results.issues.length;
  recommendationsCount.textContent = results.recommendations.length;
  
  // Update details
  resultDetails.innerHTML = '';
  
  if (results.issues.length > 0) {
    const issuesDiv = document.createElement('div');
    issuesDiv.innerHTML = '<h4>Issues Found:</h4>';
    results.issues.forEach(issue => {
      const issueDiv = document.createElement('div');
      issueDiv.className = 'issue-item';
      issueDiv.innerHTML = `
        <strong>${issue.type}:</strong> ${issue.description}<br>
        <em>Solution: ${issue.solution}</em>
      `;
      issuesDiv.appendChild(issueDiv);
    });
    resultDetails.appendChild(issuesDiv);
  }
  
  if (results.recommendations.length > 0) {
    const recDiv = document.createElement('div');
    recDiv.innerHTML = '<h4>Recommendations:</h4>';
    results.recommendations.forEach(rec => {
      const recItemDiv = document.createElement('div');
      recItemDiv.className = 'recommendation-item';
      recItemDiv.innerHTML = `
        <strong>${rec.type}:</strong> ${rec.description}<br>
        <em>Action: ${rec.action}</em>
      `;
      recDiv.appendChild(recItemDiv);
    });
    resultDetails.appendChild(recDiv);
  }
  
  // Show results
  resultsDiv.style.display = 'block';
  resultsDiv.classList.add('show');
  
  // Reset run button
  const runBtn = document.getElementById('runDiagnosticBtn');
  runBtn.innerHTML = '<i class="fas fa-play"></i> Run Diagnostic';
  runBtn.disabled = false;
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Diagnostic completed!', 'success');
  }
}

function getHealthClass(score) {
  if (score >= 80) return 'excellent';
  if (score >= 60) return 'good';
  if (score >= 40) return 'fair';
  return 'poor';
}

function resetDiagnostic() {
  diagnosticData.answers = {};
  document.getElementById('diagnosticResults').style.display = 'none';
  loadDiagnosticQuestions();
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Diagnostic reset', 'info');
  }
}

function requestService(serviceType) {
  // Simulate service request
  supportStats.totalTickets++;
  saveSupportStats();
  updateStatsDisplay();
  
  const serviceNames = {
    diagnostics: 'Computer Diagnostics',
    printer: 'Printer Setup & Repair',
    av: 'Projector & AV Support',
    mobile: 'Mobile Device Support'
  };
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification(`${serviceNames[serviceType]} request submitted!`, 'success');
  }
  
  // In a real application, this would submit a ticket
  console.log(`Service request submitted: ${serviceType}`);
}
