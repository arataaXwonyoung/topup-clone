// Service Worker for Takapedia PWA
const CACHE_NAME = 'takapedia-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Files to cache for offline functionality
const ESSENTIAL_CACHE_FILES = [
  '/',
  '/offline.html',
  '/manifest.json',
  '/css/app.css',
  '/js/app.js',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png'
];

// Files to cache on demand
const CACHE_ON_DEMAND = [
  '/user/dashboard',
  '/user/orders',
  '/cek-transaksi',
  '/leaderboard'
];

// Install event - cache essential files
self.addEventListener('install', event => {
  console.log('Service Worker: Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Caching essential files');
        return cache.addAll(ESSENTIAL_CACHE_FILES);
      })
      .then(() => {
        console.log('Service Worker: Installation complete');
        return self.skipWaiting(); // Activate immediately
      })
      .catch(error => {
        console.error('Service Worker: Installation failed', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker: Activating...');
  
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Service Worker: Deleting old cache', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log('Service Worker: Activation complete');
      return self.clients.claim(); // Take control of all clients
    })
  );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip external URLs
  if (url.origin !== location.origin) {
    return;
  }
  
  // Handle different types of requests
  if (url.pathname.startsWith('/api/')) {
    // API requests - network first, cache fallback
    event.respondWith(networkFirst(request));
  } else if (url.pathname.includes('/images/')) {
    // Images - cache first, network fallback
    event.respondWith(cacheFirst(request));
  } else if (url.pathname.includes('.css') || url.pathname.includes('.js')) {
    // Static assets - cache first
    event.respondWith(cacheFirst(request));
  } else {
    // HTML pages - network first with offline fallback
    event.respondWith(networkFirstWithOffline(request));
  }
});

// Network first strategy
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline response for failed API calls
    return new Response(
      JSON.stringify({ 
        error: 'Offline', 
        message: 'No network connection available' 
      }), 
      { 
        status: 503,
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

// Cache first strategy
async function cacheFirst(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    // Update cache in background
    fetch(request).then(response => {
      if (response.ok) {
        caches.open(CACHE_NAME).then(cache => {
          cache.put(request, response);
        });
      }
    }).catch(() => {
      // Silent fail for background updates
    });
    
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.error('Failed to fetch resource:', request.url);
    throw error;
  }
}

// Network first with offline fallback for HTML pages
async function networkFirstWithOffline(request) {
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline page for failed navigation
    const offlineResponse = await caches.match(OFFLINE_URL);
    if (offlineResponse) {
      return offlineResponse;
    }
    
    // Fallback offline response
    return new Response(
      `
      <!DOCTYPE html>
      <html lang="id">
      <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Offline - Takapedia</title>
          <style>
              body { 
                  font-family: -apple-system, BlinkMacSystemFont, sans-serif;
                  background: #0E0E0F;
                  color: #fff;
                  text-align: center;
                  padding: 50px 20px;
              }
              .offline-icon { font-size: 64px; margin-bottom: 20px; }
              .retry-btn {
                  background: #FFEA00;
                  color: #000;
                  border: none;
                  padding: 12px 24px;
                  border-radius: 8px;
                  font-weight: bold;
                  cursor: pointer;
                  margin-top: 20px;
              }
          </style>
      </head>
      <body>
          <div class="offline-icon">📱</div>
          <h1>You're Offline</h1>
          <p>Check your internet connection and try again.</p>
          <button class="retry-btn" onclick="location.reload()">Retry</button>
      </body>
      </html>
      `,
      { 
        status: 200,
        headers: { 'Content-Type': 'text/html' }
      }
    );
  }
}

// Background sync for failed requests
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    console.log('Service Worker: Background sync triggered');
    event.waitUntil(syncData());
  }
});

async function syncData() {
  // Implement background sync logic here
  // For example, retry failed order submissions
  console.log('Service Worker: Syncing data...');
}

// Push notification handling
self.addEventListener('push', event => {
  if (!event.data) return;
  
  try {
    const data = event.data.json();
    const options = {
      body: data.body,
      icon: '/images/icons/icon-192x192.png',
      badge: '/images/icons/icon-96x96.png',
      image: data.image,
      data: data.data,
      actions: [
        {
          action: 'view',
          title: 'View',
          icon: '/images/icons/view-icon.png'
        },
        {
          action: 'close',
          title: 'Close',
          icon: '/images/icons/close-icon.png'
        }
      ],
      requireInteraction: data.requireInteraction || false,
      silent: false,
      vibrate: [200, 100, 200]
    };
    
    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  } catch (error) {
    console.error('Push notification error:', error);
  }
});

// Notification click handling
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.action === 'view' && event.notification.data?.url) {
    event.waitUntil(
      self.clients.openWindow(event.notification.data.url)
    );
  } else if (event.action !== 'close') {
    event.waitUntil(
      self.clients.openWindow('/')
    );
  }
});

// Message handling from main thread
self.addEventListener('message', event => {
  if (event.data?.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(CACHE_NAME).then(cache => {
        return cache.addAll(event.data.urls);
      })
    );
  }
  
  if (event.data?.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

console.log('Service Worker: Script loaded');