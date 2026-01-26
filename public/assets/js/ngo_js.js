// ===== NGO Dashboard JS (NO projects.xml + single submit listener + MVC + MySQL) =====

const APP_BASE = (window.APP_BASE && String(window.APP_BASE).trim() !== "")
  ? String(window.APP_BASE).trim()
  : "";

const session = window.PHP_SESSION_USER || null;

const ngoNameEl = document.getElementById("ngo-name");
const ngoStatusEl = document.getElementById("ngo-status");
const projectsList = document.getElementById("ngo-projects-list");
const donationsBody = document.getElementById("ngo-donations-body");

const ngoName = (session && session.role === "ngo") ? session.name : null;

// --------------------
// Header info
// --------------------
if (ngoNameEl) ngoNameEl.textContent = ngoName ? ngoName : "NGO (not logged in)";

if (!ngoName) {
  if (projectsList) projectsList.innerHTML = "<li>Please login as an NGO account to see your projects.</li>";
  if (donationsBody) donationsBody.innerHTML = "<tr><td colspan='5'>Please login as an NGO account to see incoming donations.</td></tr>";
  if (ngoStatusEl) ngoStatusEl.textContent = "Unverified";
} else {
  // since XML is removed, just show approved/unapproved from session
  if (ngoStatusEl) ngoStatusEl.textContent = (session.status === "approved") ? "Approved" : "Pending approval";
}

// --------------------
// Demo donations (optional - uses localStorage)
// --------------------
(function renderDemoDonations() {
async function loadNgoDashboard() {
  
  const projectOwnerByTitle = {};
  let hasVerifiedProject = false;

  try {
    const resp = await fetch("projects.xml");
    if (!resp.ok) throw new Error("Could not load projects.xml");

    const text = await resp.text();
    const xml = new DOMParser().parseFromString(text, "application/xml");
    const projects = xml.querySelectorAll("project");

    if (projectsList) projectsList.innerHTML = "";

    projects.forEach((p) => {
      const title = p.querySelector("title")?.textContent || "";
      const ngo = p.querySelector("ngo")?.textContent || "";
      const verified = p.querySelector("verified")?.textContent === "true";

      if (title) projectOwnerByTitle[title] = ngo;

      if (ngoName && ngo.toLowerCase().startsWith(ngoName.toLowerCase())) {
        if (projectsList) {
          const li = document.createElement("li");
          li.textContent = title + (verified ? " (Verified project)" : "");
          projectsList.appendChild(li);
        }
        if (verified) hasVerifiedProject = true;
      }
    });

    if (projectsList && !projectsList.hasChildNodes()) {
      projectsList.innerHTML = "<li>No projects found for this NGO in projects.xml (demo).</li>";
    }

    if (ngoStatusEl) {
      ngoStatusEl.textContent = hasVerifiedProject ? "Verified (has verified projects)" : "Unverified";
    }
  } catch (err) {
    console.error(err);
    if (projectsList) projectsList.innerHTML = "<li>Could not load projects.xml.</li>";
    if (ngoStatusEl) ngoStatusEl.textContent = "Unverified";
  }

  if (!donationsBody) return;
  if (!ngoName) return;

  const allDonations = JSON.parse(localStorage.getItem("donorHistory") || "[]");
  donationsBody.innerHTML = "";

  if (allDonations.length === 0) {
    donationsBody.innerHTML = "<tr><td colspan='5'>No donations yet (demo).</td></tr>";
    return;
  }

  const STATUS_FLOW = ["processing", "received", "implementing", "completed", "reported"];

  allDonations.forEach((d, idx) => {
    const tr = document.createElement("tr");

    const tdProject = document.createElement("td");
    const tdDate = document.createElement("td");
    const tdAmount = document.createElement("td");
    const tdStatus = document.createElement("td");
    const tdAction = document.createElement("td");

    tdProject.textContent = d.project || "";
    tdDate.textContent = d.date || "";
    tdAmount.textContent = "৳" + Number(d.amount || 0).toLocaleString();

    const statusSpan = document.createElement("span");
    const statusValue = (d.status || "processing").toLowerCase();
    statusSpan.classList.add("status-badge");

    const labelMap = {
      processing: ["status-processing", "Processing"],
      received: ["status-received", "Received"],
      implementing: ["status-implementing", "In Implementation"],
      completed: ["status-completed", "Completed"],
      reported: ["status-reported", "Reported"],
      flagged: ["status-flagged", "Flagged / On Hold"]
    };

    const chosen = labelMap[statusValue] || labelMap.processing;
    statusSpan.classList.add(chosen[0]);
    statusSpan.textContent = chosen[1];

    tdStatus.appendChild(statusSpan);

    const actionBtn = document.createElement("button");
    actionBtn.textContent = "Advance";
    actionBtn.className = "status-action-btn";
    actionBtn.onclick = () => {
      const all = JSON.parse(localStorage.getItem("donorHistory") || "[]");
      const rec = all[idx];
      if (!rec) return;

      const current = (rec.status || "processing").toLowerCase();
      const pos = STATUS_FLOW.indexOf(current);
      const nextStatus = STATUS_FLOW[Math.min(STATUS_FLOW.length - 1, pos + 1)];

      rec.status = nextStatus;
      all[idx] = rec;
      localStorage.setItem("donorHistory", JSON.stringify(all));
      location.reload();
    };

    tdAction.appendChild(actionBtn);

    tr.appendChild(tdProject);
    tr.appendChild(tdDate);
    tr.appendChild(tdAmount);
    tr.appendChild(tdStatus);
    tr.appendChild(tdAction);

    donationsBody.appendChild(tr);
  });
})();

// --------------------
// Logout (MVC route)
// --------------------
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) logoutBtn.onclick = () => (window.location.href = `${APP_BASE}/logout`);

