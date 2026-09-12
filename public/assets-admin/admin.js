(function () {
  var csrf = document.querySelector('meta[name="csrf-token"]');
  csrf = csrf ? csrf.getAttribute("content") : "";
  var toast = document.getElementById("toast");

  function showToast(msg, ok) {
    if (!toast) return;
    toast.textContent = msg;
    toast.style.borderColor = ok === false ? "rgba(255,107,107,.4)" : "rgba(61,220,151,.35)";
    toast.classList.add("is-on");
    setTimeout(function () { toast.classList.remove("is-on"); }, 2400);
  }

  document.querySelectorAll("[data-confirm]").forEach(function (el) {
    el.addEventListener("submit", function (e) {
      if (!confirm(el.getAttribute("data-confirm") || "Are you sure?")) e.preventDefault();
    });
  });

  document.querySelectorAll("[data-ajax]").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      if (form.getAttribute("data-ajax") === "off") return;
      e.preventDefault();
      var fd = new FormData(form);
      fd.append("ajax", "1");
      var btn = form.querySelector("[type=submit]");
      if (btn) btn.disabled = true;
      fetch(form.action || location.href, {
        method: "POST",
        body: fd,
        headers: { "X-Requested-With": "XMLHttpRequest", "X-CSRF-Token": csrf, "Accept": "application/json" }
      }).then(function (r) { return r.json().catch(function () { return { ok: false, error: "Save failed" }; }); })
        .then(function (j) {
          if (btn) btn.disabled = false;
          if (j.redirect && !j.message) { location.href = j.redirect; return; }
          showToast(j.message || j.error || (j.ok ? "Saved" : "Could not save"), j.ok !== false);
          if (j.redirect && j.ok) setTimeout(function () { location.href = j.redirect; }, 400);
        })
        .catch(function () {
          if (btn) btn.disabled = false;
          showToast("Network error", false);
        });
    });
  });

  document.querySelectorAll("[data-slug-source]").forEach(function (src) {
    var target = document.querySelector(src.getAttribute("data-slug-source"));
    if (!target) return;
    var locked = target.value !== "";
    target.addEventListener("input", function () { locked = true; });
    src.addEventListener("input", function () {
      if (locked) return;
      target.value = src.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
    });
  });

  var slugInput = document.querySelector("[data-slug-check]");
  if (slugInput) {
    var timer;
    slugInput.addEventListener("input", function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        var u = "/admin/api/slug-check/?kind=" + encodeURIComponent(slugInput.getAttribute("data-slug-check"))
          + "&slug=" + encodeURIComponent(slugInput.value)
          + "&id=" + encodeURIComponent(slugInput.getAttribute("data-id") || "")
          + "&type_id=" + encodeURIComponent(slugInput.getAttribute("data-type-id") || "");
        fetch(u, { headers: { "X-Requested-With": "XMLHttpRequest" } })
          .then(function (r) { return r.json(); })
          .then(function (j) {
            slugInput.style.borderColor = j.ok ? "" : "var(--err)";
            var hint = slugInput.parentElement.querySelector(".slug-hint");
            if (!hint) {
              hint = document.createElement("small");
              hint.className = "slug-hint";
              hint.style.color = "var(--err)";
              slugInput.parentElement.appendChild(hint);
            }
            hint.textContent = j.ok ? "" : (j.message || "Unavailable");
          });
      }, 280);
    });
  }

  function bindSortable(list) {
    if (!list) return;
    var drag;
    var items = function () { return list.querySelectorAll(list.getAttribute("data-sort-item") || ".pe-item, .sec"); };
    items().forEach(function (sec) {
      sec.addEventListener("dragstart", function (e) {
        if (e.target.closest("button, input, textarea, select, a")) return;
        drag = sec; sec.classList.add("is-drag");
      });
      sec.addEventListener("dragend", function () {
        sec.classList.remove("is-drag");
        var url = list.getAttribute("data-reorder") || (list.closest("[data-reorder]") && list.closest("[data-reorder]").getAttribute("data-reorder"));
        if (!url) return;
        var order = [].map.call(list.querySelectorAll("[name='section_id[]']"), function (i) { return i.value; });
        var fd = new FormData();
        fd.append("_csrf", csrf);
        fd.append("ajax", "1");
        order.forEach(function (id) { fd.append("order[]", id); });
        fetch(url, { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest", "X-CSRF-Token": csrf, "Accept": "application/json" } })
          .then(function () {
            [].forEach.call(list.querySelectorAll(".pe-num"), function (n, i) { n.textContent = String(i + 1); });
            showToast("Section order saved", true);
          });
      });
      sec.addEventListener("dragover", function (e) {
        e.preventDefault();
        if (!drag || drag === sec) return;
        var rect = sec.getBoundingClientRect();
        var before = (e.clientY - rect.top) < rect.height / 2;
        list.insertBefore(drag, before ? sec : sec.nextSibling);
      });
    });
  }
  bindSortable(document.querySelector("[data-sortable]"));
  bindSortable(document.querySelector("[data-sec-list]"));

  document.querySelectorAll("[data-add-field]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var wrap = document.querySelector(btn.getAttribute("data-add-field"));
      if (!wrap) return;
      var proto = wrap.getAttribute("data-proto");
      if (!proto) return;
      wrap.insertAdjacentHTML("beforeend", proto);
    });
  });

  var picker = document.getElementById("media-picker");
  var pickerRows = [];
  function renderPicker(q) {
    var grid = picker && picker.querySelector(".media-grid");
    if (!grid) return;
    q = (q || "").toLowerCase();
    var rows = pickerRows.filter(function (m) {
      if (!q) return true;
      return (m.original_name || "").toLowerCase().indexOf(q) !== -1 || (m.alt_text || "").toLowerCase().indexOf(q) !== -1;
    });
    grid.innerHTML = rows.map(function (m) {
      var dim = (m.width && m.height) ? (m.width + "×" + m.height) : "";
      var kb = m.size_bytes ? (Math.round(m.size_bytes / 1024) + " KB") : "";
      var src = (m.mime || "").indexOf("image/") === 0 ? m.public_path : "";
      return '<figure data-pick="' + m.public_path + '">' +
        (src ? '<img src="' + src + '" alt="">' : '<div class="empty">File</div>') +
        '<figcaption>' + (m.original_name || "") + '<br>' + [dim, kb].filter(Boolean).join(" · ") + "</figcaption></figure>";
    }).join("") || '<p class="empty">No matching files.</p>';
  }
  function applyPicked(path) {
    var sel = picker.dataset.target;
    var input = sel ? document.querySelector(sel) : null;
    if (!input) return;
    input.value = path;
    input.dispatchEvent(new Event("input", { bubbles: true }));
    var wrap = input.closest("[data-img-field]");
    if (wrap) {
      var img = wrap.querySelector("img");
      var empty = wrap.querySelector(".img-field__empty");
      var pathEl = wrap.querySelector(".img-field__path");
      var clear = wrap.querySelector("[data-img-clear]");
      if (img) { img.src = path; img.hidden = !path; }
      if (empty) empty.hidden = !!path;
      if (pathEl) pathEl.textContent = path ? path.split("/").pop() : "None selected";
      if (clear) clear.hidden = !path;
    }
  }
  document.body.addEventListener("click", function (e) {
    var t = e.target.closest("[data-media-open]");
    if (!t || !picker) return;
    e.preventDefault();
    picker.dataset.target = t.getAttribute("data-media-open");
    picker.classList.add("is-on");
    fetch("/admin/media/?ajax=1", { headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" } })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        pickerRows = j.rows || [];
        var q = picker.querySelector("[data-media-search]");
        renderPicker(q ? q.value : "");
      });
  });
  if (picker) {
    var qin = picker.querySelector("[data-media-search]");
    if (qin) qin.addEventListener("input", function () { renderPicker(qin.value); });
    picker.addEventListener("click", function (e) {
      if (e.target === picker || e.target.closest("[data-media-close]")) picker.classList.remove("is-on");
      var fig = e.target.closest("[data-pick]");
      if (!fig) return;
      applyPicked(fig.getAttribute("data-pick"));
      picker.classList.remove("is-on");
    });
  }
  document.body.addEventListener("click", function (e) {
    var c = e.target.closest("[data-img-clear]");
    if (!c) return;
    var wrap = c.closest("[data-img-field]");
    if (!wrap) return;
    var input = wrap.querySelector("input[id]");
    if (input && picker) picker.dataset.target = "#" + input.id;
    applyPicked("");
  });

  (function pageEditor() {
    var root = document.querySelector("[data-page-editor]");
    if (!root) return;
    function selectSec(id) {
      root.querySelectorAll(".pe-item").forEach(function (el) {
        el.classList.toggle("is-on", el.getAttribute("data-sec-id") === String(id));
      });
      root.querySelectorAll(".pe-panel").forEach(function (el) {
        var on = el.getAttribute("data-panel") === String(id);
        el.hidden = !on;
        el.classList.toggle("is-on", on);
      });
      var focus = document.getElementById("focus-sec");
      if (focus) focus.value = String(id);
      var url = new URL(location.href);
      url.searchParams.set("sec", String(id));
      history.replaceState(null, "", url);
    }
    root.addEventListener("click", function (e) {
      var sel = e.target.closest("[data-select-sec]");
      if (sel) selectSec(sel.getAttribute("data-select-sec"));
      var vis = e.target.closest("[data-vis-toggle]");
      if (vis) {
        e.preventDefault();
        e.stopPropagation();
        var id = vis.getAttribute("data-vis-toggle");
        var field = root.querySelector("[data-vis-field='" + id + "']");
        var next = field && field.value === "1" ? "0" : "1";
        if (field) field.value = next;
        var item = vis.closest(".pe-item");
        if (item) item.classList.toggle("is-hidden", next === "0");
        vis.textContent = next === "1" ? "◉" : "○";
        vis.setAttribute("aria-pressed", next === "1" ? "true" : "false");
        vis.title = next === "1" ? "Hide section" : "Show section";
        var fd = new FormData();
        fd.append("_csrf", csrf);
        fd.append("section_id", id);
        fd.append("visible", next);
        fetch(root.getAttribute("data-visible"), {
          method: "POST", body: fd,
          headers: { "X-Requested-With": "XMLHttpRequest", "X-CSRF-Token": csrf, "Accept": "application/json" }
        }).then(function (r) { return r.json(); }).then(function (j) {
          showToast(next === "1" ? "Section visible" : "Section hidden", j.ok !== false);
        });
      }
    });
    root.querySelectorAll(".pe-panel").forEach(function (panel) {
      panel.addEventListener("input", function () {
        var id = panel.getAttribute("data-panel");
        var item = root.querySelector(".pe-item[data-sec-id='" + id + "']");
        if (item) {
          var d = item.querySelector(".pe-dirty");
          if (d) d.hidden = false;
        }
        var hint = root.querySelector("[data-dirty-hint]");
        if (hint) hint.hidden = false;
      });
    });
    var prev = root.querySelector("[data-save-preview]");
    if (prev) prev.addEventListener("click", function () {
      document.getElementById("save-after").value = "preview";
    });
    var save = root.querySelector("[data-save]");
    if (save) save.addEventListener("click", function () {
      document.getElementById("save-after").value = "";
    });
    document.querySelectorAll("[data-rev-open]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var art = btn.closest(".pe-rev");
        var open = art.classList.contains("is-open");
        document.querySelectorAll(".pe-rev").forEach(function (a) {
          a.classList.remove("is-open");
          var d = a.querySelector(".pe-rev__detail");
          if (d) d.hidden = true;
        });
        if (!open) {
          art.classList.add("is-open");
          var det = art.querySelector(".pe-rev__detail");
          if (det) det.hidden = false;
          var acc = document.getElementById("revisions-acc");
          if (acc) acc.open = true;
        }
      });
    });
  })();

  var drop = document.querySelector("[data-drop-upload]");
  if (drop) {
    ["dragenter", "dragover"].forEach(function (ev) {
      drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add("is-on"); });
    });
    drop.addEventListener("dragleave", function () { drop.classList.remove("is-on"); });
    drop.addEventListener("drop", function (e) {
      e.preventDefault();
      drop.classList.remove("is-on");
      var input = drop.querySelector("input[type=file]");
      if (!input) return;
      input.files = e.dataTransfer.files;
      drop.closest("form").requestSubmit();
    });
  }
})();
