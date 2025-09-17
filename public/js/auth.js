let authToken = localStorage.getItem("auth_token");

function updateHeader() {
    const authSection = document.getElementById("auth-section");
    const loginSection = document.getElementById("login-section");

    if (authToken) {
        authSection.classList.remove("hidden");
        authSection.classList.add("flex");
        loginSection.classList.add("hidden");

        getCurrentUser();
    } else {
        authSection.classList.add("hidden");
        authSection.classList.remove("flex");
        loginSection.classList.remove("hidden");
    }
}

async function getCurrentUser() {
    if (!authToken) return;

    try {
        const data = await apiCall(API_ENDPOINTS.USER);
        if (data.name) {
            document.getElementById("user-name").textContent = data.name;
        }
    } catch (error) {
        authToken = null;
        localStorage.removeItem("auth_token");
        updateHeader();
    }
}

// Authentication functions
async function login() {
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    if (!email || !password) {
        alert("Please enter email and password");
        return;
    }

    try {
        const data = await apiCall(API_ENDPOINTS.LOGIN, {
            method: "POST",
            body: JSON.stringify({ email, password }),
        });

        if (data.token) {
            authToken = data.token;
            localStorage.setItem("auth_token", authToken);
            updateHeader();
            document.getElementById("email").value = "";
            document.getElementById("password").value = "";
            loadArticles(); // Refresh articles to show comment forms
        } else {
            alert(data.message || "Login failed");
        }
    } catch (error) {
        alert("Login failed");
    }
}

async function logout() {
    try {
        await apiCall(API_ENDPOINTS.LOGOUT, { method: "POST" });
    } catch (error) {
        console.error("Logout error:", error);
    }

    authToken = null;
    localStorage.removeItem("auth_token");
    updateHeader();
    loadArticles(); // Refresh articles to hide comment forms
}

function getAuthToken() {
    return authToken;
}

function setAuthToken(token) {
    authToken = token;
    if (token) {
        localStorage.setItem("auth_token", token);
    } else {
        localStorage.removeItem("auth_token");
    }
}
