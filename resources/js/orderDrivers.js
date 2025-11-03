let parcel = document.getElementById("parcel");
const form = document.getElementById("tabsDriver-content");
let tabsHead = document.querySelectorAll(".tabs ul li");

let steps = form.querySelectorAll(".tab");
function printParcel(parcelId) {
  $.ajax({
    url: "fetch_parcelDrivers.php",
    method: "post",
    data: { query: parcelId },
    success: function (res) {
      response = JSON.parse(res);
      let parcelid = document.getElementById("parcelid");
      parcelid.value = response["parcelNumber"];

      let datePrint = document.getElementById("datePrint");
      datePrint.value = response["parcelDate"];

      let nameSTPrint = document.getElementById("nameSTPrint");
      nameSTPrint.value = response["driverName"];

      let addressSTPrint = document.getElementById("addressSTPrint");
      addressSTPrint.value = response["officeName"];

      let phoneSTPrint = document.getElementById("phoneSTPrint");
      phoneSTPrint.value = response["driverNumber"];

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
        "  اجرة السائق : " + parseInt(response["cost"]) + " " + currency;

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
        costreceiptparcel.textContent = "غير واصل";
      } else {
        costreceiptparcel.textContent = "واصل";
      }
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
  $.ajax({
    url: "fetch_parcelDriverDetails.php",
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
function showParcel() {
  steps.forEach((step) => step.classList.remove("active"));
  parcel.classList.add("active");
  tabsHead.forEach((tab) => tab.classList.remove("active"));
  tabsHead[1].classList.add("active");
}
let printBtn = Array.from(document.getElementsByClassName("printBtnInsert"));
printBtn.forEach((btn) => {
  btn.addEventListener("click", (event) => {
    event.preventDefault();
    window.location.replace("drivers.php");
  });
});
let printBtnEdit = Array.from(document.getElementsByClassName("printBtnEdit"));
printBtnEdit.forEach((btn) => {
  btn.addEventListener("click", (event) => {
    event.preventDefault();
    window.location.replace("admin/reportsdDrivers.php");
  });
});
function home() {
  window.location.replace("drivers.php");
}
// Order Drivers
$("#saveParcelDrivers").submit(function (event) {
  event.preventDefault();
  if (count > 0) {
    // let customerId = JSON.parse(localStorage.getItem("name")).nameId;
    let formData = $("#saveParcelDrivers").serialize();
    // formData += "&customerId=" + encodeURIComponent(customerId);
    $.ajax({
      url: "saveParcelDriver.php",
      type: "post",
      data: formData,
      success: function (response) {
        printParcel(response);
        showParcel();
      },
      error: function (xhr, status, error) {
        console.log("AJAX request error:", error);
      },
    });
  }
});
// Order Drivers Edit
$("#EditParcelDrivers").submit(function (event) {
  event.preventDefault();
  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get("id");
  if (count > 0) {
  }
});
$("#EditParcelDrivers").submit(function (event) {
  event.preventDefault();

  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get("id");

  const formData = new FormData(this);
  formData.append("id", id);

  $.ajax({
    url: "update_ParcelDriver.php",
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
