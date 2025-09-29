// Network Setup JavaScript
let networkState = {
  devices: {
    router1: { status: 'online', traffic: 0 },
    switch1: { status: 'online', traffic: 0 },
    switch2: { status: 'online', traffic: 0 },
    server1: { status: 'online', traffic: 0 },
    server2: { status: 'online', traffic: 0 },
    pc1: { status: 'online', traffic: 0 },
    pc2: { status: 'online', traffic: 0 },
    pc3: { status: 'online', traffic: 0 }
  },
  connections: [
    { from: 'router1', to: 'switch1', active: false },
    { from: 'switch1', to: 'switch2', active: false },
    { from: 'switch1', to: 'server1', active: false },
    { from: 'switch1', to: 'pc1', active: false },
    { from: 'switch2', to: 'server2', active: false },
    { from: 'switch2', to: 'pc2', active: false },
    { from: 'switch2', to: 'pc3', active: false }
  ],
  isSimulating: false
};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
  initializeNetworkSimulator();
  setupDeviceClickHandlers();
  updateNetworkDisplay();
});

function initializeNetworkSimulator() {
  // Set up initial connection positions
  updateConnectionPositions();
}

function setupDeviceClickHandlers() {
  document.querySelectorAll('.device').forEach(device => {
    device.addEventListener('click', function() {
      const deviceId = this.dataset.device;
      toggleDeviceStatus(deviceId);
      this.classList.add('clicked');
      setTimeout(() => this.classList.remove('clicked'), 300);
    });
  });
}

function updateConnectionPositions() {
  const connections = document.querySelectorAll('.connection');
  
  connections.forEach(connection => {
    const fromDevice = document.querySelector(`[data-device="${connection.dataset.from}"]`);
    const toDevice = document.querySelector(`[data-device="${connection.dataset.to}"]`);
    
    if (fromDevice && toDevice) {
      const fromRect = fromDevice.getBoundingClientRect();
      const toRect = toDevice.getBoundingClientRect();
      const containerRect = document.querySelector('.network-topology').getBoundingClientRect();
      
      const fromX = fromRect.left + fromRect.width / 2 - containerRect.left;
      const fromY = fromRect.top + fromRect.height / 2 - containerRect.top;
      const toX = toRect.left + toRect.width / 2 - containerRect.left;
      const toY = toRect.top + toRect.height / 2 - containerRect.top;
      
      const length = Math.sqrt(Math.pow(toX - fromX, 2) + Math.pow(toY - fromY, 2));
      const angle = Math.atan2(toY - fromY, toX - fromX) * 180 / Math.PI;
      
      connection.style.width = length + 'px';
      connection.style.left = fromX + 'px';
      connection.style.top = fromY + 'px';
      connection.style.transform = `rotate(${angle}deg)`;
    }
  });
}

function toggleDeviceStatus(deviceId) {
  const device = networkState.devices[deviceId];
  if (device) {
    device.status = device.status === 'online' ? 'offline' : 'online';
    updateNetworkDisplay();
    
    // Show notification
    if (window.CUTApp && window.CUTApp.showNotification) {
      const status = device.status === 'online' ? 'online' : 'offline';
      window.CUTApp.showNotification(`${deviceId} is now ${status}`, 'info');
    }
  }
}

function updateNetworkDisplay() {
  // Update device status indicators
  Object.keys(networkState.devices).forEach(deviceId => {
    const deviceElement = document.querySelector(`[data-device="${deviceId}"]`);
    const statusElement = deviceElement?.querySelector('.device-status');
    
    if (statusElement) {
      statusElement.className = `device-status ${networkState.devices[deviceId].status}`;
    }
  });
  
  // Update connection status
  networkState.connections.forEach((connection, index) => {
    const connectionElement = document.querySelectorAll('.connection')[index];
    if (connectionElement) {
      const fromDevice = networkState.devices[connection.from];
      const toDevice = networkState.devices[connection.to];
      
      if (fromDevice.status === 'online' && toDevice.status === 'online' && connection.active) {
        connectionElement.classList.add('active');
      } else {
        connectionElement.classList.remove('active');
      }
    }
  });
}

function simulateNetworkActivity() {
  if (networkState.isSimulating) {
    stopSimulation();
    return;
  }
  
  networkState.isSimulating = true;
  const button = document.querySelector('button[onclick="simulateNetworkActivity()"]');
  button.innerHTML = '<i class="fas fa-stop"></i> Stop Simulation';
  button.classList.remove('btn-primary');
  button.classList.add('btn-secondary');
  
  // Start simulation
  const simulationInterval = setInterval(() => {
    if (!networkState.isSimulating) {
      clearInterval(simulationInterval);
      return;
    }
    
    // Randomly activate connections
    networkState.connections.forEach(connection => {
      const fromDevice = networkState.devices[connection.from];
      const toDevice = networkState.devices[connection.to];
      
      if (fromDevice.status === 'online' && toDevice.status === 'online') {
        connection.active = Math.random() < 0.3;
        
        // Update traffic levels
        if (connection.active) {
          fromDevice.traffic = Math.min(100, fromDevice.traffic + Math.random() * 10);
          toDevice.traffic = Math.min(100, toDevice.traffic + Math.random() * 10);
        }
      }
    });
    
    updateNetworkDisplay();
  }, 1000);
  
  // Store interval ID for cleanup
  networkState.simulationInterval = simulationInterval;
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Network simulation started!', 'success');
  }
}

