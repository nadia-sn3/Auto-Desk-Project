document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector(".sidebar");
    const toggleButton = document.createElement("button");
    
    toggleButton.innerText = "☰";
    toggleButton.classList.add("sidebar-toggle");

    toggleButton.addEventListener("click", function () {
        sidebar.classList.toggle("open");
    });

    document.body.appendChild(toggleButton);
});
