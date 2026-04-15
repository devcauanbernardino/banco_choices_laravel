if (window.location.search.includes("error=")) {
  window.history.replaceState({}, document.title, window.location.pathname);
}
