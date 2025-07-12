
document.getElementById("loginForm").addEventListener("submit", function(event) {
  event.preventDefault();

  const formData = new FormData(this);

  fetch("/backend/api/auth/login.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      window.location.href = "dashboard.php"; // beddel bogga haddii la galo
    } else {
      document.getElementById("loginError").textContent = data.message;
    }
  })
  .catch(error => {
    document.getElementById("loginError").textContent = "Error occurred. Try again.";
    console.error("Login error:", error);
  });
});



/*document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");
  const errorMsg = document.getElementById("loginError");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(form);
    formData.append("action", "login_user");

    fetch("backend.php", {
      method: "POST",
      body: formData,
    })
      .then(async res => {
        const text = await res.text();
        try {
          const data = JSON.parse(text);
          if (data.success) {
            window.location.href = "dashbood.php";
          } else {
            errorMsg.textContent = data.message;
          }
        } catch (e) {
          console.error("Server response was not JSON:", text);
          errorMsg.textContent = "Login error: " + text;
        }
      })
      .catch(error => {
        console.error("Fetch error:", error);
        errorMsg.textContent = "Login failed due to an error.";
      });
  });
}); */
