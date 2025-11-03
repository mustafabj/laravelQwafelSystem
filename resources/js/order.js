const container = document.getElementById("packagesDet");
const packagequnt = document.getElementById("packagequnt");
if (packagequnt) {
  const select = packagequnt.getElementsByTagName("option");
}
function deletepkg(btn, event) {
  event.preventDefault();
  let element = btn.parentNode;
  container.removeChild(element);
  count--;
  if (count == 0 && parcelSave) {
    parcelSave.style.display = "none";
  }
  updatepkg();
  updatenumm();
}
function updatepkg() {
  const backnum = document.querySelectorAll(".backnum");
  const qun = document.querySelectorAll(".qun");
  const desc = document.querySelectorAll(".desc");
  for (let i = 0; i < backnum.length; i++) {
    qun[i].id = "qun" + (i + 1);
    desc[i].id = "desc" + (i + 1);
    backnum[i].innerHTML = `الصنف ${i + 1}`;
  }
  for (let i = 0; i < select.length; i++) {
    select[i].removeAttribute("selected");
  }
}
function updatenumm() {
  for (let i = 0; i < select.length; i++) {
    if (select[i].value == count) {
      packagequnt.selectedIndex = i;
      break;
    }
  }
}
let printBtn = Array.from(document.getElementsByClassName("printBtn"));
printBtn.forEach((btn) => {
  btn.addEventListener("click", (event) => {
    event.preventDefault();
    window.location.replace("index.php");
  });
});

let printEditParcel = Array.from(
  document.getElementsByClassName("printEditParcel")
);
printEditParcel.forEach((btn) => {
  btn.addEventListener("click", (event) => {
    event.preventDefault();
    window.location.replace("admin/reportsParcels.php");
  });
});

let printEditTickts = Array.from(
  document.getElementsByClassName("printEditTickts")
);
printEditTickts.forEach((btn) => {
  btn.addEventListener("click", (event) => {
    event.preventDefault();
    window.location.replace("admin/reportsTicekts.php");
  });
});
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth() + 1;
var yyyy = today.getFullYear();

if (dd < 10) {
  dd = "0" + dd;
}

if (mm < 10) {
  mm = "0" + mm;
}

var todayFormatted = yyyy + "-" + mm + "-" + dd;

let date = document.getElementById("datact");
// min data
var today = new Date().toISOString().split("T")[0];

if (date) {
  date.setAttribute("min", today);
  date.value = todayFormatted;
}

var now = new Date();
var hours = now.getHours();
var minutes = now.getMinutes();

if (hours < 10) {
  hours = "0" + hours;
}

if (minutes < 10) {
  minutes = "0" + minutes;
}

var timeFormatted = hours + ":" + minutes;
let time = document.getElementById("timect");
if (time) {
  time.value = timeFormatted;
}

// step
const form = document.getElementById("tabs-content");
let steps;
let tabsHead;
let nextBtns;
let prevBtns;
if (form) {
  steps = form.querySelectorAll(".tab");
  tabsHead = document.querySelectorAll(".tabs ul li");
  nextBtns = form.querySelectorAll(".next-step");
  prevBtns = form.querySelectorAll(".pre-step");
}

let currentStep = 0;

function showStep(step) {
  steps.forEach((step) => step.classList.remove("active"));
  steps[currentStep].classList.add("active");
}
function tabshead(tab) {
  tabsHead.forEach((tab) => tab.classList.remove("active"));
  tabsHead[currentStep].classList.add("active");
}
function saveData() {
  const data = steps[currentStep].querySelector("input");
  localStorage.setItem(`step-${currentStep}-data`, input.value);
}

function nextStep() {
  // saveData();
  currentStep++;
  if (currentStep >= steps.length) {
    // showSummary();
  } else {
    showStep(currentStep);
    tabshead(currentStep);
    // loadData();
  }
}
function prevStep(event) {
  if (currentStep == 0) {
    window.location.href = "index.php";
  } else {
    // saveData();
    currentStep--;
    showStep(currentStep);
    tabshead(currentStep);
    // loadData();
  }
}
// getName
let counter = 1;
function tableNameClick(event, select) {
  if (event.target.tagName == "TD") {
    const name = select.querySelector(".name").textContent.trim();
    const Passport = select.querySelector(".cusPassport").textContent.trim();
    const nameId = select.getAttribute("data-id");
    const data = { nameId: nameId, name: name, passport: Passport };
    let number = document.getElementById("number");
    localStorage.setItem("name", JSON.stringify(data));
    number.innerHTML = "";
    counter = 1;
    showPhone(nameId);
  }
}

