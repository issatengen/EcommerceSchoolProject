// Search suggestions UX
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const suggestionsBox = document.getElementById('searchSuggestions');
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    if (query.length === 0) {
        suggestionsBox.classList.add('d-none');
        suggestionsBox.innerHTML = '';
        return;
    }
    searchTimeout = setTimeout(function() {
        fetch(`{{ path('app_item_index') }}?q=${encodeURIComponent(query)}&ajax=1`)
            .then(response => response.json())
            .then(data => {
                if (data.results && data.results.length > 0) {
                    suggestionsBox.innerHTML = data.results.map(item =>
                        `<div class="search-suggestion-item" onclick="window.location.href='${item.url}'">
                            <i class="fa fa-search me-2 text-primary"></i>${item.label}
                        </div>`
                    ).join('');
                    suggestionsBox.classList.remove('d-none');
                } else {
                    suggestionsBox.innerHTML = '<div class="search-suggestion-item text-muted">No results found</div>';
                    suggestionsBox.classList.remove('d-none');
                }
            })
            .catch(() => {
                suggestionsBox.innerHTML = '';
                suggestionsBox.classList.add('d-none');
            });
    }, 400);
});
// Hide suggestions on blur
searchInput.addEventListener('blur', function() {
    setTimeout(() => suggestionsBox.classList.add('d-none'), 200);
});