/**
 * Global initializer registry for partial page loads.
 * Register functions with window.registerPartialInit(fn)
 * They will run after each fragment navigation (event 'partial:loaded').
 */
(function () {
  window.__partialInits = window.__partialInits || [];
  window.registerPartialInit = function (fn) {
    if (typeof fn === 'function') window.__partialInits.push(fn);
  };
  document.addEventListener('partial:loaded', (e) => {
    window.__partialInits.forEach((fn) => {
      try {
        fn(e);
      } catch (err) {
        console.error('Partial init failed', err);
      }
    });
  });
  // Run once on first load too
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(() => {
      document.dispatchEvent(new CustomEvent('partial:loaded', { detail: { initial: true, url: location.href } }));
    }, 30);
  } else {
    document.addEventListener('DOMContentLoaded', () => {
      document.dispatchEvent(new CustomEvent('partial:loaded', { detail: { initial: true, url: location.href } }));
    });
  }
}());
