/* ===== تكبير/تصغير اللوجو عند التمرير ===== */
const topLogo = document.querySelector('.top-logo');
window.addEventListener('scroll', () => {
    if(window.scrollY > 50){
        topLogo.classList.add('shrink');
    } else {
        topLogo.classList.remove('shrink');
    }
});

/* ===== قائمة الموبايل ===== */
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');
if(menuToggle) {
    menuToggle.addEventListener('click', () => {
        navLinks.classList.toggle('show');
    });
}

/* ===== حركة العناصر عند التمرير ===== */
const animateElements = document.querySelectorAll('.animate');
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if(entry.isIntersecting){
            entry.target.classList.add('show');
        }
    });
}, { threshold: 0.2 });

animateElements.forEach(el => observer.observe(el));

/* ===== إرسال الرسالة عبر واتساب ===== */
function sendMessage(){
    const managerPhone = "0509084202";
    const barberPhone = "0548840672";

    const name = document.getElementById("name")?.value;
    const phone = document.getElementById("phone")?.value;
    const message = document.getElementById("message")?.value;

    if(!name || !phone || !message){
        alert("يرجى تعبئة جميع الحقول");
        return;
    }

    const fullMessage = `رسالة جديدة من ${name}\nالجوال: ${phone}\nالرسالة: ${message}`;

    const urlBarber = `https://wa.me/${barberPhone}?text=${encodeURIComponent(fullMessage)}`;
    window.open(urlBarber, '_blank');

    const urlManager = `https://wa.me/${managerPhone}?text=${encodeURIComponent(fullMessage)}`;
    window.open(urlManager, '_blank');
}