// --------------------
// Hide submission card if NGO not approved
// --------------------

const submitCard = document.getElementById("projectSubmissionCard");
if (submitCard && (!session || session.status !== "approved")) {
  submitCard.style.display = "none";
}

// --------------------
// Submit Project (MVC + MySQL) - SINGLE listener
// --------------------
(function attachSubmit() {
  const form = document.getElementById("projectSubmissionForm");
  const msg = document.getElementById("projSubmitMsg");

  if (!form) return;

  if (form.dataset.bound === "1") return;
  form.dataset.bound = "1";


const form = document.getElementById("projectSubmissionForm");
if (form) {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const title = document.getElementById("projTitle")?.value.trim() || "";
    const description = document.getElementById("projDesc")?.value.trim() || "";
    const goal = Number(document.getElementById("projGoal")?.value || 0);

    if (!title || !description || goal <= 0) {
      alert("Please fill all fields correctly.");
      return;
    }

    if (!window.PHP_CSRF) {
      alert("CSRF missing. Reload the page.");
      return;
    }

    const payload = { csrf: window.PHP_CSRF, title, description, goal };

    try {
      const res = await fetch(`${APP_BASE}/api/ngo/project/submit`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch {
        console.error("Non-JSON response:", text);
        alert("Server returned non-JSON (PHP error). Check XAMPP Apache logs.");
        return;
      }
    const payload = {
      csrf: window.PHP_CSRF,
      title: title,
      description: description, 
      goal: goal
    };

      if (!data.success) {
        alert(data.error || "Project submit failed.");
        return;
      }

      if (msg) msg.style.display = "block";
      form.reset();
      setTimeout(() => { if (msg) msg.style.display = "none"; }, 3000);

    } catch (err) {
      console.error(err);
      alert("Network/server error. Check Console + Network tab.");
    }
  });
})();


    document.getElementById("projSubmitMsg").style.display = "block";
    form.reset();
  });
}




