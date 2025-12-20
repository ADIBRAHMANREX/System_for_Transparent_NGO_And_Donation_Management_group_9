

// Get project name from URL query
const urlParams = new URLSearchParams(window.location.search);
const project = urlParams.get('proj');
document.getElementById('projectName').innerText = project;

// Confirm donation
document.getElementById('confirmBtn').onclick = ()=>{
    const amount = document.getElementById('amountInput').value;
    const method = document.getElementById('paymentSelect').value;

    if(amount < 1) return alert("Amount must be greater than 0");

    alert(`Donation Successful!\n\nProject: ${project}\nAmount: ${amount}\nMethod: ${method}`);

    // For now, store in localStorage (demo)
   const donationRecord = {
  project: project,
  amount: amount,
  method: method,
  date: new Date().toISOString().slice(0, 10),
  status: 'processing'   // initial status
};


    let history = JSON.parse(localStorage.getItem('donorHistory') || '[]');
    history.push(donationRecord);
    localStorage.setItem('donorHistory', JSON.stringify(history));

    window.location.href="donor_dashboard.html";
};

// Back to dashboard
document.getElementById('backBtn').onclick = ()=>{
    window.location.href="donor_dashboard.html";
};
