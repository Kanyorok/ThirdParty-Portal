# Partial Navigation System

This project implements AJAX fragment navigation to avoid full page reloads.

## How it works

1. Sidebar links render with `data-ajax="1"` (`ModuleService::buildNavbar`).
2. Clicks are intercepted in `layouts/app.blade.php` script.
3. A fetch request is made with headers `X-Requested-With: XMLHttpRequest` and `X-Partial: 1`.
4. `ReturnContentFragment` middleware detects these requests and returns only the Blade `@section('content')` (and any
   stacked `scripts`).
5. The `#mainBodyContent` inner HTML is replaced; scripts within the fragment are re-executed.
6. History API (pushState + popstate) preserves navigation/back support.
7. Active sidebar highlighting re-runs on `partial:loaded` event.

## Adding new pages

Ensure your Blade view extends the base layout and defines a `@section('content')`. Sidebar links should include
`data-ajax="1"`.

## Page-specific JS

Register reinitialization callbacks:

```
window.registerPartialInit(function(){
  // e.g., $('[data-toggle="tooltip"]').tooltip();
});
```

## Fallback

If anything fails (non-200, missing fragment), code falls back to full-page reload.

## Server-returned scripts

Inline and external scripts inside the fragment are executed in order. Prefer `@push('scripts')` for maintainability.

## Notes

- Forms still submit normally (full reload). Enhance progressively if needed.
- Avoid placing large global script blocks in fragment content; push them instead.
