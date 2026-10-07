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

// ==========================================
// PRACTICAL 5: FORM VALIDATION
// ==========================================
const regForm = document.getElementById("registrationForm");

if (regForm) {
    // Elements
    const nameEl = document.getElementById("name");
    const emailEl = document.getElementById("email");
    const mobileEl = document.getElementById("mobile");
    const passwordEl = document.getElementById("password");
    const confirmEl = document.getElementById("confirm-password");
    const courseEl = document.getElementById("course");
    const yearEl = document.getElementById("year");
    const termsEl = document.getElementById("terms");
    const strengthText = document.getElementById("passwordStrength");
        
    // Regular Expressions
    const nameRegex = /^[a-zA-Z\s]{3,}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const mobileRegex = /^[0-9]{10}$/;

    // Helper function to show/hide errors
    function validateField(element, condition, errorMsgId, errorMessage) {
        const errorEl = document.getElementById(errorMsgId);
        if (condition) {
            errorEl.textContent = "";
            element.classList.remove("input-error");
            return true;
        } else {
            errorEl.textContent = errorMessage;
            element.classList.add("input-error");
            return false;
        }
    }

    // 1. Password Strength Checker[cite: 13, 14]
    passwordEl.addEventListener("input", function() {
        const val = passwordEl.value;
        let strength = 0;
        
        if (val.length >= 8) strength++; // Length
        if (/[A-Z]/.test(val) && /[a-z]/.test(val)) strength++; // Mixed case
        if (/[0-9]/.test(val)) strength++; // Numbers
        if (/[^A-Za-z0-9]/.test(val)) strength++; // Special chars

        strengthText.className = "password-strength"; // Reset classes
        
        if (val.length === 0) {
            strengthText.textContent = "";
        } else if (strength <= 2) {
            strengthText.textContent = "Password Strength: Weak";
            strengthText.classList.add("strength-weak");
        } else if (strength === 3) {
            strengthText.textContent = "Password Strength: Medium";
            strengthText.classList.add("strength-medium");
        } else {
            strengthText.textContent = "Password Strength: Strong";
            strengthText.classList.add("strength-strong");
        }

        // Real-time confirm password check[cite: 14]
        if (confirmEl.value.length > 0) {
            validateField(confirmEl, confirmEl.value === passwordEl.value, "confirmError", "Passwords do not match.");
        }
    });

    // 2. Real-time validation extensions[cite: 14]
    nameEl.addEventListener("input", () => validateField(nameEl, nameRegex.test(nameEl.value.trim()), "nameError", "Full name is required (letters only)."));
    emailEl.addEventListener("input", () => validateField(emailEl, emailRegex.test(emailEl.value.trim()), "emailError", "Please enter a valid email address."));
    mobileEl.addEventListener("input", () => validateField(mobileEl, mobileRegex.test(mobileEl.value.trim()), "mobileError", "Mobile number must be exactly 10 digits."));
    confirmEl.addEventListener("input", () => validateField(confirmEl, confirmEl.value === passwordEl.value, "confirmError", "Passwords do not match."));
    courseEl.addEventListener("change", () => validateField(courseEl, courseEl.value !== "", "courseError", "Please select your course."));
    yearEl.addEventListener("change", () => validateField(yearEl, yearEl.value !== "", "yearError", "Please select your year."));
    termsEl.addEventListener("change", () => document.getElementById("termsError").textContent = termsEl.checked ? "" : "You must accept the Terms and Conditions.");

    // 3. Form Submit Handler
    regForm.addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent page reload
        
        let isValid = true;

        // Validate all fields on submit[cite: 13]
        isValid &= validateField(nameEl, nameRegex.test(nameEl.value.trim()), "nameError", "Full name is required.");
        isValid &= validateField(emailEl, emailRegex.test(emailEl.value.trim()), "emailError", "Valid email is required.");
        isValid &= validateField(mobileEl, mobileRegex.test(mobileEl.value.trim()), "mobileError", "10-digit mobile number is required.");
        isValid &= validateField(passwordEl, passwordEl.value.length >= 8, "passwordError", "Password must be at least 8 characters.");
        isValid &= validateField(confirmEl, confirmEl.value === passwordEl.value && confirmEl.value !== "", "confirmError", "Please confirm your password.");
        isValid &= validateField(courseEl, courseEl.value !== "", "courseError", "Please select your course.");
        isValid &= validateField(yearEl, yearEl.value !== "", "yearError", "Please select your year.");
            
        // Validate Gender Radio Buttons
        const genderSelected = document.querySelector('input[name="gender"]:checked');
        const genderError = document.getElementById("genderError");
        if (!genderSelected) {
            genderError.textContent = "Please select your gender.";
            isValid = false;
        } else {
            genderError.textContent = "";
        }

        // Validate Checkbox
        const termsError = document.getElementById("termsError");
        if (!termsEl.checked) {
            termsError.textContent = "You must accept the Terms and Conditions.";
            isValid = false;
        } else {
            termsError.textContent = "";
        }

        // If completely valid, show success message
        if (isValid) {
            document.getElementById("formSuccess").style.display = "block";
            regForm.reset(); // Clear the form
            strengthText.textContent = ""; // Clear password text
        } else {
            document.getElementById("formSuccess").style.display = "none";
        }
    });
}
