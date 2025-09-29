// Gallery JavaScript
let galleryData = [];
let currentFilter = 'all';
let currentView = 'grid';
let currentImageIndex = 0;
let isLoading = false;

// Sample gallery data
const sampleImages = [
  {
    id: 1,
    title: "University Entrance",
    description: "The main entrance to Chinhoyi University of Technology, showcasing the modern architecture and welcoming atmosphere.",
    image: "https://upload.wikimedia.org/wikipedia/commons/3/3f/Entrance_into_Chinhoyi_University_of_Technology.jpg",
    category: "campus",
    date: "2024-01-15"
  },
  {
    id: 2,
    title: "Student Life at CUT",
    description: "Vibrant student life activities and campus community events that bring students together.",
    image: "https://scontent.cdninstagram.com/v/t51.2885-15/428624317_18348124803245671_7747814972638340976_n.jpg",
    category: "students",
    date: "2024-02-10"
  },
  {
    id: 3,
    title: "Computer Lab",
    description: "State-of-the-art computer laboratory equipped with modern technology for student learning.",
    image: "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&h=600&fit=crop",
    category: "technology",
    date: "2024-01-20"
  },
  {
    id: 4,
    title: "Library Interior",
    description: "The university library with its extensive collection of books and digital resources.",
    image: "https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&h=600&fit=crop",
    category: "campus",
    date: "2024-01-25"
  },
  {
    id: 5,
    title: "Graduation Ceremony",
    description: "Annual graduation ceremony celebrating the achievements of our students.",
    image: "https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&h=600&fit=crop",
    category: "events",
    date: "2024-03-15"
  },
  {
    id: 6,
    title: "Research Laboratory",
    description: "Advanced research facilities where students and faculty conduct cutting-edge research.",
    image: "https://images.unsplash.com/photo-1532094341604-801e69e3abf1?w=800&h=600&fit=crop",
    category: "technology",
    date: "2024-02-05"
  },
  {
    id: 7,
    title: "Student Dormitories",
    description: "Modern student accommodation facilities providing comfortable living spaces.",
    image: "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop",
    category: "campus",
    date: "2024-01-30"
  },
  {
    id: 8,
    title: "Sports Complex",
    description: "Athletic facilities and sports complex for student recreation and fitness.",
    image: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=800&h=600&fit=crop",
    category: "students",
    date: "2024-02-20"
  },
  {
    id: 9,
    title: "IT Support Center",
    description: "Dedicated IT support center providing technical assistance to students and staff.",
    image: "https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&h=600&fit=crop",
    category: "technology",
    date: "2024-02-15"
  },
  {
    id: 10,
    title: "Cultural Festival",
    description: "Annual cultural festival celebrating diversity and cultural heritage.",
    image: "https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop",
    category: "events",
    date: "2024-03-01"
  },
  {
    id: 11,
    title: "Campus Garden",
    description: "Beautiful campus gardens providing peaceful study and relaxation areas.",
    image: "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop",
    category: "campus",
    date: "2024-02-25"
  },
  {
    id: 12,
    title: "Student Study Group",
    description: "Students collaborating in study groups, fostering academic excellence.",
    image: "https://images.unsplash.com/photo-1522202176988-66273c79fd81?w=800&h=600&fit=crop",
    category: "students",
    date: "2024-03-05"
  }
];

// Initialize the gallery
document.addEventListener('DOMContentLoaded', function() {
  loadGalleryData();
  renderGallery();
  setupEventListeners();
});

function loadGalleryData() {
  // Load from localStorage or use sample data
  const stored = localStorage.getItem('galleryData');
  if (stored) {
    galleryData = JSON.parse(stored);
  } else {
    galleryData = [...sampleImages];
    saveGalleryData();
  }
}

function saveGalleryData() {
  localStorage.setItem('galleryData', JSON.stringify(galleryData));
}

function setupEventListeners() {
  // Close modal when clicking outside
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('image-modal')) {
      closeImageModal();
    }
  });
  
  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    if (document.getElementById('imageModal').classList.contains('active')) {
      if (e.key === 'Escape') {
        closeImageModal();
      } else if (e.key === 'ArrowLeft') {
        previousImage();
      } else if (e.key === 'ArrowRight') {
        nextImage();
      }
    }
  });
}

function renderGallery() {
  const galleryGrid = document.getElementById('galleryGrid');
  const filteredImages = getFilteredImages();
  
  galleryGrid.innerHTML = '';
  
  filteredImages.forEach((image, index) => {
    const galleryItem = createGalleryItem(image, index);
    galleryGrid.appendChild(galleryItem);
  });
}

function createGalleryItem(image, index) {
  const item = document.createElement('div');
  item.className = `gallery-item ${currentView === 'list' ? 'list-view' : ''}`;
  item.onclick = () => openImageModal(index);
  
  const categoryClass = getCategoryClass(image.category);
  const formattedDate = formatDate(image.date);
  
  item.innerHTML = `
    <img src="${image.image}" alt="${image.title}" loading="lazy">
    <div class="gallery-item-content">
      <h3>${image.title}</h3>
      <p>${image.description}</p>
      <div class="gallery-item-meta">
        <span class="gallery-category ${categoryClass}">${getCategoryName(image.category)}</span>
        <span class="gallery-date">${formattedDate}</span>
      </div>
    </div>
  `;
  
  return item;
}