let customer = document.getElementsByClassName("customer")[0];
function showName() {
  steps.forEach((step) => step.classList.remove("active"));
  customer.classList.add("active");
  currentStep++;
  tabshead(currentStep);
}

// get Phone Number
function tablePhoneClick(select) {
  let container = select.parentElement.parentElement;
  let input = container.querySelector(".phoneNumber");
  let ArrayOfNumbers = ["", "", "", ""];
  let customerId = JSON.parse(localStorage.getItem("name")).nameId;
  if (input.value.length > 1) {
    for (let i = 1; i < 5; i++) {
      if (document.getElementById("number" + i)) {
        ArrayOfNumbers[i - 1] = document.getElementById("number" + i).value;
      }
    }
    $.ajax({
      url: "updatePhones.php",
      type: "post",
      data: { query: ArrayOfNumbers, customerId: customerId },
      success: function (response) {
        const number = input.value;
        const numberId = input.getAttribute("data-id");
        const data = { numberId: numberId, number: number };
        localStorage.setItem("number", JSON.stringify(data));
        showAddress();
        refreshaddress();
      },
      error: function (xhr, status, error) {
        console.log("AJAX request error:", error);
      },
    });
  }
}
// Number
function appendNumber(numb) {
  let number = document.getElementById("number");
  let addnumber = document.createElement("div");
  let div = document.createElement("div");
  addnumber.classList.add("addnumber");
  let phonebutton = document.createElement("div");
  phonebutton.classList.add("phonebutton");
  let selectButton = document.createElement("button");
  selectButton.innerText = "تحديد الهاتف";
  let delteButton = document.createElement("button");
  selectButton.setAttribute("onclick", "tablePhoneClick(this)");
  delteButton.innerText = "حذف الهاتف";
  delteButton.setAttribute("onclick", "deleteNumber(this)");
  delteButton.classList.add("delete");
  phonebutton.appendChild(selectButton);
  phonebutton.appendChild(delteButton);

  let phonenumber = numb != -9568741325 ? numb : "";

  div.innerHTML = `
  <label for="number${counter}">رقم الهاتف ${counter}</label>
  <input type="text"
  id="number${counter}"
  data-id="${counter}"
  class="phoneNumber"
  value="${phonenumber}"
  onkeyup="validateInput(this)"
  required="required"
  />

  `;
  addnumber.appendChild(div);

  addnumber.appendChild(phonebutton);
  number.appendChild(addnumber);
}

