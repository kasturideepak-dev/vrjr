(function () {
  "use strict";
  var root = document.documentElement;
  root.classList.add("js");
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* Nav */
  var nav = document.querySelector("[data-nav]");
  var toggle = document.querySelector("[data-nav-toggle]");
  var backdrop = document.querySelector("[data-nav-backdrop]");
  function setNav(open) {
    if (!nav || !toggle) return;
    nav.classList.toggle("is-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    nav.setAttribute("aria-hidden", open ? "false" : "true");
    if (backdrop) backdrop.classList.toggle("is-on", open);
    document.body.classList.toggle("is-locked", open);
  }
  function isDrawer() { return window.matchMedia("(max-width: 1099px)").matches; }
  if (nav && isDrawer()) nav.setAttribute("aria-hidden", "true");
  if (toggle) toggle.addEventListener("click", function () { setNav(!nav.classList.contains("is-open")); });
  if (backdrop) backdrop.addEventListener("click", function () { setNav(false); });
  nav && nav.querySelectorAll("a").forEach(function (a) {
    a.addEventListener("click", function () { if (isDrawer()) setNav(false); });
  });
  document.querySelectorAll("[data-sub-toggle]").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      if (!isDrawer()) return;
      e.preventDefault();
      var open = !btn.parentElement.classList.contains("is-open");
      btn.parentElement.classList.toggle("is-open", open);
      btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
  });
  function markCurrentNav() {
    function leaf(p) {
      p = String(p || "").split("#")[0].split("?")[0].replace(/\/+$/, "");
      var n = p.split("/").pop() || "";
      n = n.replace(/\.html$/, "");
      return (!n || n === "index") ? "home" : n;
    }
    var here = leaf(location.pathname);
    document.querySelectorAll(".nav .subnav a, .nav__list > li > a").forEach(function (a) {
      var href = a.getAttribute("href") || "";
      if (href.charAt(0) === "#" || href.indexOf("tel:") === 0 || href.indexOf("mailto:") === 0) return;
      var on = leaf(href) === here;
      a.classList.toggle("is-on", on);
      if (on) a.setAttribute("aria-current", "page");
      else a.removeAttribute("aria-current");
    });
  }
  markCurrentNav();
  window.addEventListener("pageshow", markCurrentNav);

  /* Path steps */
  var pathWrap = document.querySelector("[data-path-pills]");
  if (pathWrap) {
    var pills = Array.prototype.slice.call(pathWrap.querySelectorAll(".path-pill"));
    function setPath(pill) {
      pills.forEach(function (p) {
        var on = p === pill;
        p.classList.toggle("is-on", on);
        p.setAttribute("aria-checked", on ? "true" : "false");
        p.tabIndex = on ? 0 : -1;
      });
    }
    pills.forEach(function (pill, i) {
      pill.addEventListener("click", function () { setPath(pill); });
      pill.addEventListener("keydown", function (e) {
        var next = i;
        if (e.key === "ArrowRight" || e.key === "ArrowDown") next = (i + 1) % pills.length;
        else if (e.key === "ArrowLeft" || e.key === "ArrowUp") next = (i - 1 + pills.length) % pills.length;
        else if (e.key === "Home") next = 0;
        else if (e.key === "End") next = pills.length - 1;
        else return;
        e.preventDefault();
        setPath(pills[next]);
        pills[next].focus();
      });
    });
    setPath(pathWrap.querySelector(".path-pill.is-on") || pills[0]);
  }

  /* Faculty loop */
  var facSlider = document.querySelector("[data-faculty-slider]");
  if (facSlider) {
    var facTrack = facSlider.querySelector(".faculty-track");
    if (facTrack && !reduce) {
      facTrack.innerHTML += facTrack.innerHTML;
      facSlider.classList.add("is-loop");
    }
  }

  /* Testimonials */
  var revView = document.querySelector("[data-reviews]");
  if (revView) {
    var revTrack = revView.querySelector(".reviews__track");
    var revCards = revTrack ? revTrack.children : [];
    var revIdx = 0;
    function revVisible() {
      if (window.matchMedia("(min-width: 992px)").matches) return revCards.length;
      return 1;
    }
    function revGo(n) {
      if (!revCards.length) return;
      var max = Math.max(0, revCards.length - revVisible());
      if (n < 0) revIdx = max;
      else if (n > max) revIdx = 0;
      else revIdx = n;
      if (revVisible() >= revCards.length) {
        revTrack.style.transform = "none";
        return;
      }
      var card = revCards[0];
      var gap = parseFloat(getComputedStyle(revTrack).gap) || 16;
      var step = card.getBoundingClientRect().width + gap;
      revTrack.style.transform = "translateX(" + (-revIdx * step) + "px)";
    }
    document.querySelectorAll("[data-rev-prev]").forEach(function (b) {
      b.addEventListener("click", function () { revGo(revIdx - 1); });
    });
    document.querySelectorAll("[data-rev-next]").forEach(function (b) {
      b.addEventListener("click", function () { revGo(revIdx + 1); });
    });
    window.addEventListener("resize", function () { revGo(revIdx); }, { passive: true });
    revGo(0);
  }

  /* Hero slider */
  var slides = document.querySelectorAll("[data-hero-slide]");
  var idx = 0;
  var timer;
  function showSlide(n) {
    if (!slides.length) return;
    idx = (n + slides.length) % slides.length;
    slides.forEach(function (s, i) { s.classList.toggle("is-on", i === idx); });
  }
  function play() {
    if (reduce || slides.length < 2) return;
    clearInterval(timer);
    timer = setInterval(function () { showSlide(idx + 1); }, 5000);
  }
  document.querySelectorAll("[data-hero-prev]").forEach(function (b) {
    b.addEventListener("click", function () { showSlide(idx - 1); play(); });
  });
  document.querySelectorAll("[data-hero-next]").forEach(function (b) {
    b.addEventListener("click", function () { showSlide(idx + 1); play(); });
  });
  play();

  /* About tabs */
  document.querySelectorAll("[data-tabs]").forEach(function (wrap) {
    var tabs = wrap.querySelectorAll("[role='tab']");
    var panels = wrap.querySelectorAll("[role='tabpanel']");
    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        var id = tab.getAttribute("aria-controls");
        tabs.forEach(function (t) { t.setAttribute("aria-selected", t === tab ? "true" : "false"); });
        panels.forEach(function (p) { p.classList.toggle("is-on", p.id === id); });
      });
    });
  });

  /* FAQ */
  document.querySelectorAll("[data-faq] .faq__q").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var item = btn.closest(".faq__item");
      var open = item.classList.contains("is-open");
      item.parentElement.querySelectorAll(".faq__item").forEach(function (el) {
        el.classList.remove("is-open");
        var q = el.querySelector(".faq__q");
        if (q) q.setAttribute("aria-expanded", "false");
      });
      if (!open) {
        item.classList.add("is-open");
        btn.setAttribute("aria-expanded", "true");
      }
    });
  });

  /* Reveal */
  if (!reduce && "IntersectionObserver" in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-in");
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
    document.querySelectorAll(".reveal").forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll(".reveal").forEach(function (el) { el.classList.add("is-in"); });
  }

  /* Counters */
  function animateCount(el) {
    var to = parseInt(el.getAttribute("data-count"), 10);
    if (!to && to !== 0) return;
    if (reduce) { el.textContent = String(to); return; }
    var t0 = null, dur = 1400;
    function tick(ts) {
      if (!t0) t0 = ts;
      var p = Math.min(1, (ts - t0) / dur);
      el.textContent = String(Math.round(to * (1 - Math.pow(1 - p, 3))));
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  if ("IntersectionObserver" in window) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          if (!reduce) entry.target.textContent = "0";
          animateCount(entry.target);
          cio.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    document.querySelectorAll("[data-count]").forEach(function (el) { cio.observe(el); });
  }

  /* Images */
  document.querySelectorAll("img").forEach(function (img) {
    img.addEventListener("error", function () {
      img.classList.add("is-broken");
      img.alt = img.alt || "Image unavailable";
    });
  });

  /* Lightbox */
  var box = document.querySelector("[data-lightbox]");
  var boxImg = box ? box.querySelector("img") : null;
  var boxCap = box ? box.querySelector("[data-lightbox-cap]") : null;
  var lbItems = Array.prototype.slice.call(document.querySelectorAll("[data-lightbox-src]"));
  var lbIndex = 0;
  function visibleLbItems() {
    return lbItems.filter(function (a) { return !a.hidden; });
  }
  function showLightbox(i) {
    var items = visibleLbItems();
    if (!box || !boxImg || !items.length) return;
    lbIndex = (i + items.length) % items.length;
    var a = items[lbIndex];
    var cap = a.getAttribute("data-alt") || a.getAttribute("alt") || "";
    boxImg.src = a.getAttribute("data-lightbox-src");
    boxImg.alt = cap;
    if (boxCap) boxCap.textContent = cap;
    box.classList.add("is-on");
    box.setAttribute("aria-hidden", "false");
    document.body.classList.add("is-locked");
    var closeBtn = box.querySelector("[data-lightbox-close]");
    if (closeBtn) closeBtn.focus();
  }
  function closeLightbox() {
    if (!box) return;
    box.classList.remove("is-on");
    box.setAttribute("aria-hidden", "true");
    document.body.classList.remove("is-locked");
    if (boxImg) boxImg.removeAttribute("src");
    if (boxCap) boxCap.textContent = "";
  }
  lbItems.forEach(function (a) {
    a.addEventListener("click", function (e) {
      e.preventDefault();
      showLightbox(visibleLbItems().indexOf(a));
    });
  });
  if (box) {
    box.addEventListener("click", function (e) {
      if (e.target === box || e.target.closest("[data-lightbox-close]")) closeLightbox();
    });
    var prevBtn = box.querySelector("[data-lightbox-prev]");
    var nextBtn = box.querySelector("[data-lightbox-next]");
    if (prevBtn) prevBtn.addEventListener("click", function (e) { e.stopPropagation(); showLightbox(lbIndex - 1); });
    if (nextBtn) nextBtn.addEventListener("click", function (e) { e.stopPropagation(); showLightbox(lbIndex + 1); });
    document.addEventListener("keydown", function (e) {
      if (!box.classList.contains("is-on")) return;
      if (e.key === "Escape") closeLightbox();
      if (e.key === "ArrowLeft") showLightbox(lbIndex - 1);
      if (e.key === "ArrowRight") showLightbox(lbIndex + 1);
    });
  }

  /* Gallery filters */
  document.querySelectorAll("[data-gallery-filter]").forEach(function (wrap) {
    var grid = document.querySelector(".gallery-grid");
    if (!grid) return;
    wrap.querySelectorAll("[data-filter]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        wrap.querySelectorAll("[data-filter]").forEach(function (b) {
          var on = b === btn;
          b.classList.toggle("is-on", on);
          b.setAttribute("aria-pressed", on ? "true" : "false");
        });
        var f = btn.getAttribute("data-filter");
        grid.querySelectorAll("a").forEach(function (a) {
          a.hidden = !(f === "all" || a.getAttribute("data-cat") === f);
        });
      });
    });
  });

  /* Form */
  var form = document.querySelector("[data-enquiry-form]");
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var ok = true;
      form.querySelectorAll("[required]").forEach(function (field) {
        var wrap = field.closest(".field");
        var valid = field.value && String(field.value).trim() !== "";
        if (field.type === "email" && valid) valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value);
        if (field.type === "tel" && valid) valid = field.value.replace(/\D/g, "").length >= 10;
        wrap && wrap.classList.toggle("is-error", !valid);
        if (!valid) ok = false;
      });
      if (!ok) {
        var first = form.querySelector(".field.is-error input, .field.is-error select");
        if (first) first.focus();
        return;
      }
      form.classList.add("is-sent");
      var success = document.querySelector("[data-form-success]");
      if (success) { success.classList.add("is-on"); success.focus(); }
    });
  }

  /* Sticky header + back to top */
  var header = document.querySelector(".site-chrome") || document.querySelector(".site-header");
  var back = document.querySelector("[data-back-top]");
  var backBar = document.querySelector("[data-back-progress]");
  var backRing = 2 * Math.PI * 15.5;
  if (backBar) {
    backBar.style.strokeDasharray = String(backRing);
    backBar.style.strokeDashoffset = String(backRing);
  }
  var backTicking = false;
  var parallaxLayers = document.querySelectorAll("[data-parallax]");
  function updateParallax() {
    if (reduce || !parallaxLayers.length) return;
    var vh = window.innerHeight || 1;
    parallaxLayers.forEach(function (bg) {
      var sec = bg.parentElement;
      if (!sec) return;
      var rect = sec.getBoundingClientRect();
      if (rect.bottom < -80 || rect.top > vh + 80) return;
      var progress = (vh - rect.top) / (vh + rect.height);
      var range = Math.min(150, Math.max(90, rect.height * 0.32));
      var shift = (progress - 0.5) * 2 * range;
      bg.style.transform = "translate3d(0," + shift.toFixed(1) + "px,0)";
    });
  }
  function updateBack() {
    var y = window.scrollY || 0;
    var max = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    var p = Math.min(1, Math.max(0, y / max));
    if (header) header.classList.toggle("is-scrolled", y > 8);
    if (back) back.classList.toggle("is-on", y > 420);
    if (backBar) backBar.style.strokeDashoffset = String(backRing * (1 - p));
    updateParallax();
    backTicking = false;
  }
  var onScroll = function () {
    if (backTicking) return;
    backTicking = true;
    requestAnimationFrame(updateBack);
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onScroll, { passive: true });
  updateBack();
  if (back) {
    back.addEventListener("click", function () {
      var start = window.scrollY || 0;
      if (reduce || start < 2) {
        window.scrollTo({ top: 0, left: 0, behavior: "auto" });
        return;
      }
      var t0 = null;
      var dur = Math.min(800, Math.max(380, start * 0.08));
      var html = document.documentElement;
      var prev = html.style.scrollBehavior;
      html.style.scrollBehavior = "auto";
      function tick(ts) {
        if (!t0) t0 = ts;
        var p = Math.min(1, (ts - t0) / dur);
        var e = 1 - Math.pow(1 - p, 3);
        window.scrollTo({ top: Math.round(start * (1 - e)), left: 0, behavior: "auto" });
        if (p < 1) requestAnimationFrame(tick);
        else html.style.scrollBehavior = prev;
      }
      requestAnimationFrame(tick);
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") { setNav(false); closeLightbox(); }
  });

  var heroSlides = document.querySelector("[data-hero-slides]");
  if (heroSlides) {
    var heroSrcs = (heroSlides.getAttribute("data-srcs") || "").split("|").map(function (s) { return s.trim(); }).filter(Boolean);
    window.setTimeout(function () {
      heroSrcs.slice(1).forEach(function (src) {
        var img = document.createElement("img");
        img.className = "hero__photo";
        img.alt = "";
        img.width = 1600;
        img.height = 900;
        img.decoding = "async";
        img.src = src;
        heroSlides.appendChild(img);
      });
      var layers = heroSlides.querySelectorAll(".hero__photo");
      if (layers.length < 2) return;
      var hi = 0;
      window.setInterval(function () {
        layers[hi].classList.remove("is-on");
        hi = (hi + 1) % layers.length;
        layers[hi].classList.add("is-on");
      }, 5000);
    }, 400);
  }

  document.querySelectorAll("[data-yt]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = btn.getAttribute("data-yt") || "";
      if (!id || !/^[A-Za-z0-9_-]{11}$/.test(id)) return;
      var iframe = document.createElement("iframe");
      iframe.src = "https://www.youtube.com/embed/" + id + "?autoplay=1&rel=0";
      iframe.title = "Video testimonial";
      iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
      iframe.allowFullscreen = true;
      btn.replaceWith(iframe);
      iframe.className = "vt-card__frame";
      var media = iframe.parentElement;
      if (media) {
        iframe.style.position = "absolute";
        iframe.style.inset = "0";
        iframe.style.width = "100%";
        iframe.style.height = "100%";
        iframe.style.border = "0";
      }
    });
  });
})();

