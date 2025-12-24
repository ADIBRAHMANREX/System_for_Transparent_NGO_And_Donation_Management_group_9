 // 1) Get logged-in NGO from demoSession
  const session = JSON.parse(localStorage.getItem('demoSession') || 'null');
  const ngoNameEl = document.getElementById('ngo-name');
  const ngoStatusEl = document.getElementById('ngo-status');
  const projectsList = document.getElementById('ngo-projects-list');
  const donationsBody = document.getElementById('ngo-donations-body');

  const ngoName = (session && session.role === 'ngo') ? session.name : null;

  if (ngoName) {
    ngoNameEl.textContent = ngoName;
  } else {
    ngoNameEl.textContent = 'NGO (not logged in)';
    projectsList.innerHTML = '<li>Please login as an NGO account to see your projects.</li>';
    donationsBody.innerHTML = '<tr><td colspan="3">Please login as an NGO account to see incoming donations.</td></tr>';
  }

  if (!ngoName) {
    ngoStatusEl.textContent = 'Unverified';
  } else {
    loadNgoDashboard().then(() => {
  renderNGOApprovalSections();
});


  }

  async function loadNgoDashboard() {
    // Map: project title -> NGO name (from projects.xml)
    const projectOwnerByTitle = {};
    let hasVerifiedProject = false;

    try {
      const resp = await fetch('projects.xml');
      if (!resp.ok) throw new Error('Could not load projects.xml');
      const text = await resp.text();
      const xml = new DOMParser().parseFromString(text, 'application/xml');
      const projects = xml.querySelectorAll('project');

      projectsList.innerHTML = '';

      projects.forEach(p => {
        const title = p.querySelector('title')?.textContent || '';
        const ngo = p.querySelector('ngo')?.textContent || '';
        const verified = p.querySelector('verified')?.textContent === 'true';

        if (title) {
          projectOwnerByTitle[title] = ngo;
        }

        // Active projects for this NGO
        if (ngo.toLowerCase().startsWith(ngoName.toLowerCase())) {
          const li = document.createElement('li');
          li.textContent = title + (verified ? ' (Verified project)' : '');
          projectsList.appendChild(li);
          if (verified) hasVerifiedProject = true;
        }
      });

      if (!projectsList.hasChildNodes()) {
        projectsList.innerHTML = '<li>No projects found for this NGO in projects.xml (demo).</li>';
      }

      ngoStatusEl.textContent = hasVerifiedProject
        ? 'Verified (has verified projects)'
        : 'Unverified';

    } catch (err) {
      console.error(err);
      projectsList.innerHTML = '<li>Could not load projects.xml.</li>';
      ngoStatusEl.textContent = 'Unverified';
    }

    // 2) Load incoming donations for this NGO
    // 2) Load incoming donations for this NGO
const allDonations = JSON.parse(localStorage.getItem('donorHistory') || '[]');
donationsBody.innerHTML = '';

const STATUS_FLOW = ['processing', 'received', 'implementing', 'completed', 'reported'];

const incomingForThisNgo = allDonations
  .map((d, idx) => ({ ...d, _idx: idx }))  // keep index in original array
  .filter(d => {
    const owner = projectOwnerByTitle[d.project];
    if (!owner) return false;
    return owner.toLowerCase().startsWith(ngoName.toLowerCase());
  });

if (incomingForThisNgo.length === 0) {
  donationsBody.innerHTML = '<tr><td colspan="5">No donations for this NGO yet (demo).</td></tr>';
} else {
  incomingForThisNgo.forEach(d => {
    const tr = document.createElement('tr');

    const tdProject = document.createElement('td');
    const tdDate = document.createElement('td');
    const tdAmount = document.createElement('td');
    const tdStatus = document.createElement('td');
    const tdAction = document.createElement('td');

    tdProject.textContent = d.project || '';
    tdDate.textContent = d.date || '';
    tdAmount.textContent = '৳' + Number(d.amount).toLocaleString();

    // Status badge
    const statusSpan = document.createElement('span');
    const statusValue = (d.status || 'processing').toLowerCase();
    statusSpan.classList.add('status-badge');

    switch (statusValue) {
      case 'received':
        statusSpan.classList.add('status-received');
        statusSpan.textContent = 'Received';
        break;
      case 'implementing':
        statusSpan.classList.add('status-implementing');
        statusSpan.textContent = 'In Implementation';
        break;
      case 'completed':
        statusSpan.classList.add('status-completed');
        statusSpan.textContent = 'Completed';
        break;
      case 'reported':
        statusSpan.classList.add('status-reported');
        statusSpan.textContent = 'Reported';
        break;
      case 'flagged':
        statusSpan.classList.add('status-flagged');
        statusSpan.textContent = 'Flagged / On Hold';
        break;
      default:
        statusSpan.classList.add('status-processing');
        statusSpan.textContent = 'Processing';
    }

    tdStatus.appendChild(statusSpan);

    // Action button: advance to next status in STATUS_FLOW
    const actionBtn = document.createElement('button');
    actionBtn.textContent = 'Advance';
    actionBtn.className = 'status-action-btn';
    actionBtn.onclick = () => {
      const all = JSON.parse(localStorage.getItem('donorHistory') || '[]');
      const rec = all[d._idx];
      if (!rec) return;

      const current = (rec.status || 'processing').toLowerCase();
      const pos = STATUS_FLOW.indexOf(current);
      const nextStatus = STATUS_FLOW[Math.min(STATUS_FLOW.length - 1, pos + 1)];

      rec.status = nextStatus;
      all[d._idx] = rec;
      localStorage.setItem('donorHistory', JSON.stringify(all));

      // Re-render dashboard after update
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

  }
  document.getElementById("logoutBtn").onclick = () => {
    localStorage.removeItem("demoSession");
    window.location.href = "login.html";
};



// Show or hide project submission based on approval status
const currentSession = JSON.parse(localStorage.getItem('demoSession') || '{}');
if (currentSession.status !== 'approved') {
  document.getElementById('projectSubmissionCard').style.display = 'none';
}

// Handle project submission
document.getElementById('projectSubmissionForm')?.addEventListener('submit', (e) => {
  e.preventDefault();

  const title = document.getElementById('projTitle').value.trim();
  const desc  = document.getElementById('projDesc').value.trim();
  const goal  = Number(document.getElementById('projGoal').value);

  let requests = JSON.parse(localStorage.getItem('projectRequests') || '[]');

  requests.push({
  ngoId: currentSession.email,      // ✅ consistent unique ID (email)
  ngoName: currentSession.name,     // (for display)
  title: title,
  desc: desc,
  goal: goal,
  raised: 0,                        // ✅ helpful for ongoing/approved view
  status: "pending",
  date: new Date().toISOString().slice(0, 10)
});


  localStorage.setItem('projectRequests', JSON.stringify(requests));

  document.getElementById('projSubmitMsg').style.display = 'block';
  e.target.reset();
});



function renderNGOApprovalSections() {
  const currentSession = JSON.parse(localStorage.getItem("demoSession") || "{}");
  const ngoId = currentSession.email;
  if (!ngoId) return;

  const requests = JSON.parse(localStorage.getItem("projectRequests") || "[]");
  const approved = JSON.parse(localStorage.getItem("approvedProjects") || "[]");

  const myPending = requests.filter(p => p.ngoId === ngoId);
  const myApproved = approved.filter(p => p.ngoId === ngoId);

  const incomingBox = document.getElementById("ngoIncomingList");
  const approvedBox = document.getElementById("ngoApprovedList");
  const ongoingBox  = document.getElementById("ngoOngoingList");

  const noIncoming = document.getElementById("ngoNoIncoming");
  const noApproved = document.getElementById("ngoNoApproved");
  const noOngoing  = document.getElementById("ngoNoOngoing");

  if (!incomingBox || !approvedBox || !ongoingBox) return;

  incomingBox.innerHTML = "";
  approvedBox.innerHTML = "";
  ongoingBox.innerHTML  = "";

  // Incoming
  if (myPending.length === 0) {
    noIncoming.style.display = "block";
  } else {
    noIncoming.style.display = "none";
    myPending.forEach(p => {
      const div = document.createElement("div");
      div.className = "card";
      div.innerHTML = `
        <h4>${p.title}</h4>
        <p>${p.desc || ""}</p>
        <p><b>Status:</b> Pending approval</p>
      `;
      incomingBox.appendChild(div);
    });
  }

  // Approved
  if (myApproved.length === 0) {
    noApproved.style.display = "block";
  } else {
    noApproved.style.display = "none";
    myApproved.forEach(p => {
      const div = document.createElement("div");
      div.className = "card";
      div.innerHTML = `
        <h4>${p.title}</h4>
        <p>${p.desc || ""}</p>
        <p><b>Goal:</b> ৳${Number(p.goal || 0).toLocaleString()}</p>
        <p><b>Raised:</b> ৳${Number(p.raised || 0).toLocaleString()}</p>
        <p><b>Status:</b> Approved</p>
      `;
      approvedBox.appendChild(div);
    });
  }

  // Ongoing (same as approved for now)
  if (myApproved.length === 0) {
    noOngoing.style.display = "block";
  } else {
    noOngoing.style.display = "none";
    myApproved.forEach(p => {
      const div = document.createElement("div");
      div.className = "card";
      div.innerHTML = `
        <h4>${p.title}</h4>
        <p><b>Raised:</b> ৳${Number(p.raised || 0).toLocaleString()}</p>
        <p><b>Status:</b> Ongoing</p>
      `;
      ongoingBox.appendChild(div);
    });
  }
}

// run once page loads
