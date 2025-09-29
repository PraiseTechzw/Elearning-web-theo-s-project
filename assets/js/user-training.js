// User Training JavaScript
let trainingData = {
  modules: {
    orientation: { completed: false, progress: 0, timeSpent: 0 },
    wifi: { completed: false, progress: 0, timeSpent: 0 },
    security: { completed: false, progress: 0, timeSpent: 0 },
    helpdesk: { completed: false, progress: 0, timeSpent: 0 }
  },
  certificates: 0,
  totalTimeSpent: 0
};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
  loadTrainingData();
  updateProgressDisplay();
  initializeModules();
});

function loadTrainingData() {
  const stored = localStorage.getItem('trainingData');
  if (stored) {
    trainingData = { ...trainingData, ...JSON.parse(stored) };
  }
}

function saveTrainingData() {
  localStorage.setItem('trainingData', JSON.stringify(trainingData));
}

function updateProgressDisplay() {
  // Calculate overall progress
  const totalModules = Object.keys(trainingData.modules).length;
  const completedModules = Object.values(trainingData.modules).filter(m => m.completed).length;
  const overallProgress = Math.round((completedModules / totalModules) * 100);
  
  // Update progress circle
  const progressCircle = document.getElementById('progressCircle');
  if (progressCircle) {
    const circumference = 2 * Math.PI * 45;
    const offset = circumference - (overallProgress / 100) * circumference;
    progressCircle.style.strokeDashoffset = offset;
  }
  
  // Update progress text
  document.getElementById('overallProgress').textContent = overallProgress + '%';
  document.getElementById('modulesCompleted').textContent = `${completedModules}/${totalModules}`;
  document.getElementById('timeSpent').textContent = `${trainingData.totalTimeSpent} min`;
  document.getElementById('certificates').textContent = trainingData.certificates;
  
  // Update individual module progress
  Object.keys(trainingData.modules).forEach(moduleId => {
    const module = trainingData.modules[moduleId];
    const moduleCard = document.querySelector(`[data-module="${moduleId}"]`);
    if (moduleCard) {
      const progressFill = moduleCard.querySelector('.progress-fill');
      const progressText = moduleCard.querySelector('.progress-text');
      
      if (progressFill) {
        progressFill.style.width = module.progress + '%';
      }
      
      if (progressText) {
        progressText.textContent = module.progress + '% Complete';
      }
      
      // Update button text if completed
      const button = moduleCard.querySelector('button');
      if (button && module.completed) {
        button.innerHTML = '<i class="fas fa-check"></i> Completed';
        button.classList.remove('btn-primary');
        button.classList.add('btn-secondary');
        button.disabled = true;
      }
    }
  });
}

function initializeModules() {
  // Add click handlers for module buttons
  document.querySelectorAll('[data-module]').forEach(card => {
    const moduleId = card.dataset.module;
    const button = card.querySelector('button');
    
    if (button && !trainingData.modules[moduleId].completed) {
      button.addEventListener('click', () => startModule(moduleId));
    }
  });
}

function startModule(moduleId) {
  const module = trainingData.modules[moduleId];
  if (module.completed) return;
  
  // Show module modal
  showModuleModal(moduleId);
}

function showModuleModal(moduleId) {
  const modal = document.createElement('div');
  modal.className = 'module-modal active';
  
  const moduleContent = getModuleContent(moduleId);
  
  modal.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <h3>${moduleContent.title}</h3>
        <button class="close-modal" onclick="closeModuleModal()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body">
        ${moduleContent.content}
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeModuleModal()">Close</button>
        <button class="btn btn-primary" onclick="completeModule('${moduleId}')">
          <i class="fas fa-check"></i> Mark as Complete
        </button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  // Start timer for this module
  startModuleTimer(moduleId);
}

function closeModuleModal() {
  const modal = document.querySelector('.module-modal');
  if (modal) {
    modal.remove();
  }
}

function completeModule(moduleId) {
  const module = trainingData.modules[moduleId];
  module.completed = true;
  module.progress = 100;
  
  // Add time spent (simulate 15-30 minutes)
  const timeSpent = Math.floor(Math.random() * 15) + 15;
  module.timeSpent += timeSpent;
  trainingData.totalTimeSpent += timeSpent;
  
  // Award certificate if this is the first completion
  if (module.completed && trainingData.certificates < Object.keys(trainingData.modules).length) {
    trainingData.certificates++;
  }
  
  // Add animation to module card
  const moduleCard = document.querySelector(`[data-module="${moduleId}"]`);
  if (moduleCard) {
    moduleCard.classList.add('updating');
    setTimeout(() => moduleCard.classList.remove('updating'), 500);
  }
  
  updateProgressDisplay();
  saveTrainingData();
  closeModuleModal();
  
  // Show success notification
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification(`Module completed! You earned a certificate.`, 'success');
  }
}

