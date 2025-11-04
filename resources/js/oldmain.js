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
//search
function searchh() {
  console.log("searchh");

  let input, filter, myTable, tr, td, i, filterAll;
  input = document.getElementById("search");
  filter = input.value.toUpperCase();
  myTable = document.getElementsByClassName("myTable");
  filterAll = document.getElementById("filterAll");
  filterAll.checked = true;
  let myTableArray = Array.from(myTable);
  if (myTable) {
    myTableArray.forEach((ele) => {
      tr = ele.querySelectorAll("tbody tr");
      for (i = 0; i < tr.length; i++) {
        tr[i].style.display = "none";
        td = tr[i].getElementsByTagName("td");
        for (let j = 0; j < td.length; j++) {
          cell = tr[i].getElementsByTagName("td")[j];
          if (cell) {
            if (cell.innerHTML.toUpperCase().indexOf(filter) > -1) {
              tr[i].style.display = "";
              break;
            }
          }
        }
      }
    });
  }
}

function searchhh() {
  let input, filter, myTable, tr, td, i, txtValue;
  input = document.getElementById("search");
  filter = input.value.toUpperCase();
  myTable = document.getElementsByClassName("myTable")[0];
  tr = myTable.querySelectorAll("tbody tr");
  for (i = 0; i < tr.length; i++) {
    tr[i].style.display = "none";
    td = tr[i].getElementsByTagName("td");
    for (let j = 0; j < td.length; j++) {
      cell = tr[i].getElementsByTagName("td")[j];
      if (cell) {
        if (cell.innerHTML.toUpperCase().indexOf(filter) > -1) {
          tr[i].style.display = "";
          break;
        }
      }
    }
  }
}
function validateInput(input) {
  if (input.value.length > 1) {
    input.style.border = "1px solid #ddd";
  } else if (input.value.length === 0) {
    input.style.border = "1px solid #d9534f";
  }
}

function deleteNumber(btn) {
  let element = btn.parentElement.parentElement;
  number.removeChild(element);
  counter--;
  updateInputid();
}
function updateInputid() {
  const inputEls = number.getElementsByTagName("input");
  const label = number.getElementsByTagName("label");
  for (let i = 0; i < inputEls.length; i++) {
    inputEls[i].id = "number" + (i + 1);
    label[i].htmlFor = "number" + (i + 1);
    label[i].innerHTML = `رقم الهاتف ${i + 1}`;
  }
}
// add
let count = 1;

function createpkg() {
  const selectValue = document.getElementById("packagequnt").value;
  const container = document.getElementById("packagesDet");

  while (container.childElementCount > selectValue) {
    container.removeChild(container.lastChild);
  }

  for (let i = container.childElementCount + 1; i <= selectValue; i++) {
    let div = document.createElement("div");
    div.classList.add("packageDet");
    div.innerHTML = `
    <h2 class="backnum">الصنف ${i}</h2>
    <div>
      <label for="qun${i}">العدد :</label>
      <input
        type="number"
        name="qun[]"
        id="qun${i}"
        class="qun"
        min="1"
        value="1"
      />
    </div>
    <div>
      <label for="desc${i}">الوصف :</label>
      <textarea name="desc[]" id="desc${i}" class="desc"></textarea>
    </div>
    <button onclick="deletepkg(this, event)" class="delete">حذف</button>
    `;
    container.appendChild(div);
  }
}

// printBtn

function pagePrint(event) {
  event.preventDefault();
  window.print();
}
// unPaid
const paymentStatusSelect = document.getElementById("paymentStatus");
const paymentAmountDiv = document.getElementById("paymentAmount");
if (paymentStatusSelect) {
  paymentStatusSelect.addEventListener("change", function () {
    if (paymentStatusSelect.value == "unpaid") {
      paymentAmountDiv.classList.remove("hidden");
    } else {
      paymentAmountDiv.classList.add("hidden");
    }
  });
}
const paymentPaid = document.getElementById("paymentPaid");
const paymentPks = document.getElementById("paymentPks");
const paidInMainOffice = document.getElementById("paidInMainOffice");
if (paymentPaid) {
  paymentPaid.addEventListener("change", function () {
    if (paymentPaid.value == "unpaid") {
      paymentPks.classList.remove("hidden");
      paidInMainOffice.classList.remove("hidden");
    } else {
      paymentPks.classList.add("hidden");
      paidInMainOffice.classList.add("hidden");
    }
  });
}