let phoneCall = document.getElementsByClassName("phone")[0];
function showPhone(customerId) {
  $.ajax({
    url: "fetch_customerWithId.php",
    method: "post",
    data: { query: customerId },
    dataType: "json",
    success: function (response) {
      document.getElementById("fname").value = response["FName"];
      document.getElementById("lname").value = response["LName"];
      const phones = [
        response["phone1"],
        response["phone2"],
        response["phone3"],
        response["phone4"],
      ];
      phones.forEach((e) => {
        if (e != "" && e != null) {
          appendNumber(e);
          counter++;
        }
      });
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });

  steps.forEach((step) => step.classList.remove("active"));
  phoneCall.classList.add("active");
  currentStep++;
  tabshead(currentStep);
} // more
let more = document.getElementById("more");
let number = document.getElementById("number");
if (more) {
  more.addEventListener("click", function (e) {
    e.preventDefault();
    if (counter < 5) {
      appendNumber(-9568741325);
      counter++;
    }
  });
}

let backToCustomer = document.getElementById("backToCustomer");
if (backToCustomer) {
  backToCustomer.addEventListener("click", () => {
    refreshCustomer();
  });
}
// getAddres
const table = document.getElementById("tableAddress");
let rows;
if (table) {
  rows = table.getElementsByTagName("tr");
}

if (table) {
  for (let i = 0; i < rows.length; i++) {
    rows[i].addEventListener("click", function (event) {
      tableAddressClick(event, this);
    });
  }
}

function tableAddressClick(event, row) {
  if (event.target.tagName == "TD") {
    const address = row.querySelector(".addressTd").textContent;
    const addressId = row.getAttribute("data-id");
    const data = { addressId: addressId, address: address };
    localStorage.setItem("address", JSON.stringify(data));
    currentStep++;
    showcust();
  }
}
function officeClick() {
  const data = { addressId: 1, address: "NoAddres" };

  localStorage.setItem("address", JSON.stringify(data));
  showPackages();
}

let address = document.getElementsByClassName("address")[0];
function showAddress() {
  steps.forEach((step) => step.classList.remove("active"));
  address.classList.add("active");
  currentStep++;
  tabshead(currentStep);
}

function paksClick() {
  $.ajax({
    url: "fetchparcelNumber.php",
    success: function (response) {
      res = JSON.parse(response);
      let parcelNumber =
        res != null &&
        parseInt(res["parcelNumber"]) < 10000 &&
        parseInt(res["parcelNumber"]) > 299
          ? parseInt(res["parcelNumber"]) + 1
          : 300;
      let id = document.getElementById("parcelid");
      id.value = parcelNumber;
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
  const data = { form: "paks" };
  localStorage.setItem("form", JSON.stringify(data));
  showpaks();
}

function custClick() {
  const data = { form: "cust" };
  localStorage.setItem("form", JSON.stringify(data));
  showcust();
}

let packages = document.getElementsByClassName("packages")[0];
function showPackages() {
  steps.forEach((step) => step.classList.remove("active"));
  packages.classList.add("active");
  currentStep++;
  tabshead(currentStep);
}
// get paks
const dateNow = new Date();

let day = dateNow.getDate();
let month = dateNow.getMonth() + 1;
let year = dateNow.getFullYear();

let currentDate = `${day}/${month}/${year}`;
let paks = document.getElementsByClassName("paks")[0];

function showpaks() {
  let nameS = document.getElementById("nameS");
  let name = JSON.parse(localStorage.getItem("name"));
  nameS.value = name["name"];
  let phoneS = document.getElementById("phoneS");
  let number = JSON.parse(localStorage.getItem("number"));
  phoneS.value = number["number"];

  let date = document.getElementById("date");
  date.value = currentDate;

  steps.forEach((step) => step.classList.remove("active"));
  paks.classList.add("active");
  currentStep++;
  tabshead(currentStep);
}

let cust = document.getElementsByClassName("cust")[0];
function showcust() {
  $.ajax({
    url: "fetchtecketNumber.php",
    success: function (response) {
      res = JSON.parse(response);
      let ticketNumber =
        res != null &&
        parseInt(res["tecketNumber"]) < 10000 &&
        parseInt(res["tecketNumber"]) > 299
          ? parseInt(res["tecketNumber"]) + 1
          : 300;
      let id = document.getElementById("ticketId");
      id.value = ticketNumber;
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });

  let namec = document.getElementById("namec");
  let name = JSON.parse(localStorage.getItem("name"));
  namec.value = name["name"];

  let phonec = document.getElementById("phonec");
  let number = JSON.parse(localStorage.getItem("number"));
  phonec.value = number["number"];

  let namecp = document.getElementById("namecp");
  let passport = JSON.parse(localStorage.getItem("name"));
  namecp.value = passport["passport"];

  let addressCust = document.getElementById("addressCust");
  let address = JSON.parse(localStorage.getItem("address"));
  addressCust.value = address["address"].trim();

  let datec = document.getElementById("datec");
  datec.value = currentDate;

  steps.forEach((step) => step.classList.remove("active"));
  cust.classList.add("active");
  currentStep++;

  tabshead(currentStep);
}

function custBack() {
  if (
    localStorage.getItem("address") !== null &&
    JSON.parse(localStorage.getItem("address")).address == "NoAddres"
  ) {
    steps.forEach((step) => step.classList.remove("active"));
    packages.classList.add("active");
    currentStep--;
    tabshead(currentStep);
  } else {
    steps.forEach((step) => step.classList.remove("active"));
    address.classList.add("active");
    currentStep -= 2;
    tabshead(currentStep);
  }
}
// show ticket
let ticket = document.getElementById("ticket");
function showTicket() {
  steps.forEach((step) => step.classList.remove("active"));
  ticket.classList.add("active");
  currentStep++;
  tabshead(currentStep);
}
// show parcel
let parcel = document.getElementById("parcel");
function showParcel() {
  steps.forEach((step) => step.classList.remove("active"));
  parcel.classList.add("active");
  currentStep++;
  tabshead(currentStep);
}
//show print cust
function printTicket(TicketId) {
  $.ajax({
    url: "fetch_ticket.php",
    method: "post",
    data: { query: TicketId },
    dataType: "json",
    success: function (response) {
      let idticketPrint = document.getElementById("idticketPrint");
      idticketPrint.value = response["tecketNumber"];

      let datetickePrintt = document.getElementById("datetickePrintt");
      datetickePrintt.value = response["ticketDate"];

      let nameticketPrint = document.getElementById("nameticketPrint");
      let fullName = response["FName"] + " " + response["LName"];
      nameticketPrint.value = fullName.trim();

      let phoneticketPrint = document.getElementById("phoneticketPrint");
      phoneticketPrint.value = response["custNumber"];

      let currency;
      const currencies = {
        JD: "دينار ",
        USD: "دولار أمريكي",
        IQD: "دينار عراقي",
        SYP: "ليرة سورية",
        SAR: "ريال سعودي",
      };

      if (currencies.hasOwnProperty(response.currency)) {
        currency = currencies[response.currency];
      } else {
        currency = response.currency;
      }

      let costTicketPrint = document.getElementById("costTicketPrint");
      costTicketPrint.value = response["cost"] + " " + currency;

      let paidticketPrint = document.getElementById("paidticketPrint");
      let paid =
        response["paid"] == "unpaid" ? response["costRest"] : response["cost"];
      paidticketPrint.value = paid + " " + currency;

      let UnpaidTicketPrint = document.getElementById("UnpaidTicketPrint");
      let cost = response["cost"] - paid;

      UnpaidTicketPrint.value = cost + " " + currency;

      let TrancustToPrint = document.getElementById("TrancustToPrint");
      TrancustToPrint.value = response["destination"];
      let costreceiptparcel;
      if (response["paid"] == "unpaid") {
        costreceiptparcel = "غير مدفوع";
      } else {
        costreceiptparcel = "مدفوع";
      }
      // let paidNotPaid = document.getElementById("paidNotPaid");
      // paidNotPaid.value = costreceiptparcel;

      let datactPrint = document.getElementById("datactPrint");
      datactPrint.value = response["travelDate"];

      let passportPrint = document.getElementById("passportPrint");
      passportPrint.value = response["customerPassport"];

      let timectPrint = document.getElementById("timectPrint");
      timectPrint.value = response["travelTime"];

      let custbnPrint = document.getElementById("custbnPrint");
      custbnPrint.value = response["Seat"];
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}
//show print parcel
function printParcel(parcelId) {
  $.ajax({
    url: "fetch_parcel.php",
    method: "post",
    data: { query: parcelId },
    success: function (res) {
      response = JSON.parse(res);

      let pksidPrint = document.getElementById("pksidPrint");
      pksidPrint.value = response["parcelNumber"];

      let datePrint = document.getElementById("datePrint");
      datePrint.value = response["parcelDate"];

      let nameSPrint = document.getElementById("nameSPrint");
      let fullName = response["FName"] + " " + response["LName"];
      nameSPrint.value = fullName.trim();

      let phoneSPrint = document.getElementById("phoneSPrint");
      phoneSPrint.value = response["custNumber"];

      let nameSTPrint = document.getElementById("nameSTPrint");
      nameSTPrint.value = response["recipientName"];

      let addressSTPrint = document.getElementById("addressSTPrint");
      addressSTPrint.value = response["sendTo"];

      let phoneSTPrint = document.getElementById("phoneSTPrint");
      phoneSTPrint.value = response["recipientNumber"];

      let officeSTPrint = document.getElementById("officeSTPrint");
      officeSTPrint.value = response["officeName"];

      let currency;
      const currencies = {
        JD: "دينار ",
        USD: "دولار أمريكي",
        IQD: "دينار عراقي",
        SYP: "ليرة سورية",
        SAR: "ريال سعودي",
      };

      if (currencies.hasOwnProperty(response.currency)) {
        currency = currencies[response.currency];
      } else {
        currency = response.currency;
      }

      let costparcel = document.getElementById("costparcel");
      costparcel.textContent =
        " رسوم الشحن : " + parseInt(response["cost"]) + " " + currency;

      if (response["paid"] == "unpaid" && parseInt(response["costRest"]) > 0) {
        let costreceiptparcel = document.getElementById("costreceiptparcel");
        let paid =
          response["paid"] == "unpaid"
            ? parseInt(response["costRest"])
            : parseInt(response["cost"]);
        costreceiptparcel.textContent = "واصل " + paid + " " + currency;
        let costRestparcel = document.getElementById("costRestparcel");
        costRestparcel.textContent += "باقي ";
        costRestparcel.textContent +=
          parseInt(response["cost"]) - parseInt(response["costRest"]);
      } else if (
        response["paid"] == "unpaid" &&
        parseInt(response["costRest"]) == 0
      ) {
        costreceiptparcel.textContent = "غير مدفوع";
      } else {
        costreceiptparcel.textContent = "مدفوع";
      }
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
  $.ajax({
    url: "fetch_parceldetails.php",
    method: "post",
    data: { query: parcelId },
    success: function (response) {
      $("#printParcels").prepend(response);
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}

$("#editParcel").submit(function (event) {
  event.preventDefault();

  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get("id");

  const formData = new FormData(this);
  formData.append("id", id);

  $.ajax({
    url: "update_Parcel.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      console.log(response);
      printParcel(id);
      showParcel();
    },
    error: function (xhr, status, error) {
      console.log("Error updating ticket:", error);
    },
  });
});
// Save Ticket
$("#saveTicket").submit(function (event) {
  event.preventDefault();
  let customerId = JSON.parse(localStorage.getItem("name")).nameId;
  let addressId = JSON.parse(localStorage.getItem("address")).addressId;
  let formData = $("#saveTicket").serialize();
  formData += "&customerId=" + encodeURIComponent(customerId);
  formData += "&addressId=" + encodeURIComponent(addressId);
  $.ajax({
    url: "saveTicket.php",
    type: "post",
    data: formData,
    success: function (response) {
      printTicket(response);
      showTicket();
    },
  });
});

$("#editTicket").submit(function (event) {
  event.preventDefault();

  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get("id");

  const formData = new FormData(this);
  formData.append("id", id);

  $.ajax({
    url: "update_ticket.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      printTicket(id);
      showTicket();
    },
    error: function (xhr, status, error) {
      console.log("Error updating ticket:", error);
    },
  });
});

// Save parcel
$("#saveParcel").submit(function (event) {
  event.preventDefault();
  if (count > 0) {
    let customerId = JSON.parse(localStorage.getItem("name")).nameId;
    let formData = $("#saveParcel").serialize();
    formData += "&customerId=" + encodeURIComponent(customerId);
    $.ajax({
      url: "saveParcel.php",
      type: "post",
      data: formData,
      success: function (response) {
        printParcel(response);
        showParcel();
      },
    });
  }
});
$(document).ready(function() {
  $('#nameSPrinttt').select2({
      ajax: {
          url: 'get_customers.php',
          dataType: 'json',
          delay: 250,
          data: function(params) {
              return {
                  search: params.term
              };
          },
          processResults: function(data) {
              return {
                  results: data
              };
          },
          cache: true,
         
      },
      minimumInputLength: 2,
       dir: "rtl",
      placeholder: 'Search for a customer',
      language: {
          inputTooShort: function() {
              return 'الرجاء إدخال حرفين على الأقل';
          },
          noResults: function() {
              return 'لم يتم العثور على عملاء';
          },
          searching: function() {
              return 'جاري البحث...';
          }
      }
  });

  $('#nameSPrinttt').on('select2:select', function(e) {
      var customerId = e.params.data.id;
      $.ajax({
          url: 'get_customer_phones.php',
          dataType: 'json',
          data: {
              customerId: customerId
          },
          success: function(data) {
              var phoneSelect = $('#phoneSPrinttt');
              phoneSelect.empty();
              $.each(data, function(index, phone) {
                  phoneSelect.append(new Option(phone, phone));
              });
          }
      });
  });
});