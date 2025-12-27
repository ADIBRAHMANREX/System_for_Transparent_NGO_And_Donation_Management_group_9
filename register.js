const $ = (id) => document.getElementById(id);

let currentStep = 1;
let selectedRole = null;

function showError(msg){
  $("regError").style.display = "block";
  $("regError").textContent = msg;
}
function clearError(){
  $("regError").style.display = "none";
  $("regError").textContent = "";
}

function setStep(step){
  currentStep = step;
  $("step1").style.display = step === 1 ? "block" : "none";
  $("step2").style.display = step === 2 ? "block" : "none";
  $("step3").style.display = step === 3 ? "block" : "none";

  document.querySelectorAll(".step-dot").forEach(dot => {
    dot.classList.toggle("active", Number(dot.dataset.step) === step);
  });

  $("regSub").textContent =
    step === 1 ? "Let’s start with your details." :
    step === 2 ? "Secure your account." :
    "Choose your account type.";
  clearError();
}

async function checkEmail(email){
  const res = await fetch(`check_email.php?email=${encodeURIComponent(email)}`);
  const data = await res.json();
  return data.exists === true;
}

$("email").addEventListener("blur", async () => {
  const email = $("email").value.trim();
  $("emailHint").textContent = "";
  if(!email) return;

  const okFormat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  if(!okFormat){
    $("emailHint").textContent = "Email format looks wrong.";
    return;
  }
  const exists = await checkEmail(email);
  $("emailHint").textContent = exists ? "Email already exists." : "Email is available.";
});

$("next1").addEventListener("click", async () => {
  const first = $("first_name").value.trim();
  const last  = $("last_name").value.trim();
  const email = $("email").value.trim();

  if(!first || !last) return showError("First and last name are required.");
  const okFormat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  if(!okFormat) return showError("Enter a valid email.");

  const exists = await checkEmail(email);
  if(exists) return showError("This email is already registered.");

  setStep(2);
});

$("back2").addEventListener("click", () => setStep(1));

$("next2").addEventListener("click", () => {
  const pw1 = $("password").value;
  const pw2 = $("password2").value;

  if(pw1.length < 8) return showError("Password must be at least 8 characters.");
  if(pw1 !== pw2) return showError("Passwords do not match.");

  setStep(3);
});

$("back3").addEventListener("click", () => setStep(2));

document.querySelectorAll(".role-card").forEach(btn => {
  btn.addEventListener("click", () => {
    selectedRole = btn.dataset.role;
    document.querySelectorAll(".role-card").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
    clearError();
  });
});

$("submitBtn").addEventListener("click", async () => {
  if(!selectedRole) return showError("Select a role to continue.");

  const payload = {
    csrf: $("csrf").value,
    first_name: $("first_name").value.trim(),
    last_name: $("last_name").value.trim(),
    email: $("email").value.trim(),
    password: $("password").value,
    role: selectedRole
  };

  const res = await fetch("auth_register.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify(payload)
  });

  const data = await res.json();

  if(!data.success){
    return showError(data.error || "Registration failed.");
  }

  if(data.user.role === "ngo" && data.user.status === "pending"){
    alert("NGO registered. Waiting for admin approval.");
    // change this if your pending page has a different name
    window.location.href = "ngo_pending.html";
  } else {
    alert("Account created. Please login.");
    window.location.href = "login.php";
  }
});

setStep(1);
