
// Load Donor Data from localStorage (demoSession created on login)
const session = JSON.parse(localStorage.getItem('demoSession') || 'null');

if (session && session.role === 'donor') {
  document.getElementById('donorName').innerText = session.name || 'Donor';
  document.getElementById('donorEmail').innerText = session.email || 'Email Not Found';
} else {
  document.getElementById('donorName').innerText = 'Guest Donor';
  document.getElementById('donorEmail').innerText = 'Not logged in';
}

// Logout → clear session and go back to login
document.getElementById('logoutBtn').onclick = () => {
  localStorage.removeItem('demoSession');
  window.location.href = 'login.html';
};

// Load donation history (stored by donation.js in localStorage["donorHistory"])
// Load donation history (stored by donation.js in localStorage["donorHistory"])
const historyTableBody = document.getElementById('historyBody');
if (historyTableBody) {
  const history = JSON.parse(localStorage.getItem('donorHistory') || '[]');

  historyTableBody.innerHTML = '';

  if (history.length === 0) {
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 4; // now 4 columns (Project, Date, Amount, Status)
    cell.textContent = 'No donations yet (demo). Use the Donate buttons below or the Projects page.';
    row.appendChild(cell);
    historyTableBody.appendChild(row);
  } else {
    history.forEach(d => {
      const row = document.createElement('tr');

      // Project
      const projectCell = document.createElement('td');
      projectCell.textContent = d.project || 'N/A';

      // Date
      const dateCell = document.createElement('td');
      dateCell.textContent = d.date || '';

      // Amount
      const amountCell = document.createElement('td');
      amountCell.textContent = `BDT ${d.amount}`;

      // Status
      const statusCell = document.createElement('td');
      const statusSpan = document.createElement('span');
      const statusValue = (d.status || 'processing').toLowerCase();

      statusSpan.classList.add('status-badge');

      switch (statusValue) {
        case 'received':
          statusSpan.classList.add('status-received');
          statusSpan.textContent = 'Received by NGO';
          break;
        case 'implementing':
          statusSpan.classList.add('status-implementing');
          statusSpan.textContent = 'In Implementation';
          break;
        case 'completed':
          statusSpan.classList.add('status-completed');
          statusSpan.textContent = 'Completed';
          break;
        default:
          statusSpan.classList.add('status-processing');
          statusSpan.textContent = 'Processing';
      }

      statusCell.appendChild(statusSpan);

      row.appendChild(projectCell);
      row.appendChild(dateCell);
      row.appendChild(amountCell);
      row.appendChild(statusCell);

      historyTableBody.appendChild(row);
    });
  }
}
// Load project progress from projects.xml and show in cards
async function loadProjectProgressForDonor() {
  try {
    const resp = await fetch('projects.xml');
    if (!resp.ok) throw new Error('Could not load projects.xml');
    const text = await resp.text();
    const xml = new DOMParser().parseFromString(text, 'application/xml');
    const projects = xml.querySelectorAll('project');

    const infoByTitle = {};
    projects.forEach(p => {
      const title = p.querySelector('title')?.textContent || '';
      const goal = Number(p.querySelector('goal')?.textContent || 0);
      const raised = Number(p.querySelector('raised')?.textContent || 0);
      if (title) {
        infoByTitle[title] = { goal, raised };
      }
    });

    document.querySelectorAll('.project-card').forEach(card => {
      const titleEl = card.querySelector('h3');
      if (!titleEl) return;
      const title = titleEl.textContent.trim();
      const info = infoByTitle[title];

      const wrap = document.createElement('div');
      wrap.className = 'project-progress';

      if (!info || !info.goal) {
        wrap.innerHTML = '<p class="progress-label">Goal data not available (demo).</p>';
      } else {
        const percent = Math.min(100, Math.round((info.raised * 100) / info.goal));
        wrap.innerHTML = `
          <p class="progress-label">
            Raised ৳${info.raised.toLocaleString()} of ৳${info.goal.toLocaleString()} (${percent}%)
          </p>
          <div class="progress"><span style="width:${percent}%"></span></div>
        `;
      }

      card.appendChild(wrap);
    });
  } catch (err) {
    console.error(err);
  }
}

// Call it when dashboard loads
loadProjectProgressForDonor();


// Donate buttons in the "Ongoing Projects" cards
const donateButtons = document.querySelectorAll('.donate-btn');

donateButtons.forEach((btn, index) => {
  btn.onclick = function () {
    const projectNames = [
  "Flood Relief - Noakhali",
  "School Rebuild - Feni",
  "Clean Water Initiative - Cumilla"
];

    const selectedProject = projectNames[index];
    window.location.href = `donation.html?proj=${encodeURIComponent(selectedProject)}`;
  };
});
