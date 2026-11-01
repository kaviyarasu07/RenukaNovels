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
	function applySize() { art.style.setProperty('--rn-read-size', size + 'px'); out.textContent = size + 'px'; }
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


/* Continue reading: remember the last paragraph of each story in this browser.
   Chapter page saves it; the Ongoing page lists it. (Logged-in sync arrives with the login module.) */
(function () {
	var KEY = 'rn_last', MAX = 10;
	function load() { try { var v = JSON.parse(localStorage.getItem(KEY)); return Array.isArray(v) ? v : []; } catch (e) { return []; } }
	function save(list) { try { localStorage.setItem(KEY, JSON.stringify(list.slice(0, MAX))); } catch (e) {} }
	var MON = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	function p2(n) { return (n < 10 ? '0' : '') + n; }
	function stamp(ts) { var d = new Date(ts); return p2(d.getDate()) + '-' + MON[d.getMonth()] + '-' + String(d.getFullYear()).slice(-2) + ' ' + p2(d.getHours()) + ':' + p2(d.getMinutes()); }

	/* ---- chapter page: track the paragraph the reader is on ---- */
	var art = document.getElementById('rn-article');
	if (art && art.getAttribute('data-story-id') !== '0') {
		var paras = art.querySelectorAll('p');
		var d = art.dataset, timer = null;
		var m = /^#p(\d+)$/.exec(location.hash || '');
		if (m && paras[+m[1]]) { setTimeout(function () { paras[+m[1]].scrollIntoView({ block: 'start' }); }, 150); }
		var currentPara = function () {
			var idx = 0;
			for (var i = 0; i < paras.length; i++) { if (paras[i].getBoundingClientRect().bottom > 110) { idx = i; break; } idx = i; }
			return idx;
		};
		var record = function () {
			if (!paras.length) { return; }
			var idx = currentPara();
			var rec = { s: d.storyId, st: d.storyTitle, su: d.storyUrl, c: d.chapterId, cn: +d.chapterNo, ct: d.chapterTitle, u: d.url, img: d.cover, pal: +d.pal, p: idx, n: paras.length, pct: Math.round(((idx + 1) / paras.length) * 100), ts: Date.now() };
			var list = load().filter(function (x) { return x.s !== rec.s; });
			list.unshift(rec);
			save(list);
		};
		record();
		window.addEventListener('scroll', function () { clearTimeout(timer); timer = setTimeout(record, 400); }, { passive: true });
		window.addEventListener('pagehide', record);
	}

	/* ---- Ongoing page: list the saved stories ---- */
	var box = document.getElementById('rn-continue');
	var listEl = document.getElementById('rn-contlist');
	if (box && listEl) {
		var items = load();
		var emptyMsg = document.getElementById('rn-cont-empty');
		if (!items.length) { if (emptyMsg) { emptyMsg.hidden = false; } return; }
		var PAL = [['#BFE6E2','#FFF8E6','#6FBDB6','#0A6F6D'],['#D6EEF5','#FFF8E6','#8AC4DA','#3A89A8'],['#FADBD3','#FFF3E0','#F0A898','#C9604C'],['#FFF0C9','#FFFFFF','#F3CC6B','#D9A23B']];
		var NS = 'http://www.w3.org/2000/svg';
		var cover = function (it) {
			if (it.img) { var im = document.createElement('img'); im.className = 'rn-cover'; im.alt = ''; im.src = it.img; return im; }
			var c = PAL[(it.pal || 0) % 4];
			var svg = document.createElementNS(NS, 'svg');
			svg.setAttribute('viewBox', '0 0 120 170'); svg.setAttribute('class', 'rn-cover'); svg.setAttribute('aria-hidden', 'true');
			svg.innerHTML = '<rect width="120" height="170" fill="' + c[0] + '"/><circle cx="60" cy="56" r="24" fill="' + c[1] + '"/><path d="M0 104Q30 88 60 104T120 100V170H0Z" fill="' + c[2] + '" opacity=".75"/><path d="M0 126Q30 110 60 126T120 122V170H0Z" fill="' + c[2] + '"/><path d="M0 148Q30 134 60 148T120 144V170H0Z" fill="' + c[3] + '"/>';
			return svg;
		};
		var el = function (tag, cls, text) { var e = document.createElement(tag); if (cls) { e.className = cls; } if (text) { e.textContent = text; } return e; };
		items.slice(0, 6).forEach(function (it) {
			var card = el('div', 'rn-cont');
			card.appendChild(cover(it));
			var body = el('div', 'rn-contbody');
			var t = el('strong', '', it.st); t.lang = 'ta';
			body.appendChild(t);
			body.appendChild(el('span', '', 'Chapter ' + it.cn + ' · Paragraph ' + (it.p + 1) + ' of ' + it.n + ' · ' + it.pct + '% read'));
			body.appendChild(el('span', '', 'Last read ' + stamp(it.ts)));
			var bar = el('div', 'rn-prog'); var fill = document.createElement('i'); fill.style.width = it.pct + '%'; bar.appendChild(fill);
			body.appendChild(bar);
			card.appendChild(body);
			var a = el('a', 'rn-btn', 'Continue reading'); a.href = it.u + '#p' + it.p;
			card.appendChild(a);
			listEl.appendChild(card);
		});
		box.hidden = false;
	}
})();