/* Career explorer tabs (World of MPC) */
(function () {
  document.querySelectorAll("[data-career-explorer]").forEach(function (ex) {
    ex.querySelectorAll("[data-ce-cat]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var ci = btn.getAttribute("data-ce-cat");
        ex.querySelectorAll("[data-ce-cat]").forEach(function (b) {
          var on = b === btn;
          b.classList.toggle("is-active", on);
          b.setAttribute("aria-selected", on ? "true" : "false");
        });
        ex.querySelectorAll("[data-ce-panel]").forEach(function (p) {
          p.classList.toggle("is-active", p.getAttribute("data-ce-panel") === ci);
        });
      });
    });
    ex.querySelectorAll("[data-ce-career]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var key = btn.getAttribute("data-ce-career");
        var ci = key.split("-")[0];
        var panel = ex.querySelector('[data-ce-panel="' + ci + '"]');
        if (!panel) return;
        panel.querySelectorAll("[data-ce-career]").forEach(function (b) {
          var on = b === btn;
          b.classList.toggle("is-active", on);
          b.setAttribute("aria-selected", on ? "true" : "false");
        });
        panel.querySelectorAll("[data-ce-detail]").forEach(function (d) {
          d.classList.toggle("is-active", d.getAttribute("data-ce-detail") === key);
        });
      });
    });
  });
})();

