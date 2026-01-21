
const session = window.PHP_SESSION_USER || null;

const ngoNameEl = document.getElementById("ngo-name");
const ngoStatusEl = document.getElementById("ngo-status");
const projectsList = document.getElementById("ngo-projects-list");
const donationsBody = document.getElementById("ngo-donations-body");

const ngoName = (session && session.role === "ngo") ? session.name : null;

if (ngoName) {
  ngoNameEl.textContent = ngoName;
} else {
  ngoNameEl.textContent = "NGO (not logged in)";
  if (projectsList) projectsList.innerHTML = "<li>Please login as an NGO account to see your projects.</li>";
  if (donationsBody) donationsBody.innerHTML = "<tr><td colspan='5'>Please login as an NGO account to see incoming donations.</td></tr>";
}

if (!ngoName) {
  if (ngoStatusEl) ngoStatusEl.textContent = "Unverified";
} else {
  loadNgoDashboard();
}

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

  const allDonations = JSON.parse(localStorage.getItem("donorHistory") || "[]");
  donationsBody.innerHTML = "";

  const STATUS_FLOW = ["processing", "received", "implementing", "completed", "reported"];

  const incomingForThisNgo = allDonations
    .map((d, idx) => ({ ...d, _idx: idx }))
    .filter((d) => {
      const owner = projectOwnerByTitle[d.project];
      if (!owner || !ngoName) return false;
      return owner.toLowerCase().startsWith(ngoName.toLowerCase());
    });

  if (incomingForThisNgo.length === 0) {
    donationsBody.innerHTML = "<tr><td colspan='5'>No donations for this NGO yet (demo).</td></tr>";
    return;
  }

  incomingForThisNgo.forEach((d) => {
    const tr = document.createElement("tr");

    const tdProject = document.createElement("td");
    const tdDate = document.createElement("td");
    const tdAmount = document.createElement("td");
    const tdStatus = document.createElement("td");
    const tdAction = document.createElement("td");

    tdProject.textContent = d.project || "";
    tdDate.textContent = d.date || "";
    tdAmount.textContent = "৳" + Number(d.amount).toLocaleString();

    const statusSpan = document.createElement("span");
    const statusValue = (d.status || "processing").toLowerCase();
    statusSpan.classList.add("status-badge");

    switch (statusValue) {
      case "received":
        statusSpan.classList.add("status-received");
        statusSpan.textContent = "Received";
        break;
      case "implementing":
        statusSpan.classList.add("status-implementing");
        statusSpan.textContent = "In Implementation";
        break;
      case "completed":
        statusSpan.classList.add("status-completed");
        statusSpan.textContent = "Completed";
        break;
      case "reported":
        statusSpan.classList.add("status-reported");
        statusSpan.textContent = "Reported";
        break;
      case "flagged":
        statusSpan.classList.add("status-flagged");
        statusSpan.textContent = "Flagged / On Hold";
        break;
      default:
        statusSpan.classList.add("status-processing");
        statusSpan.textContent = "Processing";
    }

    tdStatus.appendChild(statusSpan);

    const actionBtn = document.createElement("button");
    actionBtn.textContent = "Advance";
    actionBtn.className = "status-action-btn";
    actionBtn.onclick = () => {
      const all = JSON.parse(localStorage.getItem("donorHistory") || "[]");
      const rec = all[d._idx];
      if (!rec) return;

      const current = (rec.status || "processing").toLowerCase();
      const pos = STATUS_FLOW.indexOf(current);
      const nextStatus = STATUS_FLOW[Math.min(STATUS_FLOW.length - 1, pos + 1)];

      rec.status = nextStatus;
      all[d._idx] = rec;
      localStorage.setItem("donorHistory", JSON.stringify(all));

      loadNgoDashboard();
    };

    tdAction.appendChild(actionBtn);

    tr.appendChild(tdProject);
    tr.appendChild(tdDate);
    tr.appendChild(tdAmount);
    tr.appendChild(tdStatus);
    tr.appendChild(tdAction);

    donationsBody.appendChild(tr);
  });
}

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) logoutBtn.onclick = () => (window.location.href = "logout.php");


const submitCard = document.getElementById("projectSubmissionCard");
if (submitCard && (!session || session.status !== "approved")) {
  submitCard.style.display = "none";
}


const form = document.getElementById("projectSubmissionForm");
if (form) {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const title = document.getElementById("projTitle").value.trim();
    const description = document.getElementById("projDesc").value.trim();
    const goal = Number(document.getElementById("projGoal").value);

    if (!title || !description || !goal || goal <= 0) {
      alert("Please fill all fields correctly.");
      return;
    }

    const payload = {
      csrf: window.PHP_CSRF,
      title: title,
      description: description, 
      goal: goal
    };

    const res = await fetch("project_submit.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!data.success) {
      alert(data.error || "Project submit failed.");
      return;
    }

    document.getElementById("projSubmitMsg").style.display = "block";
    form.reset();
  });
}