function getModuleContent(moduleId) {
  const modules = {
    orientation: {
      title: 'New User Orientation',
      content: `
        <h4>Welcome to Chinhoyi University of Technology!</h4>
        <p>This module will guide you through the essential systems and services available on campus.</p>
        
        <h5>What you'll learn:</h5>
        <ul>
          <li>How to access your student/staff portal</li>
          <li>Understanding campus network policies</li>
          <li>Available IT services and support</li>
          <li>Security best practices</li>
          <li>How to get help when you need it</li>
        </ul>
        
        <h5>Getting Started:</h5>
        <ol>
          <li>Visit the main campus IT portal</li>
          <li>Log in with your university credentials</li>
          <li>Complete your profile setup</li>
          <li>Review the acceptable use policy</li>
          <li>Set up two-factor authentication</li>
        </ol>
        
        <p><strong>Estimated time:</strong> 20-30 minutes</p>
      `
    },
    wifi: {
      title: 'Campus Wi-Fi Setup',
      content: `
        <h4>Connecting to Campus Wi-Fi</h4>
        <p>Learn how to connect your devices to the university's wireless network.</p>
        
        <h5>Step-by-step instructions:</h5>
        <ol>
          <li>Enable Wi-Fi on your device</li>
          <li>Select "CUT-Student" or "CUT-Staff" network</li>
          <li>Enter your university email and password</li>
          <li>Accept the terms and conditions</li>
          <li>Wait for connection confirmation</li>
        </ol>
        
        <h5>Troubleshooting:</h5>
        <ul>
          <li>Forget and reconnect to the network</li>
          <li>Check your password and email</li>
          <li>Restart your device's Wi-Fi</li>
          <li>Contact IT support if issues persist</li>
        </ul>
        
        <p><strong>Network names:</strong></p>
        <ul>
          <li><strong>CUT-Student:</strong> For student devices</li>
          <li><strong>CUT-Staff:</strong> For faculty and staff</li>
          <li><strong>CUT-Guest:</strong> For visitors (limited access)</li>
        </ul>
        
        <p><strong>Estimated time:</strong> 15-20 minutes</p>
      `
    },
    security: {
      title: 'Cybersecurity Basics',
      content: `
        <h4>Stay Safe Online</h4>
        <p>Protect yourself and the university from cyber threats with these essential practices.</p>
        
        <h5>Password Security:</h5>
        <ul>
          <li>Use strong, unique passwords</li>
          <li>Enable two-factor authentication</li>
          <li>Never share your passwords</li>
          <li>Change passwords regularly</li>
        </ul>
        
        <h5>Email Safety:</h5>
        <ul>
          <li>Be cautious with email attachments</li>
          <li>Verify sender identity before clicking links</li>
          <li>Report suspicious emails to IT support</li>
          <li>Never provide personal information via email</li>
        </ul>
        
        <h5>Device Security:</h5>
        <ul>
          <li>Keep software updated</li>
          <li>Install antivirus software</li>
          <li>Lock your device when not in use</li>
          <li>Use secure networks only</li>
        </ul>
        
        <h5>Data Protection:</h5>
        <ul>
          <li>Backup important data regularly</li>
          <li>Use encrypted storage for sensitive files</li>
          <li>Be careful with public Wi-Fi</li>
          <li>Log out of accounts when finished</li>
        </ul>
        
        <p><strong>Estimated time:</strong> 25-35 minutes</p>
      `
    },
    helpdesk: {
      title: 'Helpdesk & Support',
      content: `
        <h4>Getting Help When You Need It</h4>
        <p>Learn how to access IT support and submit support requests effectively.</p>
        
        <h5>Support Channels:</h5>
        <ul>
          <li><strong>Online Portal:</strong> Submit tickets 24/7</li>
          <li><strong>Phone Support:</strong> +263 67 212 9451</li>
          <li><strong>Email:</strong> ictsupport@cut.ac.zw</li>
          <li><strong>Walk-in:</strong> IT Support Office, Main Campus</li>
        </ul>
        
        <h5>Submitting a Support Ticket:</h5>
        <ol>
          <li>Log into the IT support portal</li>
          <li>Click "New Ticket"</li>
          <li>Select the appropriate category</li>
          <li>Provide detailed description of the issue</li>
          <li>Attach screenshots if helpful</li>
          <li>Submit and wait for response</li>
        </ol>
        
        <h5>Ticket Categories:</h5>
        <ul>
          <li><strong>Network Issues:</strong> Wi-Fi, internet connectivity</li>
          <li><strong>Hardware Problems:</strong> Computer, printer, device issues</li>
          <li><strong>Software Support:</strong> Application installation, updates</li>
          <li><strong>Account Issues:</strong> Login problems, password resets</li>
          <li><strong>General Inquiry:</strong> Questions, information requests</li>
        </ul>
        
        <h5>Response Times:</h5>
        <ul>
          <li><strong>Critical Issues:</strong> 2-4 hours</li>
          <li><strong>High Priority:</strong> 1-2 business days</li>
          <li><strong>Medium Priority:</strong> 3-5 business days</li>
          <li><strong>Low Priority:</strong> 1-2 weeks</li>
        </ul>
        
        <p><strong>Estimated time:</strong> 15-25 minutes</p>
      `
    }
  };
  
  return modules[moduleId] || { title: 'Module', content: 'Content not available.' };
}

function startModuleTimer(moduleId) {
  // This would start a timer for tracking time spent
  // For now, we'll simulate it
  console.log(`Started timer for module: ${moduleId}`);
}

function downloadResource(resourceType) {
  // Simulate resource download
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Resource download started!', 'info');
  }
  
  // In a real implementation, this would trigger a file download
  console.log(`Downloading resource: ${resourceType}`);
}

function openVideoTutorials() {
  // Simulate opening video tutorials
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Opening video tutorials...', 'info');
  }
  
  // In a real implementation, this would open a video gallery
  console.log('Opening video tutorials');
}

function openFAQ() {
  // Redirect to contact/FAQ page
  window.location.href = 'contact.html';
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('module-modal')) {
    closeModuleModal();
  }
});

// Initialize with some demo progress
setTimeout(() => {
  // Add some initial progress for demo
  if (trainingData.totalTimeSpent === 0) {
    trainingData.modules.orientation.progress = 25;
    trainingData.modules.wifi.progress = 10;
    updateProgressDisplay();
    saveTrainingData();
  }
}, 1000);