// printBtn

// logo
let logo = Array.from(document.getElementsByClassName("logo"));
logo.forEach((btn) => {
  btn.addEventListener("click", (event) => {
    event.preventDefault();
    window.location.replace("index.php");
  });
});
// fetch
// if (
//   window.location.href === "https://system.qwafeltravel.com/index.php" ||
//   window.location.href === "https://system.qwafeltravel.com"
// ) {
function parcelsRequest() {
  $.ajax({
    url: route('fetch-last-parcels'),
    method: "post",
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    success: function (response) {
      $("#indexParecelsBody").html(response);
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}
parcelsRequest();
setInterval(parcelsRequest, 60000);
// accept parcels
function acceptparcel(rowId) {
  $.ajax({
    url: "acceptparcel.php",
    method: "post",
    data: { id: rowId },
    success: function () {
      parcelsRequest();
      getParelsById(rowId);
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}

// fetch Ticket
function TicketRequest() {
  $.ajax({
    url: route('fetch-last-tickets'),
    method: "post",
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    success: function (response) {
      $("#indexTicketBody").html(response);
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}
TicketRequest();
setInterval(TicketRequest, 60000);
// accept Ticket
function acceptticket(rowId) {
  $.ajax({
    url: "acceptticket.php",
    method: "post",
    data: { id: rowId },
    success: function () {
      TicketRequest();
      getTicketById(rowId);
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}

if (window.location.href === "https://system.qwafeltravel.com/drivers.php") {
  function parcelsRequest(filterValue) {
    $.ajax({
      url: "fetch_lastDriversParcels.php",
      method: "post",
      data: { filter: filterValue },
      success: function (response) {
        $("#indexDriversParcelsBody").html(response);
      },
      error: function (xhr, status, error) {
        console.log("AJAX request error:", error);
      },
    });
  }
  parcelsRequest("all");
  setInterval(function () {
    let selectedFilter = document.querySelector(
      'input[name="filterHome"]:checked'
    ).value;
    parcelsRequest(selectedFilter);
  }, 60000);
}
// filter
function handleFilterChangeDrivers(selectedValue) {
  let selectedFilter = selectedValue.value;
  parcelsRequest(selectedFilter);
}
function handleFilterChange(selectedValue) {
  let input, filter, myTable, tr, td, i, txtValue;
  input = document.getElementById("search");
  filter = selectedValue.value;
  if (filter != "all") {
    input.value = "";
  }
  myTable = document.getElementsByClassName("myTable");
  let myTableArray = Array.from(myTable);
  if (myTable) {
    myTableArray.forEach((ele) => {
      tr = ele.querySelectorAll("tbody tr");
      for (i = 0; i < tr.length; i++) {
        tr[i].style.display = "none";
        td = tr[i].getElementsByTagName("td");
        for (let j = 0; j < td.length; j++) {
          cell = tr[i].getElementsByTagName("td")[j];
          if (cell) {
            if (
              cell.innerHTML.toUpperCase().indexOf(filter) > -1 ||
              filter == "all"
            ) {
              tr[i].style.display = "";
              break;
            }
          }
        }
      }
    });
  }
}
// scroll to top
let scrollTop = document.querySelector(".scroll-to-top");
if (scrollTop) {
  window.onscroll = function () {
    if (this.scrollY >= 500) {
      scrollTop.classList.add("show");
    } else {
      scrollTop.classList.remove("show");
    }
  };
  scrollTop.onclick = function () {
    window.scrollTo({
      top: 0,
    });
  };
}
function reportTicket() {
  window.location.replace("admin/reportsTicekts.php");
}
function reportParcels() {
  window.location.replace("admin/reportsParcels.php");
}
function reportDriverParcels() {
  window.location.replace("admin/reportsdDrivers.php");
}
function pricePrint() {
  const checkbox = document.getElementById("toggle-checkbox");
  let costparcel = document.getElementById("costparcel");
  let costRestparcel = document.getElementById("costRestparcel");
  if (checkbox.checked) {
    costparcel.classList.remove("noPrint");
    costRestparcel.classList.remove("noPrint");
  } else {
    costparcel.classList.add("noPrint");
    costRestparcel.classList.add("noPrint");
  }
}
