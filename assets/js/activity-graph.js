// Activity Graph JavaScript
let usageChart, supportChart;
let activityData = {
  totalUsers: 0,
  networkUptime: 99.9,
  supportTickets: 0,
  dataUsage: 0,
  weeklyUsage: [0, 0, 0, 0, 0, 0, 0],
  supportTypes: {
    'Network Issues': 0,
    'Hardware Problems': 0,
    'Software Support': 0,
    'Account Issues': 0,
    'General Inquiry': 0
  },
  recentActivity: []
};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
  initializeCharts();
  loadStoredData();
  updateDisplay();
  startRealTimeUpdates();
});

function initializeCharts() {
  // Weekly Usage Chart
  const usageCtx = document.getElementById('usageChart');
  if (usageCtx) {
    usageChart = new Chart(usageCtx, {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
          label: 'Network Usage (GB)',
          data: activityData.weeklyUsage,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#e2e8f0'
            }
          },
          x: {
            grid: {
              color: '#e2e8f0'
            }
          }
        }
      }
    });
  }

  // Support Types Chart
  const supportCtx = document.getElementById('supportChart');
  if (supportCtx) {
    supportChart = new Chart(supportCtx, {
      type: 'doughnut',
      data: {
        labels: Object.keys(activityData.supportTypes),
        datasets: [{
          data: Object.values(activityData.supportTypes),
          backgroundColor: [
            '#3b82f6',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6'
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20,
              usePointStyle: true
            }
          }
        }
      }
    });
  }
}

function loadStoredData() {
  const stored = localStorage.getItem('activityData');
  if (stored) {
    const parsed = JSON.parse(stored);
    activityData = { ...activityData, ...parsed };
  }
}

function saveData() {
  localStorage.setItem('activityData', JSON.stringify(activityData));
}

function updateDisplay() {
  // Update stat cards
  document.getElementById('totalUsers').textContent = activityData.totalUsers;
  document.getElementById('networkUptime').textContent = activityData.networkUptime.toFixed(1) + '%';
  document.getElementById('supportTickets').textContent = activityData.supportTickets;
  document.getElementById('dataUsage').textContent = activityData.dataUsage.toFixed(1) + ' GB';

  // Update charts
  if (usageChart) {
    usageChart.data.datasets[0].data = activityData.weeklyUsage;
    usageChart.update();
  }

  if (supportChart) {
    supportChart.data.datasets[0].data = Object.values(activityData.supportTypes);
    supportChart.update();
  }

  // Update recent activity
  updateActivityList();
}

function updateActivityList() {
  const activityList = document.getElementById('activityList');
  if (!activityList) return;

  activityList.innerHTML = '';

  if (activityData.recentActivity.length === 0) {
    activityList.innerHTML = '<p style="text-align: center; color: #64748b; padding: 20px;">No recent activity</p>';
    return;
  }

  activityData.recentActivity.slice(0, 10).forEach(activity => {
    const activityItem = document.createElement('div');
    activityItem.className = 'activity-item';
    
    const iconClass = getActivityIconClass(activity.type);
    const timeAgo = getTimeAgo(activity.timestamp);
    
    activityItem.innerHTML = `
      <div class="activity-icon ${iconClass}">
        <i class="fas fa-${getActivityIcon(activity.type)}"></i>
      </div>
      <div class="activity-content">
        <h4>${activity.title}</h4>
        <p>${activity.description}</p>
      </div>
      <div class="activity-time">${timeAgo}</div>
    `;
    
    activityList.appendChild(activityItem);
  });
}

function getActivityIconClass(type) {
  const iconMap = {
    'login': 'login',
    'network': 'network',
    'support': 'support',
    'data': 'data'
  };
  return iconMap[type] || 'support';
}

function getActivityIcon(type) {
  const iconMap = {
    'login': 'user-check',
    'network': 'wifi',
    'support': 'ticket-alt',
    'data': 'download'
  };
  return iconMap[type] || 'info-circle';
}

function getTimeAgo(timestamp) {
  const now = new Date();
  const time = new Date(timestamp);
  const diff = now - time;
  
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);
  
  if (minutes < 1) return 'Just now';
  if (minutes < 60) return `${minutes}m ago`;
  if (hours < 24) return `${hours}h ago`;
  return `${days}d ago`;
}

