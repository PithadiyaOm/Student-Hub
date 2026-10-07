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

// ==========================================
    // PRACTICAL 6: FETCH API, SEARCH, FILTER & PAGINATION
    // ==========================================
    const dataContainer = document.getElementById("data-container");

    if (dataContainer) {
        const searchInput = document.getElementById("searchInput");
        const categoryFilter = document.getElementById("categoryFilter");
        const sortFilter = document.getElementById("sortFilter");
        const recordCount = document.getElementById("record-count");
        const paginationControls = document.getElementById("pagination-controls");
        const prevPageBtn = document.getElementById("prevPage");
        const nextPageBtn = document.getElementById("nextPage");
        const pageInfo = document.getElementById("pageInfo");
        const loadingState = document.getElementById("loading-state");

        let rawData = [];
        let filteredData = [];
        let currentPage = 1;
        const recordsPerPage = 4; 

        // 1. Fetch JSON Data & Handle Errors/Caching
        async function fetchEvents() {
            try {
                // Ensure this path matches your folder structure (e.g., './data/events.json')
                const response = await fetch('data/events.json');
                if (!response.ok) throw new Error("Network response was not ok.");
                
                const data = await response.json();
                rawData = data;
                
                // Advanced Extension: Cache data in localStorage for offline use
                localStorage.setItem("cachedEvents", JSON.stringify(data));
                applyFiltersAndRender();

            } catch (error) {
                console.error("Fetch failed:", error);
                // Fallback to offline cache
                const cachedData = localStorage.getItem("cachedEvents");
                if (cachedData) {
                    rawData = JSON.parse(cachedData);
                    applyFiltersAndRender();
                } else {
                    loadingState.innerHTML = `<span class="error-text">Error loading data. Make sure you are using a local web server (like VS Code Live Server) and that data/events.json exists.</span>`;
                }
            }
        }

        // 2. Map, Filter, and Sort Array Methods
        function applyFiltersAndRender() {
            const searchTerm = searchInput.value.toLowerCase();
            const category = categoryFilter.value;
            const sortMode = sortFilter.value;

            // Apply Filter
            filteredData = rawData.filter(item => {
                const matchesSearch = item.title.toLowerCase().includes(searchTerm) || 
                                      item.location.toLowerCase().includes(searchTerm);
                const matchesCategory = category === "All" || item.category === category;
                return matchesSearch && matchesCategory;
            });

            // Apply Sort
            if (sortMode === "date-asc") {
                filteredData.sort((a, b) => new Date(a.date) - new Date(b.date));
            } else if (sortMode === "date-desc") {
                filteredData.sort((a, b) => new Date(b.date) - new Date(a.date));
            }

            currentPage = 1; // Reset to page 1 on new filter
            renderPage();
        }

        // 3. Dynamic Rendering & Pagination Logic
        function renderPage() {
            dataContainer.innerHTML = ""; 
            const totalPages = Math.ceil(filteredData.length / recordsPerPage) || 1;
            
            recordCount.textContent = `${filteredData.length} records found · Showing page ${currentPage} of ${totalPages}`;

            if (filteredData.length === 0) {
                dataContainer.innerHTML = "<p>No events found matching your search criteria.</p>";
                paginationControls.style.display = "none";
                return;
            }

            // Array Slice for Pagination
            const startIndex = (currentPage - 1) * recordsPerPage;
            const paginatedItems = filteredData.slice(startIndex, startIndex + recordsPerPage);

            // Generate HTML for each event object
            paginatedItems.forEach(event => {
                const eventDate = new Date(event.date);
                const formattedDate = eventDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + " · " + eventDate.toLocaleTimeString('en-US', { hour: '2-digit', minute:'2-digit' });

                // Map category to color tag
                let tagClass = "tag";
                if (event.category === "Academic" || event.category === "Workshop") tagClass = "tag academic";
                if (event.category === "Sports" || event.category === "Cultural") tagClass = "tag event";

                const cardHTML = `
                    <div class="event-card">
                        <div class="event-header">
                            <span class="${tagClass}" style="margin: 0;">${event.category}</span>
                            <span class="event-date">${formattedDate}</span>
                        </div>
                        <h3>${event.title}</h3>
                        <p>${event.desc}</p>
                        <div class="event-footer">
                            📍 ${event.location}
                        </div>
                    </div>
                `;
                dataContainer.insertAdjacentHTML('beforeend', cardHTML);
            });

            // Update Pagination UI
            paginationControls.style.display = totalPages > 1 ? "flex" : "none";
            pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
            
            prevPageBtn.disabled = currentPage === 1;
            nextPageBtn.disabled = currentPage === totalPages;
            prevPageBtn.style.opacity = currentPage === 1 ? "0.5" : "1";
            nextPageBtn.style.opacity = currentPage === totalPages ? "0.5" : "1";
        }

        // 4. Attach Event Listeners
        searchInput.addEventListener("input", applyFiltersAndRender);
        categoryFilter.addEventListener("change", applyFiltersAndRender);
        sortFilter.addEventListener("change", applyFiltersAndRender);

        prevPageBtn.addEventListener("click", () => {
            if (currentPage > 1) { currentPage--; renderPage(); }
        });

        nextPageBtn.addEventListener("click", () => {
            const totalPages = Math.ceil(filteredData.length / recordsPerPage);
            if (currentPage < totalPages) { currentPage++; renderPage(); }
        });

        // Trigger fetch on load
        fetchEvents();
    }

    // ==========================================
    // PRACTICAL 6 EXTENSION: NOTICES, DIRECTORY, FAQS
    // ==========================================
    
    // 1. NOTICES PAGE LOGIC
    const noticesContainer = document.getElementById("notices-container");
    if (noticesContainer) {
        let nData = [], fData = [], curPage = 1, perPage = 4;
        
        async function fetchNotices() {
            try {
                const res = await fetch('data/notices.json');
                nData = await res.json();
                renderNotices();
            } catch (e) { noticesContainer.innerHTML = "<p>Error loading notices.</p>"; }
        }

        function renderNotices() {
            const search = document.getElementById("noticeSearch").value.toLowerCase();
            const cat = document.getElementById("noticeCategory").value;
            const sort = document.getElementById("noticeSort").value;

            fData = nData.filter(i => (i.title.toLowerCase().includes(search) || i.desc.toLowerCase().includes(search)) && (cat === "All" || i.category === cat));
            
            if (sort === "date-desc") fData.sort((a, b) => new Date(b.date) - new Date(a.date));
            if (sort === "date-asc") fData.sort((a, b) => new Date(a.date) - new Date(b.date));

            const total = Math.ceil(fData.length / perPage) || 1;
            document.getElementById("notice-count").textContent = `${fData.length} records found · Showing page ${curPage} of ${total}`;
            
            noticesContainer.innerHTML = "";
            fData.slice((curPage - 1) * perPage, curPage * perPage).forEach(n => {
                const prioClass = n.priority === "High" ? "priority-high" : "priority-normal";
                noticesContainer.innerHTML += `
                    <div class="event-card">
                        <div class="event-header">
                            <span class="tag">${n.category}</span>
                            <span class="${prioClass}">${n.priority}</span>
                        </div>
                        <h3>${n.title}</h3>
                        <p>${n.desc}</p>
                        <div class="event-footer" style="color: var(--text-muted);">${n.date}</div>
                    </div>`;
            });

            document.getElementById("notice-pagination").style.display = total > 1 ? "flex" : "none";
            document.getElementById("noticePageInfo").textContent = `Page ${curPage} of ${total}`;
        }

        document.getElementById("noticeSearch").addEventListener("input", () => { curPage = 1; renderNotices(); });
        document.getElementById("noticeCategory").addEventListener("change", () => { curPage = 1; renderNotices(); });
        document.getElementById("noticeSort").addEventListener("change", () => { curPage = 1; renderNotices(); });
        document.getElementById("noticePrev").addEventListener("click", () => { if(curPage > 1) { curPage--; renderNotices(); } });
        document.getElementById("noticeNext").addEventListener("click", () => { if(curPage < Math.ceil(fData.length/perPage)) { curPage++; renderNotices(); } });
        fetchNotices();
    }

    // 2. STUDENT DIRECTORY LOGIC
    const dirContainer = document.getElementById("directory-container");
    if (dirContainer) {
        let sData = [], fData = [], curPage = 1, perPage = 4;
        
        async function fetchDirectory() {
            try {
                const res = await fetch('data/students.json');
                sData = await res.json();
                renderDirectory();
            } catch (e) { dirContainer.innerHTML = "<p>Error loading directory.</p>"; }
        }

        function renderDirectory() {
            const search = document.getElementById("dirSearch").value.toLowerCase();
            const cat = document.getElementById("dirCategory").value;
            const sort = document.getElementById("dirSort").value;

            fData = sData.filter(i => (i.name.toLowerCase().includes(search) || i.id.toLowerCase().includes(search) || i.skills.join(" ").toLowerCase().includes(search)) && (cat === "All" || i.semester === cat));
            
            if (sort === "name-asc") fData.sort((a, b) => a.name.localeCompare(b.name));
            if (sort === "name-desc") fData.sort((a, b) => b.name.localeCompare(a.name));

            const total = Math.ceil(fData.length / perPage) || 1;
            document.getElementById("dir-count").textContent = `${fData.length} records found · Showing page ${curPage} of ${total}`;
            
            dirContainer.innerHTML = "";
            fData.slice((curPage - 1) * perPage, curPage * perPage).forEach(s => {
                const skillsHtml = s.skills.map(skill => `<span class="skill-tag">${skill}</span>`).join("");
                dirContainer.innerHTML += `
                    <div class="event-card">
                        <div class="profile-header">
                            <div class="profile-avatar">${s.name.charAt(0)}</div>
                            <span class="event-date">${s.semester}</span>
                        </div>
                        <h3>${s.name}</h3>
                        <p style="margin-bottom: 5px;">${s.id} · ${s.program}</p>
                        <a href="mailto:${s.email}" style="color: var(--text-muted); font-size: 0.85rem;">${s.email}</a>
                        <div class="skills-container">${skillsHtml}</div>
                    </div>`;
            });

            document.getElementById("dir-pagination").style.display = total > 1 ? "flex" : "none";
            document.getElementById("dirPageInfo").textContent = `Page ${curPage} of ${total}`;
        }

        document.getElementById("dirSearch").addEventListener("input", () => { curPage = 1; renderDirectory(); });
        document.getElementById("dirCategory").addEventListener("change", () => { curPage = 1; renderDirectory(); });
        document.getElementById("dirSort").addEventListener("change", () => { curPage = 1; renderDirectory(); });
        document.getElementById("dirPrev").addEventListener("click", () => { if(curPage > 1) { curPage--; renderDirectory(); } });
        document.getElementById("dirNext").addEventListener("click", () => { if(curPage < Math.ceil(fData.length/perPage)) { curPage++; renderDirectory(); } });
        fetchDirectory();
    }

    // 3. FAQ DYNAMIC JSON LOGIC
    const faqContainer = document.getElementById("faq-container");
    if (faqContainer) {
        let fqData = [], fData = [], curPage = 1, perPage = 6;
        
        async function fetchFaqs() {
            try {
                const res = await fetch('data/faqs.json');
                fqData = await res.json();
                renderFaqs();
            } catch (e) { faqContainer.innerHTML = "<p>Error loading FAQs.</p>"; }
        }

        function renderFaqs() {
            const search = document.getElementById("faqSearch").value.toLowerCase();
            const cat = document.getElementById("faqCategory").value;

            fData = fqData.filter(i => (i.question.toLowerCase().includes(search) || i.answer.toLowerCase().includes(search)) && (cat === "All" || i.category === cat));
            
            const total = Math.ceil(fData.length / perPage) || 1;
            document.getElementById("faq-count").textContent = `${fData.length} records found · Showing page ${curPage} of ${total}`;
            
            faqContainer.innerHTML = "";
            fData.slice((curPage - 1) * perPage, curPage * perPage).forEach(f => {
                faqContainer.innerHTML += `
                    <div class="event-card">
                        <span class="tag" style="margin-bottom: 10px;">${f.category}</span>
                        <h3 style="font-size: 1.1rem;">${f.question}</h3>
                        <p>${f.answer}</p>
                    </div>`;
            });

            document.getElementById("faq-pagination").style.display = total > 1 ? "flex" : "none";
            document.getElementById("faqPageInfo").textContent = `Page ${curPage} of ${total}`;
        }

        document.getElementById("faqSearch").addEventListener("input", () => { curPage = 1; renderFaqs(); });
        document.getElementById("faqCategory").addEventListener("change", () => { curPage = 1; renderFaqs(); });
        document.getElementById("faqPrev").addEventListener("click", () => { if(curPage > 1) { curPage--; renderFaqs(); } });
        document.getElementById("faqNext").addEventListener("click", () => { if(curPage < Math.ceil(fData.length/perPage)) { curPage++; renderFaqs(); } });
        fetchFaqs();
    }
