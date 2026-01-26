document.querySelector('.brand').onclick = function(){
    window.location.href = "index.html";
};

document.addEventListener('DOMContentLoaded', function () {
  const newsSubmit = document.getElementById('news-submit');
  if (newsSubmit) {
    newsSubmit.addEventListener('click', () => {
      const email = document.getElementById('news-input').value.trim();
      if (!email) return alert('Please enter your email.');
      // Demo: store to localStorage (frontend-only)
      let subs = JSON.parse(localStorage.getItem('newsletterSubs') || '[]');
      if (subs.includes(email)) return alert('You are already subscribed (demo).');
      subs.push(email);
      localStorage.setItem('newsletterSubs', JSON.stringify(subs));
      alert('Thanks — you are subscribed (demo).');
      document.getElementById('news-input').value = '';
    });
  }
});