/* Faculty department tabs */
(function () {
  document.querySelectorAll("[data-faculty-tabs]").forEach(function (wrap) {
    wrap.querySelectorAll("[data-fac-tab]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var i = btn.getAttribute("data-fac-tab");
        wrap.querySelectorAll("[data-fac-tab]").forEach(function (b) {
          var on = b === btn;
          b.classList.toggle("is-active", on);
          b.setAttribute("aria-selected", on ? "true" : "false");
        });
        wrap.querySelectorAll("[data-fac-panel]").forEach(function (p) {
          p.classList.toggle("is-active", p.getAttribute("data-fac-panel") === i);
        });
      });
    });
  });
})();

/* Student journey — reveal steps one after another */
(function () {
  var track = document.querySelector("[data-journey]");
  if (!track) return;
  var steps = [].slice.call(track.querySelectorAll(".jstep"));
  if (!steps.length) return;
  var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (reduce || !("IntersectionObserver" in window)) {
    steps.forEach(function (s) { s.classList.add("is-in"); });
    return;
  }
  var played = false;
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting || played) return;
      played = true;
      io.disconnect();
      var canScroll = track.scrollWidth > track.clientWidth + 8;
      steps.forEach(function (s, i) {
        setTimeout(function () {
          s.classList.add("is-in");
          if (canScroll) {
            var rel = s.getBoundingClientRect().left - track.getBoundingClientRect().left;
            var target = track.scrollLeft + rel - 40;
            if (target > track.scrollLeft + 40) track.scrollTo({ left: target, behavior: "smooth" });
          }
        }, i * 260);
      });
      // return the strip to the start after the sequence finishes
      if (canScroll) {
        setTimeout(function () { track.scrollTo({ left: 0, behavior: "smooth" }); }, steps.length * 260 + 900);
      }
    });
  }, { threshold: 0.3 });
  io.observe(track);
})();
