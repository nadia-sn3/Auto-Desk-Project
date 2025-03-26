document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("userSearch");

    searchInput.addEventListener("keyup", function () {
        let input = searchInput.value.toLowerCase();
        let rows = document.querySelectorAll(".user-row");

        rows.forEach(row => {
            let name = row.querySelector(".user-name").textContent.toLowerCase();
            let email = row.querySelector(".user-email").textContent.toLowerCase();

            if (name.includes(input) || email.includes(input)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});
