(function () {
  const user = window.DONOR_USER || { name: "Donor", email: "", role: "donor" };

  const $ = (id) => document.getElementById(id);

  const donorName = $("donorName");
  const donorEmail = $("donorEmail");
  const logoutBtn = $("logoutBtn");
  const themeToggle = $("themeToggle");

  const statTotal = $("statTotal");
  const statCount = $("statCount");
  const statProjects = $("statProjects");
  const statLast = $("statLast");

  const historyBody = $("historyBody");
  const historySearch = $("historySearch");
  const historyProject = $("historyProject");
  const historyStatus = $("historyStatus");
  const exportCsv = $("exportCsv");

  const projectsGrid = $("projectsGrid");
  const projectSearch = $("projectSearch");
  const projectSuggestions = $("projectSuggestions");
  const projectSort = $("projectSort");
  const favOnly = $("favOnly");

  
  const HISTORY_KEY = "donorHistory";
  const APPROVED_KEY = "approvedProjects";
  const THEME_KEY = "donorTheme";
  const FAV_KEY = `donorFavorites:${(user.email || "anon").toLowerCase()}`;


  function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem(THEME_KEY, theme);
    themeToggle.textContent = theme === "dark" ? "Light" : "Dark";
  }
  const savedTheme = localStorage.getItem(THEME_KEY) || "light";
  applyTheme(savedTheme);

  themeToggle?.addEventListener("click", () => {
    const t = document.documentElement.dataset.theme === "dark" ? "light" : "dark";
    applyTheme(t);
  });

  
  logoutBtn?.addEventListener("click", async () => {
    
    try {
      await fetch("logout.php", { method: "POST" });
    } catch (e) {}
    
    window.location.href = "index.html";
  });

  
  const fmtBDT = (n) => {
    const num = Number(n || 0);
    try {
      return "৳" + num.toLocaleString("en-US");
    } catch {
      return "৳" + num;
    }
  };

  const norm = (s) => String(s || "").toLowerCase().trim();

  function safeJSON(key, fallback) {
    try {
      const raw = localStorage.getItem(key);
      if (!raw) return fallback;
      return JSON.parse(raw);
    } catch {
      return fallback;
    }
  }

  function uniq(arr) {
    return Array.from(new Set(arr));
  }

  
  async function loadVerifiedProjectsFromXML() {
    try {
      const res = await fetch("projects.xml", { cache: "no-store" });
      if (!res.ok) return [];
      const xmlText = await res.text();
      const doc = new DOMParser().parseFromString(xmlText, "application/xml");
      const nodes = Array.from(doc.querySelectorAll("project"));
      const projects = nodes
        .map((p) => {
          const get = (tag) => p.querySelector(tag)?.textContent?.trim() || "";
          return {
            id: p.getAttribute("id") || get("id") || get("title"),
            title: get("title"),
            ngo: get("ngo"),
            verified: norm(get("verified")) === "true",
            goal: Number(get("goal") || 0),
            raised: Number(get("raised") || 0),
            image: get("image"),
            short: get("short"),
            status: "approved",
          };
        })
        .filter((p) => p.verified && p.title);
      return projects;
    } catch {
      return [];
    }
  }

  async function loadProjects() {
    const approved = safeJSON(APPROVED_KEY, []);
    if (Array.isArray(approved) && approved.length > 0) {
      
      return approved.map((p, i) => ({
        id: p.id || p.projectId || p.title || `ap_${i}`,
        title: p.title || p.projectTitle || "Untitled Project",
        ngo: p.ngo || p.ngoName || "",
        goal: Number(p.goal || p.target || 0),
        raised: Number(p.raised || p.collected || 0),
        image: p.image || p.img || "",
        short: p.short || p.description || "",
        status: p.status || "approved",
      }));
    }
    
    return await loadVerifiedProjectsFromXML();
  }

  function loadHistory() {
    const all = safeJSON(HISTORY_KEY, []);
    if (!Array.isArray(all)) return [];
    const email = norm(user.email);
    return all.filter((h) => {
      const e = norm(h.email || h.donorEmail || h.userEmail);
      return email && e === email;
    });
  }

  function loadFavorites() {
    const favs = safeJSON(FAV_KEY, []);
    return Array.isArray(favs) ? favs : [];
  }
  function saveFavorites(ids) {
    localStorage.setItem(FAV_KEY, JSON.stringify(ids));
  }

  function renderStats(history) {
    const amounts = history.map((h) => Number(h.amount || h.donationAmount || h.amt || 0));
    const total = amounts.reduce((a, b) => a + b, 0);
    const count = history.length;

    const projectNames = history.map((h) => h.project || h.projectTitle || h.title || "").filter(Boolean);
    const projectsSupported = uniq(projectNames).length;

    const last = history
      .map((h) => new Date(h.date || h.donationDate || h.time || ""))
      .filter((d) => !isNaN(d.getTime()))
      .sort((a, b) => b.getTime() - a.getTime())[0];

    statTotal.textContent = fmtBDT(total);
    statCount.textContent = String(count);
    statProjects.textContent = String(projectsSupported);
    statLast.textContent = last ? last.toLocaleDateString() : "—";
  }

  function fillHistoryProjectFilter(history) {
    const names = uniq(history.map((h) => h.project || h.projectTitle || h.title || "").filter(Boolean)).sort();
    historyProject.innerHTML = `<option value="">All projects</option>` + names.map((n) => `<option value="${escapeHtml(n)}">${escapeHtml(n)}</option>`).join("");
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function getFilteredHistory(history) {
    const q = norm(historySearch.value);
    const proj = historyProject.value;
    const status = norm(historyStatus.value);

    return history.filter((h) => {
      const title = norm(h.project || h.projectTitle || h.title);
      const st = norm(h.status || h.state || "success");
      const matchQ = !q || title.includes(q);
      const matchProj = !proj || (h.project || h.projectTitle || h.title) === proj;
      const matchStatus = !status || st === status;
      return matchQ && matchProj && matchStatus;
    });
  }

  function renderHistory(history) {
    const rows = getFilteredHistory(history);

    if (!rows.length) {
      historyBody.innerHTML = `<tr><td colspan="4" class="muted">No matching donations.</td></tr>`;
      return;
    }

    historyBody.innerHTML = rows
      .sort((a, b) => new Date(b.date || "").getTime() - new Date(a.date || "").getTime())
      .map((h) => {
        const title = h.project || h.projectTitle || h.title || "—";
        const date = h.date || h.donationDate || h.time || "—";
        const amount = Number(h.amount || h.donationAmount || h.amt || 0);
        const status = (h.status || h.state || "success").toLowerCase();
        return `
          <tr>
            <td>${escapeHtml(title)}</td>
            <td>${escapeHtml(date)}</td>
            <td>${fmtBDT(amount)}</td>
            <td>${escapeHtml(status)}</td>
          </tr>`;
      })
      .join("");
  }

  function downloadCSV(rows) {
    const header = ["Project", "Date", "Amount", "Status"];
    const lines = [header.join(",")];

    rows.forEach((h) => {
      const title = (h.project || h.projectTitle || h.title || "").replaceAll('"', '""');
      const date = (h.date || h.donationDate || h.time || "").replaceAll('"', '""');
      const amount = String(Number(h.amount || h.donationAmount || h.amt || 0));
      const status = String((h.status || h.state || "success"));
      lines.push(`"${title}","${date}","${amount}","${status}"`);
    });

    const blob = new Blob([lines.join("\n")], { type: "text/csv;charset=utf-8;" });
    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "donation_history.csv";
    document.body.appendChild(a);
    a.click();
    a.remove();
  }

  
  function updateSuggestions(projects) {
    const titles = uniq(projects.map((p) => p.title).filter(Boolean)).sort((a, b) => a.localeCompare(b));
    projectSuggestions.innerHTML = titles.map((t) => `<option value="${escapeHtml(t)}"></option>`).join("");
  }

  function sortProjects(list, mode) {
    const arr = [...list];
    switch (mode) {
      case "mostRaised":
        return arr.sort((a, b) => (b.raised || 0) - (a.raised || 0));
      case "highestProgress":
        return arr.sort((a, b) => (progress(b) - progress(a)));
      case "lowestGoal":
        return arr.sort((a, b) => (a.goal || 0) - (b.goal || 0));
      case "az":
        return arr.sort((a, b) => String(a.title).localeCompare(String(b.title)));
      case "recommended":
      default:
        return arr.sort((a, b) => (progress(b) - progress(a)) || ((b.raised || 0) - (a.raised || 0)));
    }
  }

  function progress(p) {
    const g = Number(p.goal || 0);
    const r = Number(p.raised || 0);
    if (!g) return 0;
    return Math.max(0, Math.min(1, r / g));
  }

  function getFilteredProjects(projects, favs) {
    const q = norm(projectSearch.value);
    let list = projects.filter((p) => norm(p.title).includes(q) || norm(p.ngo).includes(q));

    if (favOnly.checked) {
      list = list.filter((p) => favs.includes(p.id));
    }

    list = sortProjects(list, projectSort.value);
    return list;
  }

  function badgeFor(p) {
    const pct = progress(p) * 100;
    if (pct >= 75) return { cls: "good", label: `${Math.round(pct)}% funded` };
    if (pct >= 35) return { cls: "warn", label: `${Math.round(pct)}% funded` };
    return { cls: "bad", label: `${Math.round(pct)}% funded` };
  }

  function renderProjects(projects, favs) {
    const list = getFilteredProjects(projects, favs);

    if (!list.length) {
      projectsGrid.innerHTML = `<div class="muted">No projects found.</div>`;
      return;
    }

    projectsGrid.innerHTML = list
      .map((p) => {
        const pct = Math.round(progress(p) * 100);
        const b = badgeFor(p);
        const isFav = favs.includes(p.id);
        const imgStyle = p.image ? `style="background-image:url('${p.image.replaceAll("'", "%27")}')"` : "";
        return `
          <div class="card project-card">
            <div class="project-img" ${imgStyle}></div>
            <div class="project-body">
              <div style="display:flex; align-items:start; justify-content:space-between; gap:10px;">
                <div>
                  <div class="project-title">${escapeHtml(p.title)}</div>
                  <div class="project-meta">NGO: ${escapeHtml(p.ngo || "—")}</div>
                </div>
                <span class="badge ${b.cls}">${escapeHtml(b.label)}</span>
              </div>

              <div class="muted" style="font-size:13px; line-height:1.35; min-height:36px;">
                ${escapeHtml(p.short || "Support this verified project and track impact transparently.")}
              </div>

              <div class="progress-wrap">
                <div class="muted" style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                  <span>Raised: ${fmtBDT(p.raised || 0)}</span>
                  <span>Goal: ${fmtBDT(p.goal || 0)}</span>
                </div>
                <div class="progress-bar"><div style="width:${pct}%"></div></div>
              </div>

              <div class="card-actions">
                <button class="btn fav-btn" data-fav="${escapeHtml(p.id)}" type="button">
                  ${isFav ? "★ Favorited" : "☆ Favorite"}
                </button>
                <a class="btn btn-primary donate-btn" href="donate.html" title="Donate to this project">Donate</a>
              </div>
            </div>
          </div>
        `;
      })
      .join("");
  }

  function wireFavoriteClicks(projects, favs) {
    projectsGrid.addEventListener("click", (e) => {
      const btn = e.target.closest("[data-fav]");
      if (!btn) return;
      const id = btn.getAttribute("data-fav");
      if (!id) return;

      const has = favs.includes(id);
      const next = has ? favs.filter((x) => x !== id) : [...favs, id];
      favs.splice(0, favs.length, ...next);
      saveFavorites(favs);
      renderProjects(projects, favs);
    });
  }

  donorName.textContent = user.name || "Donor";
  donorEmail.textContent = user.email || "—";

  (async function init() {
    const history = loadHistory();
    renderStats(history);
    fillHistoryProjectFilter(history);
    renderHistory(history);

    historySearch.addEventListener("input", () => renderHistory(history));
    historyProject.addEventListener("change", () => renderHistory(history));
    historyStatus.addEventListener("change", () => renderHistory(history));
    exportCsv.addEventListener("click", () => downloadCSV(getFilteredHistory(history)));

    const projects = await loadProjects();
    const favs = loadFavorites();

    updateSuggestions(projects);
    renderProjects(projects, favs);
    wireFavoriteClicks(projects, favs);

    projectSearch.addEventListener("input", () => renderProjects(projects, favs));
    projectSort.addEventListener("change", () => renderProjects(projects, favs));
    favOnly.addEventListener("change", () => renderProjects(projects, favs));
  })();
})();
