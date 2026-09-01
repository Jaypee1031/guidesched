// GuideSched PWA Installer & ServiceWorker Registration
let deferredPrompt = null;

document.addEventListener('DOMContentLoaded', () => {
  // Register ServiceWorker
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('./service-worker.js')
      .then((reg) => console.log('[PWA] ServiceWorker registered with scope:', reg.scope))
      .catch((err) => console.log('[PWA] ServiceWorker registration failed:', err));
  }

  // Listen for beforeinstallprompt event
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    console.log('[PWA] beforeinstallprompt event triggered');

    // Show custom install buttons/banners if present
    document.querySelectorAll('.pwa-install-btn, .pwa-banner').forEach(el => {
      el.style.display = 'inline-flex';
    });
  });

  // Handle appinstalled event
  window.addEventListener('appinstalled', () => {
    console.log('[PWA] GuideSched App installed successfully!');
    deferredPrompt = null;
    document.querySelectorAll('.pwa-install-btn, .pwa-banner').forEach(el => {
      el.style.display = 'none';
    });
    alert('Thank you for installing GuideSched App!');
  });
});

// Trigger Install App Prompt
function triggerPWAInstall() {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        console.log('[PWA] User accepted the install prompt');
      } else {
        console.log('[PWA] User dismissed the install prompt');
      }
      deferredPrompt = null;
    });
  } else {
    // Show instruction modal if automatic prompt is not ready or user is on Safari / iOS / Desktop
    showPWAInstructions();
  }
}

// Show Install App Instructions Modal
function showPWAInstructions() {
  let modal = document.getElementById('pwaModal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'pwaModal';
    modal.innerHTML = `
      <div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(33,27,54,0.6); z-index:99999; display:flex; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#fff; border-radius:16px; max-width:440px; width:100%; padding:24px; box-shadow:0 20px 40px rgba(0,0,0,0.25); text-align:center; position:relative;">
          <button onclick="document.getElementById('pwaModal').remove()" style="position:absolute; top:14px; right:14px; background:none; border:none; font-size:20px; font-weight:700; color:#726C87; cursor:pointer;">&times;</button>
          
          <div style="width:56px; height:56px; background:#EDE6FB; color:#6D28D9; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          </div>

          <h3 style="font-size:18px; color:#2B1153; margin-bottom:6px;">Download GuideSched App</h3>
          <p style="font-size:13px; color:#726C87; margin-bottom:16px;">Install GuideSched on your Android phone, iPhone, or Desktop PC for 1-click access & instant notifications!</p>

          <div style="text-align:left; background:#F6F3FD; border-radius:10px; padding:12px 16px; font-size:12.5px; color:#211B36; margin-bottom:16px; line-height:1.5;">
            <div style="font-weight:700; margin-bottom:4px;">📱 On Android / Chrome:</div>
            Tap the <strong>three dots (⋮)</strong> menu in browser & select <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong>.
            <div style="font-weight:700; margin-top:8px; margin-bottom:4px;">🍎 On iPhone / iOS Safari:</div>
            Tap the <strong>Share button</strong> <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg> at the bottom, then scroll and select <strong>"Add to Home Screen"</strong>.
          </div>

          <div style="display:flex; gap:10px;">
            <a href="download-app.php" style="flex:1; padding:10px 14px; background:#6D28D9; color:#fff; border-radius:99px; text-decoration:none; font-weight:700; font-size:13px; display:inline-block;">Download Page</a>
            <button onclick="document.getElementById('pwaModal').remove()" style="flex:1; padding:10px 14px; background:#EDE6FB; color:#6D28D9; border:none; border-radius:99px; font-weight:700; font-size:13px; cursor:pointer;">Close</button>
          </div>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
  }
}
