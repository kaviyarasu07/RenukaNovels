/* Menu drawer and reading controls. No dependencies. */
(function () {
	var scrim = document.getElementById('rn-scrim');
	var openBtn = document.getElementById('rn-menu-open');
	var closeBtn = document.getElementById('rn-menu-close');
	function setMenu(open) {
		if (!scrim) { return; }
		scrim.hidden = !open;
		openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
		document.documentElement.style.overflow = open ? 'hidden' : '';
		(open ? closeBtn : openBtn).focus();
	}
	if (openBtn) { openBtn.addEventListener('click', function () { setMenu(true); }); }
	if (closeBtn) { closeBtn.addEventListener('click', function () { setMenu(false); }); }
	if (scrim) { scrim.addEventListener('click', function (e) { if (e.target === scrim) { setMenu(false); } }); }
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && scrim && !scrim.hidden) { setMenu(false); } });

	/* Reading controls (chapter page only). */
	var art = document.getElementById('rn-article');
	if (!art) { return; }
	var size = 18, out = document.getElementById('rn-size');
	function store(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
	function load(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
	function applySize() { document.documentElement.style.setProperty('--rn-read-size', size + 'px'); out.textContent = size + 'px'; }
	var saved = parseInt(load('rn_size'), 10);
	if (saved >= 14 && saved <= 30) { size = saved; }
	applySize();
	document.getElementById('rn-smaller').addEventListener('click', function () { size = Math.max(14, size - 2); store('rn_size', size); applySize(); });
	document.getElementById('rn-larger').addEventListener('click', function () { size = Math.min(30, size + 2); store('rn_size', size); applySize(); });
	function applyMode(m) { if (m === 'night') { document.documentElement.setAttribute('data-mode', 'night'); } else { document.documentElement.removeAttribute('data-mode'); } }
	applyMode(load('rn_mode'));
	document.getElementById('rn-mode').addEventListener('click', function () {
		var n = document.documentElement.getAttribute('data-mode') === 'night' ? 'day' : 'night';
		store('rn_mode', n); applyMode(n);
	});
})();

/* Trending: "See more" shows the extra novels. */
(function () {
	var b = document.getElementById('rn-trend-more');
	if (!b) { return; }
	b.addEventListener('click', function () {
		var open = b.getAttribute('aria-expanded') !== 'true';
		document.querySelectorAll('.rn-trend[data-more]').forEach(function (el) { el.hidden = !open; });
		b.setAttribute('aria-expanded', open ? 'true' : 'false');
		b.textContent = open ? 'Show fewer' : 'See more trending novels';
	});
})();
