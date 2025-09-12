/**
 * Global initializer registry for partial page loads.
 * Register functions with window.registerPartialInit(fn)
 * They will run after each fragment navigation (event 'partial:loaded').
 */
(function(){
  window.__partialInits = window.__partialInits || [];
  window.registerPartialInit = function(fn){
    if(typeof fn === 'function') window.__partialInits.push(fn);
  };
  document.addEventListener('partial:loaded', function(e){
    window.__partialInits.forEach(function(fn){
      try { fn(e); } catch(err){ console.error('Partial init failed', err); }
    });
  });
  // Run once on first load too
  if(document.readyState === 'complete' || document.readyState === 'interactive'){
    setTimeout(function(){
      document.dispatchEvent(new CustomEvent('partial:loaded', {detail:{initial:true, url:location.href}}));
    }, 30);
  } else {
    document.addEventListener('DOMContentLoaded', function(){
      document.dispatchEvent(new CustomEvent('partial:loaded', {detail:{initial:true, url:location.href}}));
    });
  }
})();