function getCategoryClass(category) {
  const classes = {
    campus: 'campus',
    students: 'students',
    technology: 'technology',
    events: 'events'
  };
  return classes[category] || 'campus';
}

function getCategoryName(category) {
  const names = {
    campus: 'Campus',
    students: 'Student Life',
    technology: 'Technology',
    events: 'Events'
  };
  return names[category] || 'Campus';
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
}

function filterGallery(filter) {
  currentFilter = filter;
  
  // Update filter buttons
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
  
  // Add filtering animation
  const items = document.querySelectorAll('.gallery-item');
  items.forEach(item => {
    item.classList.add('filtering');
  });
  
  setTimeout(() => {
    renderGallery();
  }, 300);
  
  if (window.CUTApp && window.CUTApp.showNotification) {
    window.CUTApp.showNotification(`Filtered by ${getCategoryName(filter)}`, 'info');
  }
}

function getFilteredImages() {
  if (currentFilter === 'all') {
    return galleryData;
  }
  return galleryData.filter(image => image.category === currentFilter);
}

function setView(view) {
  currentView = view;
  
  // Update view buttons
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  document.querySelector(`[data-view="${view}"]`).classList.add('active');
  
  // Update gallery items
  const items = document.querySelectorAll('.gallery-item');
  items.forEach(item => {
    if (view === 'list') {
      item.classList.add('list-view');
    } else {
      item.classList.remove('list-view');
    }
  });
  
  // Update gallery grid
  const galleryGrid = document.getElementById('galleryGrid');
  if (view === 'list') {
    galleryGrid.classList.add('list-view');
  } else {
    galleryGrid.classList.remove('list-view');
  }
}

function openImageModal(index) {
  const filteredImages = getFilteredImages();
  currentImageIndex = index;
  
  const image = filteredImages[index];
  const modal = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  const imageTitle = document.getElementById('imageTitle');
  const imageDescription = document.getElementById('imageDescription');
  const imageDate = document.getElementById('imageDate');
  const imageCategory = document.getElementById('imageCategory');
  
  modalImage.src = image.image;
  modalImage.alt = image.title;
  imageTitle.textContent = image.title;
  imageDescription.textContent = image.description;
  imageDate.textContent = formatDate(image.date);
  imageCategory.textContent = getCategoryName(image.category);
  
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeImageModal() {
  const modal = document.getElementById('imageModal');
  modal.classList.remove('active');
  document.body.style.overflow = 'auto';
}

function previousImage() {
  const filteredImages = getFilteredImages();
  currentImageIndex = (currentImageIndex - 1 + filteredImages.length) % filteredImages.length;
  openImageModal(currentImageIndex);
}

function nextImage() {
  const filteredImages = getFilteredImages();
  currentImageIndex = (currentImageIndex + 1) % filteredImages.length;
  openImageModal(currentImageIndex);
}

function loadMoreImages() {
  if (isLoading) return;
  
  isLoading = true;
  const button = document.querySelector('button[onclick="loadMoreImages()"]');
  const originalText = button.innerHTML;
  button.innerHTML = '<div class="loading"></div> Loading...';
  button.disabled = true;
  
  // Simulate loading more images
  setTimeout(() => {
    // Add more sample images
    const newImages = generateMoreImages(galleryData.length + 1);
    galleryData.push(...newImages);
    saveGalleryData();
    
    renderGallery();
    
    button.innerHTML = originalText;
    button.disabled = false;
    isLoading = false;
    
    if (window.CUTApp && window.CUTApp.showNotification) {
      window.CUTApp.showNotification(`Loaded ${newImages.length} more images`, 'success');
    }
  }, 2000);
}

function generateMoreImages(startId) {
  const categories = ['campus', 'students', 'technology', 'events'];
  const titles = [
    'Campus Walkway', 'Student Center', 'Research Lab', 'Sports Event',
    'Library Study Area', 'Computer Workshop', 'Cultural Performance', 'Campus Garden',
    'Student Meeting', 'Technology Demo', 'Graduation Day', 'Campus Tour'
  ];
  
  const descriptions = [
    'Beautiful campus walkway lined with trees and modern architecture.',
    'Vibrant student center where students gather for activities and events.',
    'Advanced research laboratory equipped with cutting-edge technology.',
    'Exciting sports events bringing the campus community together.',
    'Quiet library study area perfect for focused learning.',
    'Hands-on computer workshop teaching practical skills.',
    'Colorful cultural performance showcasing student talents.',
    'Serene campus garden providing peaceful study environment.',
    'Collaborative student meeting fostering academic discussion.',
    'Interactive technology demonstration for students and faculty.',
    'Memorable graduation day celebrating student achievements.',
    'Informative campus tour for prospective students.'
  ];
  
  const newImages = [];
  for (let i = 0; i < 6; i++) {
    const category = categories[Math.floor(Math.random() * categories.length)];
    const title = titles[Math.floor(Math.random() * titles.length)];
    const description = descriptions[Math.floor(Math.random() * descriptions.length)];
    
    newImages.push({
      id: startId + i,
      title: title,
      description: description,
      image: `https://images.unsplash.com/photo-${1500000000000 + Math.random() * 1000000000}?w=800&h=600&fit=crop`,
      category: category,
      date: new Date(Date.now() - Math.random() * 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    });
  }
  
  return newImages;
}