/* My Account: quick-fill buttons for announcements. */
(function () {
	var ta = document.getElementById('dm_text');
	if (!ta) { return; }
	document.querySelectorAll('[data-fill]').forEach(function (b) {
		b.addEventListener('click', function () { ta.value = b.getAttribute('data-fill'); ta.focus(); });
	});
})();


/* Comment box: grows while typing, counts characters. */
(function () {
	document.querySelectorAll('textarea[data-grow]').forEach(function (ta) {
		var box = ta.closest('.rn-compbox');
		var counter = box ? box.querySelector('[data-count]') : null;
		function fit() { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 260) + 'px'; if (counter) { counter.textContent = ta.value.length; } }
		ta.addEventListener('input', fit);
		fit();
	});
})();


/* ---------- Comments: 5 newest, "show more" adds 10; only the newest reply, "show all replies" ---------- */
(function () {
	var list = document.getElementById('rn-cmtlist');
	if (!list) { return; }
	var groups = [].slice.call(list.querySelectorAll('[data-group]'));
	var more = document.getElementById('rn-cmt-more');
	var shown = 5;
	function paint() {
		groups.forEach(function (g, i) { g.hidden = i >= shown; });
		var left = groups.length - shown;
		if (more) {
			more.hidden = left <= 0;
			if (left > 0) { more.textContent = 'Show ' + Math.min(10, left) + ' more comment' + (Math.min(10, left) === 1 ? '' : 's') + ' (' + left + ' left)'; }
		}
	}
	if (more) { more.addEventListener('click', function () { shown += 10; paint(); }); }
	paint();

	groups.forEach(function (g) {
		var ul = g.querySelector('[data-replies]');
		if (!ul) { return; }
		var reps = [].slice.call(ul.children);
		if (reps.length < 2) { return; }
		var open = false;
		var btn = document.createElement('button');
		btn.type = 'button'; btn.className = 'rn-more-btn rn-allrep';
		function sync() {
			reps.forEach(function (r, i) { r.hidden = !open && i < reps.length - 1; });
			btn.textContent = open ? 'Hide replies' : 'Show all replies (' + reps.length + ')';
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
		btn.addEventListener('click', function () { open = !open; sync(); });
		ul.parentNode.insertBefore(btn, ul);
		sync();
	});

	/* A link to one comment (after posting) must open its comment, even if it is further down. */
	var m = /^#comment-(\d+)$/.exec(location.hash || '');
	if (m) {
		var el = document.getElementById('comment-' + m[1]);
		if (el) {
			var g = el.closest('[data-group]');
			if (g) { shown = Math.max(shown, groups.indexOf(g) + 1); paint(); }
			var rep = el.closest('.rn-rep');
			if (rep) { rep.hidden = false; }
			el.scrollIntoView({ block: 'center' });
		}
	}
})();

/* ---------- Reading page: no copying, sentence highlighter, bookmark ---------- */
(function () {
	var art = document.getElementById('rn-article');
	if (!art) { return; }
	var d = art.dataset;
	var status = d.status || 'guest';
	var canMark = status === 'approved';
	var marks = { bm: null, hl: [] };
	try { marks = JSON.parse(d.marks || '{}') || marks; } catch (e) {}
	marks.hl = marks.hl || [];

	/* Stop copying: no selection, copy, cut, right-click menu or dragging inside the story text. */
	['copy', 'cut', 'contextmenu', 'dragstart', 'selectstart'].forEach(function (ev) {
		art.addEventListener(ev, function (e) { e.preventDefault(); });
	});

	/* Wrap each sentence so it can be tapped. */
	var paras = [].slice.call(art.querySelectorAll('p'));
	paras.forEach(function (p, pi) {
		var si = 0;
		var walker = document.createTreeWalker(p, NodeFilter.SHOW_TEXT, null);
		var nodes = [];
		while (walker.nextNode()) { nodes.push(walker.currentNode); }
		nodes.forEach(function (node) {
			if (!node.nodeValue.trim()) { return; }
			var parts = node.nodeValue.match(/[^.!?]+[.!?]*["”’)”’]*\s*/g) || [node.nodeValue];
			var frag = document.createDocumentFragment();
			parts.forEach(function (txt) {
				var s = document.createElement('span');
				s.className = 'rn-s'; s.setAttribute('data-p', pi); s.setAttribute('data-s', si++);
				s.textContent = txt;
				frag.appendChild(s);
			});
			node.parentNode.replaceChild(frag, node);
		});
	});
	function sentEl(p, s) { return art.querySelector('.rn-s[data-p="' + p + '"][data-s="' + s + '"]'); }
	function setColor(el, color) {
		['yellow', 'green', 'pink'].forEach(function (c) { el.classList.remove('hl-' + c); });
		if (color) { el.classList.add('hl-' + color); }
	}
	marks.hl.forEach(function (h) { var el = sentEl(h.p, h.s); if (el) { setColor(el, h.c); } });

	/* Small message above the toolbar. */
	var toastEl = null, toastTimer = null;
	function toast(msg, linkText, linkUrl) {
		if (!toastEl) { toastEl = document.createElement('div'); toastEl.className = 'rn-toast'; toastEl.setAttribute('role', 'status'); document.body.appendChild(toastEl); }
		toastEl.textContent = msg + ' ';
		if (linkText) { var a = document.createElement('a'); a.href = linkUrl; a.textContent = linkText; toastEl.appendChild(a); }
		toastEl.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { toastEl.hidden = true; }, 4000);
	}
	function needAccount() {
		if (status === 'guest') { toast('Log in to save bookmarks and highlights.', 'Log in', d.login); }
		else if (status === 'pending') { toast('Your account is waiting for approval. You can save bookmarks and highlights once it is approved.'); }
		else { toast('Bookmarks and highlights are not available for this account.'); }
	}
	function send(data) {
		var fd = new FormData();
		fd.append('action', 'dm_mark'); fd.append('nonce', d.nonce); fd.append('chapter', d.chapterId);
		Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
		return fetch(d.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
			if (!j || !j.success) { throw new Error('failed'); }
		}).catch(function () { toast('Could not save. Please check your connection and try again.'); throw new Error('failed'); });
	}

	/* Bookmark: saves this chapter and the paragraph at the top of the screen. */
	var bmBtn = document.getElementById('rn-bm');
	function currentPara() {
		var idx = 0;
		for (var i = 0; i < paras.length; i++) { if (paras[i].getBoundingClientRect().bottom > 110) { idx = i; break; } idx = i; }
		return idx;
	}
	function paintBm() {
		var on = marks.bm !== null && marks.bm !== undefined;
		bmBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
		bmBtn.classList.toggle('on', on);
		paras.forEach(function (p, i) { p.classList.toggle('rn-bm', on && i === marks.bm); });
	}
	paintBm();
	bmBtn.addEventListener('click', function () {
		if (!canMark) { needAccount(); return; }
		if (marks.bm !== null && marks.bm !== undefined) {
			send({ op: 'bm_clear' }).then(function () { marks.bm = null; paintBm(); toast('Bookmark removed.'); }).catch(function () {});
		} else {
			var p = currentPara();
			send({ op: 'bm_set', para: p }).then(function () { marks.bm = p; paintBm(); toast('Bookmarked at paragraph ' + (p + 1) + '.', 'See bookmarks', d.url.replace(/chapter\/.*$/, 'my-account/?tab=bookmarks')); }).catch(function () {});
		}
	});

	/* Highlighter: tap a sentence to colour it, tap again to clear. */
	var hlBtn = document.getElementById('rn-hl');
	var bar = document.getElementById('rn-hlbar');
	var mode = false, color = 'yellow';
	function paintMode() {
		hlBtn.setAttribute('aria-pressed', mode ? 'true' : 'false');
		hlBtn.classList.toggle('on', mode);
		bar.hidden = !mode;
		art.classList.toggle('rn-hlmode', mode);
		[].forEach.call(bar.querySelectorAll('.rn-sw[data-color]'), function (b) { b.classList.toggle('sel', b.getAttribute('data-color') === color); });
	}
	hlBtn.addEventListener('click', function () {
		if (!canMark) { needAccount(); return; }
		mode = !mode; paintMode();
		if (mode) { toast('Tap a sentence to highlight it.'); }
	});
	document.getElementById('rn-hl-done').addEventListener('click', function () { mode = false; paintMode(); });
	[].forEach.call(bar.querySelectorAll('.rn-sw[data-color]'), function (b) {
		b.addEventListener('click', function () { color = b.getAttribute('data-color'); paintMode(); });
	});
	art.addEventListener('click', function (e) {
		if (!mode) { return; }
		var el = e.target.closest ? e.target.closest('.rn-s') : null;
		if (!el) { return; }
		var p = +el.getAttribute('data-p'), s = +el.getAttribute('data-s');
		var cur = ['yellow', 'green', 'pink'].filter(function (c) { return el.classList.contains('hl-' + c); })[0] || '';
		if (color === 'erase' || cur === color) {
			if (!cur) { return; }
			setColor(el, '');
			send({ op: 'hl_clear', para: p, sent: s }).catch(function () { setColor(el, cur); });
		} else {
			setColor(el, color);
			send({ op: 'hl_set', para: p, sent: s, color: color, snippet: el.textContent.trim().slice(0, 100) }).catch(function () { setColor(el, cur); });
		}
	});
})();
