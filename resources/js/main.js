let user = document.querySelector("#user");
let profile_menu = document.getElementById("profile_menu");
user.onclick = function () {
  profile_menu.classList.toggle("actived");
};
// Tabs
let tabs = document.querySelectorAll(".historyTab");
let tabsArray = Array.from(tabs);
let divs = document.querySelectorAll(
  ".history .container > div:not(:first-child)"
);
let divsArray = Array.from(divs);
if (tabs && divs) {
  tabsArray.forEach((ele) => {
    ele.addEventListener("click", function (e) {
      e.preventDefault();
      tabsArray.forEach((ele) => {
        ele.classList.remove("historyTabActive");
      });
      ele.classList.add("historyTabActive");
      divsArray.forEach((div) => {
        div.style.display = "none";
      });

      document.querySelector(e.currentTarget.dataset.cont).style.display =
        "block";
    });
  });
}