function simulateActivity() {
  // Simulate random activity
  const activities = [
    {
      type: 'login',
      title: 'User Login',
      description: 'Student logged into campus network'
    },
    {
      type: 'network',
      title: 'Network Activity',
      description: 'High bandwidth usage detected'
    },
    {
      type: 'support',
      title: 'Support Request',
      description: 'New ticket created for network issues'
    },
    {
      type: 'data',
      title: 'Data Transfer',
      description: 'Large file download completed'
    }
  ];

  // Update stats
  activityData.totalUsers += Math.floor(Math.random() * 3) + 1;
  activityData.supportTickets += Math.floor(Math.random() * 2);
  activityData.dataUsage += Math.random() * 5;
  
  // Update weekly usage (add to today's usage)
  const today = new Date().getDay();
  const dayIndex = today === 0 ? 6 : today - 1; // Convert Sunday=0 to Sunday=6
  activityData.weeklyUsage[dayIndex] += Math.random() * 10;

  // Update support types
  const supportTypes = Object.keys(activityData.supportTypes);
  const randomType = supportTypes[Math.floor(Math.random() * supportTypes.length)];
  activityData.supportTypes[randomType] += 1;

  // Add recent activity
  const randomActivity = activities[Math.floor(Math.random() * activities.length)];
  activityData.recentActivity.unshift({
    ...randomActivity,
    timestamp: new Date().toISOString()
  });

  // Keep only last 20 activities
  activityData.recentActivity = activityData.recentActivity.slice(0, 20);

  // Add animation to stat cards
  document.querySelectorAll('.stat-card').forEach(card => {
    card.classList.add('updating');
    setTimeout(() => card.classList.remove('updating'), 500);
  });

  updateDisplay();
  saveData();
  
  // Show notification
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Activity simulation completed!', 'success');
  }
}

function resetData() {
  if (confirm('Are you sure you want to reset all data? This action cannot be undone.')) {
    activityData = {
      totalUsers: 0,
      networkUptime: 99.9,
      supportTickets: 0,
      dataUsage: 0,
      weeklyUsage: [0, 0, 0, 0, 0, 0, 0],
      supportTypes: {
        'Network Issues': 0,
        'Hardware Problems': 0,
        'Software Support': 0,
        'Account Issues': 0,
        'General Inquiry': 0
      },
      recentActivity: []
    };
    
    updateDisplay();
    saveData();
    
    if (window.CUTApp && window.CUTApp.showNotification) {
      window.CUTApp.showNotification('Data reset successfully!', 'info');
    }
  }
}

function exportData() {
  const dataStr = JSON.stringify(activityData, null, 2);
  const dataBlob = new Blob([dataStr], {type: 'application/json'});
  
  const link = document.createElement('a');
  link.href = URL.createObjectURL(dataBlob);
  link.download = `activity-data-${new Date().toISOString().split('T')[0]}.json`;
  link.click();
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Data exported successfully!', 'success');
  }
}

function startRealTimeUpdates() {
  // Simulate real-time updates every 30 seconds
  setInterval(() => {
    // Random chance of activity
    if (Math.random() < 0.3) {
      simulateActivity();
    }
  }, 30000);
}

// Add some initial data for demo
function addInitialData() {
  if (activityData.totalUsers === 0) {
    activityData.totalUsers = 150;
    activityData.supportTickets = 12;
    activityData.dataUsage = 1250.5;
    activityData.weeklyUsage = [120, 135, 110, 145, 160, 80, 90];
    activityData.supportTypes = {
      'Network Issues': 5,
      'Hardware Problems': 3,
      'Software Support': 2,
      'Account Issues': 1,
      'General Inquiry': 1
    };
    
    // Add some recent activities
    const now = new Date();
    activityData.recentActivity = [
      {
        type: 'login',
        title: 'User Login',
        description: 'Student logged into campus network',
        timestamp: new Date(now - 5 * 60000).toISOString()
      },
      {
        type: 'network',
        title: 'Network Activity',
        description: 'High bandwidth usage detected',
        timestamp: new Date(now - 15 * 60000).toISOString()
      },
      {
        type: 'support',
        title: 'Support Request',
        description: 'New ticket created for network issues',
        timestamp: new Date(now - 30 * 60000).toISOString()
      }
    ];
    
    updateDisplay();
    saveData();
  }
}

// Initialize with some demo data
setTimeout(addInitialData, 1000);
