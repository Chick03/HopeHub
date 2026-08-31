// HopeHub - shared client-side behaviour

document.addEventListener('DOMContentLoaded', function () {

    // Toggle amount field vs quantity field based on donation type
    var typeSelect = document.getElementById('donation_type');
    if (typeSelect) {
        var amountGroup = document.getElementById('amount-group');
        var qtyGroup = document.getElementById('quantity-group');
        var shippingInfo = document.getElementById('shipping-info');
        var toggle = function () {
            var isCash = typeSelect.value === 'Cash';
            if (amountGroup) amountGroup.style.display = isCash ? 'block' : 'none';
            if (qtyGroup) qtyGroup.style.display = isCash ? 'none' : 'block';
            if (shippingInfo) shippingInfo.style.display = isCash ? 'none' : 'block';
            var amountInput = document.getElementById('amount');
            if (amountInput) amountInput.required = isCash;
        };
        typeSelect.addEventListener('change', toggle);
        toggle();
    }

    // Confirm before destructive actions (delete buttons)
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // Simple client-side required-field check with inline messaging
    document.querySelectorAll('form.validate').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var valid = true;
            form.querySelectorAll('[required]').forEach(function (field) {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#A3312B';
                } else {
                    field.style.borderColor = '';
                }
            });
            if (!valid) e.preventDefault();
        });
    });

    // Payment method toggle (UPI vs Card fields)
    var methodRadios = document.querySelectorAll('input[name="method"]');
    if (methodRadios.length) {
        var upiFields = document.getElementById('upi-fields');
        var cardFields = document.getElementById('card-fields');
        var updateMethod = function () {
            var selected = document.querySelector('input[name="method"]:checked');
            if (!selected) return;
            if (upiFields) upiFields.style.display = selected.value === 'UPI' ? 'block' : 'none';
            if (cardFields) cardFields.style.display = selected.value === 'Card' ? 'block' : 'none';
        };
        methodRadios.forEach(function (r) { r.addEventListener('change', updateMethod); });
        updateMethod();
    }

    // Public Top Donors leaderboard: the frontend (this JS) calls the PHP
    // backend's JSON API and renders the result itself, matching the
    // "Web Application Server (PHP)" tier in the System Architecture
    // diagram being a separate layer from the Donor Portal/frontend.
    var leaderboardEl = document.getElementById('leaderboard-list');
    if (leaderboardEl) {
        fetch('/hopehub/api/leaderboard.php')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success || !data.leaderboard.length) {
                    leaderboardEl.innerHTML = '<div class="card-body"><p class="help-text">No confirmed cash donations yet &mdash; be the first on the board!</p></div>';
                    return;
                }
                var medals = ['🥇', '🥈', '🥉'];
                var html = '<div class="card-body">';
                data.leaderboard.forEach(function (entry) {
                    var badge = medals[entry.rank - 1] || ('#' + entry.rank);
                    html += '<div class="leaderboard-row">'
                        + '<span class="leaderboard-rank">' + badge + '</span>'
                        + '<span class="leaderboard-name">' + escapeHtml(entry.name) + '</span>'
                        + '<span class="leaderboard-amount">\u20B9' + Number(entry.total_donated).toLocaleString('en-IN') + '</span>'
                        + '</div>';
                });
                html += '</div>';
                leaderboardEl.innerHTML = html;
            })
            .catch(function () {
                leaderboardEl.innerHTML = '<div class="card-body"><p class="help-text">Could not load the leaderboard right now.</p></div>';
            });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
