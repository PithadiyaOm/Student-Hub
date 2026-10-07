document.addEventListener("DOMContentLoaded", function() {

    // 1. NOTIFICATION BANNER
    const banner = document.getElementById("notification-banner");
    const closeBannerBtn = document.getElementById("close-banner");
    if (banner && closeBannerBtn) {
        closeBannerBtn.addEventListener("click", () => banner.style.display = "none");
    }

    // 2. GLOBAL HAMBURGER MENU (Sidebar Toggle)
    const hamburgerBtn = document.getElementById("hamburger-btn");
    const sidebar = document.getElementById("sidebar");
    
    if (hamburgerBtn && sidebar) {
        // Toggle Sidebar
        hamburgerBtn.addEventListener("click", function(event) {
            sidebar.classList.toggle("active");
            event.stopPropagation();
        });

        // Close sidebar when clicking anywhere outside of it
        document.addEventListener("click", function(event) {
            if (sidebar.classList.contains("active") && !sidebar.contains(event.target)) {
                sidebar.classList.remove("active");
            }
        });
    }

    // 3. LIGHT/DARK THEME SWITCHER
    const themeBtn = document.getElementById("theme-toggle");
    const body = document.body;

    if (localStorage.getItem("theme") === "dark") {
        body.classList.add("dark-mode");
        if (themeBtn) themeBtn.innerHTML = "☀️ Light";
    }

    if (themeBtn) {
        themeBtn.addEventListener("click", function() {
            body.classList.toggle("dark-mode");
            if (body.classList.contains("dark-mode")) {
                localStorage.setItem("theme", "dark");
                themeBtn.innerHTML = "☀️ Light";
            } else {
                localStorage.setItem("theme", "light");
                themeBtn.innerHTML = "🌙 Dark";
            }
        });
    }

    // 4. CONTENT SLIDER
    const slides = document.querySelectorAll(".slide");
    const prevBtn = document.getElementById("prev-slide");
    const nextBtn = document.getElementById("next-slide");
    let currentSlide = 0;

    if (slides.length > 0 && prevBtn && nextBtn) {
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove("active"));
            slides[index].classList.add("active");
        }
        nextBtn.addEventListener("click", () => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        });
        prevBtn.addEventListener("click", () => {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        });
    }

    // 5. COLLAPSIBLE FAQ
    const faqQuestions = document.querySelectorAll(".faq-question");
    if (faqQuestions.length > 0) {
        faqQuestions.forEach(btn => {
            btn.addEventListener("click", function() {
                this.classList.toggle("active");
                const answer = this.nextElementSibling;
                const icon = this.querySelector(".faq-icon");
                if (answer.style.display === "block") {
                    answer.style.display = "none";
                    icon.textContent = "+";
                } else {
                    answer.style.display = "block";
                    icon.textContent = "-";
                }
            });
        });
    }

    // 6. MODAL POPUP
    const modal = document.getElementById("my-modal");
    const openBtns = document.querySelectorAll(".open-modal-btn");
    const closeModalBtn = document.getElementById("close-modal");

    if (modal && closeModalBtn) {
        openBtns.forEach(btn => {
            btn.addEventListener("click", () => modal.style.display = "flex");
        });
        closeModalBtn.addEventListener("click", () => modal.style.display = "none");
        window.addEventListener("click", (e) => {
            if (e.target === modal) modal.style.display = "none";
        });
    }
});
