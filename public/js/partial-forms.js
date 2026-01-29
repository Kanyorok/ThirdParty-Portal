// Progressive enhancement: AJAX submit for forms with data-ajax-form="1"
(function () {
  function enhanceForms() {
    document.querySelectorAll('form[data-ajax-form="1"]:not([data-ajax-bound])').forEach((form) => {
      form.setAttribute('data-ajax-bound', '1');
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const action = form.getAttribute('action') || location.href;
        const method = (form.getAttribute('method') || 'POST').toUpperCase();
        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) {
          submitBtn.dataset.originalText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = 'Saving...';
        }
        try {
          const res = await fetch(action, {
            method,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Partial': '1' },
            body: fd,
          });
          if (!res.ok) {
            // fallback full reload
            window.location.href = action;
            return;
          }
          const html = await res.text();
          // Replace main content
          const target = document.getElementById('mainBodyContent');
          if (target) {
            target.innerHTML = html;
            document.dispatchEvent(new CustomEvent('partial:loaded', { detail: { url: action, form: true } }));
          } else {
            window.location.reload();
          }
        } catch (err) {
          console.error('AJAX form failed', err);
          window.location.href = action;
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = submitBtn.dataset.originalText || 'Submit';
          }
        }
      });
    });
  }

  window.registerPartialInit(enhanceForms);
  document.addEventListener('DOMContentLoaded', enhanceForms);
}());
