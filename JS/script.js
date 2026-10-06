// ==========================================
// 1. NOTIFICATION BANNER
// ==========================================
const banner = document.getElementById("notification-banner");
const closeBannerBtn = document.getElementById("close-banner");

// When the close button is clicked, hide the banner
closeBannerBtn.addEventListener("click", function() {
    banner.style.display = "none";
});

// ==========================================
// 2. HAMBURGER MENU
// ==========================================
const hamburgerBtn = document.getElementById("hamburger-btn");
const navMenu = document.getElementById("nav-menu");

// Toggle the 'active' class to show/hide the menu on mobile
hamburgerBtn.addEventListener("click", function() {
    navMenu.classList.toggle("active");
});

// ==========================================
// 3. LIGHT/DARK THEME SWITCHER (with localStorage)
// ==========================================
const themeBtn = document.getElementById("theme-toggle");
const body = document.body;

// Check local storage when the page loads to remember the user's choice
if (localStorage.getItem("theme") === "dark") {
    body.classList.add("dark-mode");
    themeBtn.textContent = "☀️ Light Mode";
}

// When the theme button is clicked, toggle dark mode
themeBtn.addEventListener("click", function() {
    body.classList.toggle("dark-mode");
    
    // Save preference to localStorage
    if (body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
        themeBtn.textContent = "☀️ Light Mode";
    } else {
        localStorage.setItem("theme", "light");
        themeBtn.textContent = "🌙 Dark Mode";
    }
});

// ==========================================
// 4. CONTENT SLIDER
// ==========================================
const slides = document.querySelectorAll(".slide");
const prevBtn = document.getElementById("prev-slide");
const nextBtn = document.getElementById("next-slide");
let currentSlide = 0;

// Function to show a specific slide
function showSlide(index) {
    // Hide all slides
    slides.forEach(slide => slide.classList.remove("active"));
    // Show the targeted slide
    slides[index].classList.add("active");
}

nextBtn.addEventListener("click", function() {
    currentSlide++;
    if (currentSlide >= slides.length) currentSlide = 0; // Loop back to start
    showSlide(currentSlide);
});

prevBtn.addEventListener("click", function() {
    currentSlide--;
    if (currentSlide < 0) currentSlide = slides.length - 1; // Loop to end
    showSlide(currentSlide);
});

// ==========================================
// 5. MODAL POPUP
// ==========================================
const modal = document.getElementById("my-modal");
const openModalBtn = document.getElementById("open-modal");
const closeModalBtn = document.getElementById("close-modal");

// Open modal
openModalBtn.addEventListener("click", function() {
    modal.style.display = "flex";
});

// Close modal when X is clicked
closeModalBtn.addEventListener("click", function() {
    modal.style.display = "none";
});

// Close modal if user clicks outside the modal content
window.addEventListener("click", function(event) {
    if (event.target === modal) {
        modal.style.display = "none";
    }
});

// ==========================================
// 6. COLLAPSIBLE FAQ
// ==========================================
const faqButtons = document.querySelectorAll(".faq-btn");

faqButtons.forEach(button => {
    button.addEventListener("click", function() {
        // Toggle the active class for styling
        this.classList.toggle("active");
        
        // Find the next element (the answer) and toggle its visibility
        const content = this.nextElementSibling;
        if (content.style.display === "block") {
            content.style.display = "none";
        } else {
            content.style.display = "block";
        }
    });
});
