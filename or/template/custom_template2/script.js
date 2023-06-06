let hamburger_menu = document.getElementById("hamburger-menu");
let nav_list = document.getElementById("nav-list");

hamburger_menu.addEventListener("click", () => {
    nav_list.classList.toggle("show");
});