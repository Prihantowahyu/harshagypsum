/**
 * Harsha Gypsum - Service Worker
 * Progressive Web Application (PWA) Offline & Caching Engine
 */

const CACHE_NAME = 'harsha-gypsum-v1';
const ASSETS_TO_CACHE = [
  './',
  './index.html',
  './manifest.json',
  './css/style.css',
  './js/products.js',
  './js/app.js',
  './assets/images/logo.png',
  './assets/images/icon-192.png',
  './assets/images/icon-512.png',
  './assets/images/apple-touch-icon.png',
  './assets/images/icon-maskable.png',
  './assets/images/hero.jpg',
  './assets/images/list-minimalis.jpg',
  './assets/images/list-klasik.jpg',
  './assets/images/shadowline.jpg',
  './assets/images/wall-moulding.jpg',
  './assets/images/ornamen-center.jpg',
  './assets/images/aksesoris.jpg'
];

// Install Event
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    }).then(() => self.skipWaiting())
  );
});

// Activate Event
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event (Stale-While-Revalidate Strategy)
self.addEventListener('fetch', (event) => {
  // Only cache GET requests
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // For external WhatsApp links, bypass cache
  if (url.hostname.includes('wa.me') || url.hostname.includes('whatsapp')) return;

  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      const fetchPromise = fetch(event.request).then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
        }
        return networkResponse;
      }).catch(() => {
        // Return offline fallback if network fails
        return cachedResponse;
      });

      return cachedResponse || fetchPromise;
    })
  );
});
