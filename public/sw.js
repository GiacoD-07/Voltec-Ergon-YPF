// Responsabilidad: conservar recursos básicos de la interfaz para mejorar su disponibilidad offline.
const CACHE_NAME = 'energhost-v1';
// Recursos mínimos que la PWA conserva para mostrar la interfaz sin conexión.
const ASSETS = [
  '/',
  '/index.html',
  '/styles.css',
  '/app.js',
  '/manifest.json'
];

self.addEventListener('install', (e) => {
  // Instala la caché inicial cuando el navegador registra el service worker.
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
  );
});

self.addEventListener('fetch', (e) => {
  // Usa la caché primero y consulta la red cuando el recurso no está guardado.
  e.respondWith(
    caches.match(e.request).then((res) => res || fetch(e.request))
  );
});