function stopSimulation() {
  networkState.isSimulating = false;
  
  if (networkState.simulationInterval) {
    clearInterval(networkState.simulationInterval);
  }
  
  // Reset all connections
  networkState.connections.forEach(connection => {
    connection.active = false;
  });
  
  // Reset traffic levels
  Object.keys(networkState.devices).forEach(deviceId => {
    networkState.devices[deviceId].traffic = 0;
  });
  
  const button = document.querySelector('button[onclick="simulateNetworkActivity()"]');
  button.innerHTML = '<i class="fas fa-play"></i> Simulate Activity';
  button.classList.remove('btn-secondary');
  button.classList.add('btn-primary');
  
  updateNetworkDisplay();
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Network simulation stopped', 'info');
  }
}

function resetNetwork() {
  // Reset all devices to online
  Object.keys(networkState.devices).forEach(deviceId => {
    networkState.devices[deviceId].status = 'online';
    networkState.devices[deviceId].traffic = 0;
  });
  
  // Reset all connections
  networkState.connections.forEach(connection => {
    connection.active = false;
  });
  
  // Stop any running simulation
  if (networkState.isSimulating) {
    stopSimulation();
  }
  
  updateNetworkDisplay();
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification('Network reset to default state', 'info');
  }
}

function showNetworkStats() {
  const stats = calculateNetworkStats();
  
  const statsModal = document.createElement('div');
  statsModal.className = 'module-modal active';
  statsModal.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fas fa-chart-bar"></i> Network Statistics</h3>
        <button class="close-modal" onclick="closeStatsModal()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="stats-grid">
          <div class="stat-item">
            <h4>Online Devices</h4>
            <p>${stats.onlineDevices}/${stats.totalDevices}</p>
          </div>
          <div class="stat-item">
            <h4>Active Connections</h4>
            <p>${stats.activeConnections}/${stats.totalConnections}</p>
          </div>
          <div class="stat-item">
            <h4>Average Traffic</h4>
            <p>${stats.averageTraffic.toFixed(1)}%</p>
          </div>
          <div class="stat-item">
            <h4>Network Health</h4>
            <p style="color: ${stats.healthColor}">${stats.healthStatus}</p>
          </div>
        </div>
        
        <h4>Device Details</h4>
        <div class="device-stats">
          ${Object.keys(networkState.devices).map(deviceId => `
            <div class="device-stat">
              <span>${deviceId}</span>
              <span class="status ${networkState.devices[deviceId].status}">${networkState.devices[deviceId].status}</span>
              <span>${networkState.devices[deviceId].traffic.toFixed(1)}%</span>
            </div>
          `).join('')}
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeStatsModal()">Close</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(statsModal);
}

function closeStatsModal() {
  const modal = document.querySelector('.module-modal');
  if (modal) {
    modal.remove();
  }
}

function calculateNetworkStats() {
  const devices = Object.values(networkState.devices);
  const onlineDevices = devices.filter(d => d.status === 'online').length;
  const totalDevices = devices.length;
  const activeConnections = networkState.connections.filter(c => c.active).length;
  const totalConnections = networkState.connections.length;
  const averageTraffic = devices.reduce((sum, d) => sum + d.traffic, 0) / devices.length;
  
  let healthStatus, healthColor;
  if (onlineDevices === totalDevices && averageTraffic < 50) {
    healthStatus = 'Excellent';
    healthColor = '#10b981';
  } else if (onlineDevices >= totalDevices * 0.8 && averageTraffic < 80) {
    healthStatus = 'Good';
    healthColor = '#3b82f6';
  } else if (onlineDevices >= totalDevices * 0.6) {
    healthStatus = 'Fair';
    healthColor = '#f59e0b';
  } else {
    healthStatus = 'Poor';
    healthColor = '#ef4444';
  }
  
  return {
    onlineDevices,
    totalDevices,
    activeConnections,
    totalConnections,
    averageTraffic,
    healthStatus,
    healthColor
  };
}

function showConfigTab(tabName) {
  // Hide all panels
  document.querySelectorAll('.config-panel').forEach(panel => {
    panel.classList.remove('active');
  });
  
  // Remove active class from all tabs
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  
  // Show selected panel
  document.getElementById(tabName + '-config').classList.add('active');
  
  // Activate selected tab
  event.target.classList.add('active');
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('module-modal')) {
    closeStatsModal();
  }
});

// Update connection positions on window resize
window.addEventListener('resize', function() {
  setTimeout(updateConnectionPositions, 100);
});
