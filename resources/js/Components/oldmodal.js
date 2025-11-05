
let modal = document.getElementById("myModal");
let bodyForm = document.getElementById("bodyForm");
let body = document.getElementsByTagName("body")[0];
let addCustomer = document.getElementById("addCustomer");
let span = document.getElementById("close");
let modelTitle = document.getElementById("modelTitle");

// search customer
function refreshCustomer() {
  let searchQuery = $("#search-customer").val();

  $.ajax({
    url: "fetch_customer.php",
    method: "GET",
    data: { query: searchQuery },
    success: function (response) {
      if (response == "") {
        $("#customerBody").html(
          "<tr><td colspan='10' class='txt-c fs-20'>لا يوجد عميل    </td></tr>"
        );
      } else {
        $("#customerBody").html(response);
      }
    },
    error: function (xhr, status, error) {
      // Handle errors
      console.log("AJAX request error:", error);
    },
  });
}
$(document).ready(function () {
  $("#search-form").submit(function (event) {
    event.preventDefault();
    refreshCustomer();
  });
});

// Add Customer
if (addCustomer) {
  addCustomer.addEventListener("click", function () {
    bodyForm.innerHTML = `<form method="post" id="addCustomerTable">
  <div class="bodyForm bodyAddress">
    <div class="custDetails">
      <h2>معلومات العميل</h2>
      <div class="inputLabel">
        <label for="FName">الاسم الاول :</label>
        <input type="text" name="FName" id="FName"  required="required"/>
      </div>
      <div class="inputLabel">
        <label for="LName">الاسم الثاني :</label>
        <input type="text" name="LName" id="LName"  required="required"/>
      </div>

      <div class="inputLabel">
        <label for="passport">رقم جواز السفر :</label>
        <input
          type="text"
          name="passport"
          id="passport"
   
        />
      </div>
      <div class="inputLabel">
        <label for="custState">حالة العميل :</label>
        <input
          type="text"
          name="custState"
          id="custState"
     
        />
      </div>
      <div class="inputLabel">
      <label for="phoneNumber">رقم الهاتف :</label>
      <input
        type="text"
        name="phoneNumber"
        id="phoneNumber"
        required="required"
      />
    </div>
    </div>
    
    <div class="addressDetails">
      <h2>عنوان العميل</h2>

      <div class="inputLabel">
        <label for="city"> المدينة :</label>
        <input type="text" name="city" id="city"   />
      </div>
      <div class="inputLabel">
        <label for="aria"> المنطقة :</label>
        <input type="text" name="aria" id="aria"  />
      </div>
      <div class="inputLabel">
        <label for="streetName">اسم الشارع :</label>
        <input type="text" name="streetName" id="streetName"  />
      </div>
      <div class="inputLabel">
        <label for="buildingNumber">رقم المبنى :</label>
        <input type="text" name="buildingNumber" id="buildingNumber"  />
      </div>
      <div class="inputLabel">
        <label for="descAddress">معلومات اضافية :</label>
        <textarea
          name="descAddress"
          id="descAddress"
          class="descAddress"
        ></textarea>
      </div>
    </div>
  </div>
  <button class="add">اضافة</button>
</form>`;
    document.getElementById("phoneNumber").value =
      document.getElementById("search-customer").value;

    $("#addCustomerTable").submit(function (e) {
      e.preventDefault();
      $.ajax({
        url: "insertCustomer.php",
        type: "post",
        data: $("#addCustomerTable").serialize(),
        success: function () {
          modal.style.display = "none";
          body.style.overflowY = "unset";
          modelTitle.innerText = "";
          $("#search-form").submit();
        },
      });
    });
    modelTitle.innerText = "اضافة عميل";
    modal.style.display = "block";
    body.style.overflowY = "hidden";
  });
}
// History
let myModalHistory = document.getElementById("myModalHistory");
let tbodyHistory = document.getElementById("tbodyHistory");
let bodyFormHistory = document.getElementById("bodyFormHistory");
let closeHistory = document.getElementById("closeHistory");
let modelTitleHistory = document.getElementById("modelTitleHistory");
function historyBtn(btn) {
  bodyFormHistory.innerHTML = `<div class="history">
  <div class="container">
    <div class="historyTabs">
      <button class="historyTab historyTabActive" data-cont=".historyT">
        الارساليات
      </button>
      <button class="historyTab" data-cont=".historyS">السفريات</button>
    </div>

    <div class="historyT">
      <table class="myTable">
        <thead>
          <tr>
            <td>رقم الارسالية</td>
            <td>اسم العميل</td>
            <td>رقم العميل</td>
            <td>اسم المرسل اليه</td>
            <td>رقم المرسل اليه</td>
            <td>اسم الموظف</td>
            <td>المكتب</td>
            <td>المكتب المرسل اليه</td>
            <td>تاريخ الوصل</td>
            <td>الحالة</td>
          </tr>
        </thead>
        <tbody id="custparcelsBody">
          
        </tbody>
      </table>
    </div>
    <div class="historyS" style="display: none">
      <table class="myTable">
        <thead>
        <tr>
        <td>رقم التذكرة</td>
        <td>اسم العميل</td>
        <td>رقم العميل</td>
        <td>اسم الموظف</td>
        <td>اسم المكتب</td>
        <td>السفر من</td>
        <td>السفر الى</td>
        <td>تاريخ التذكرة</td>
        <td>الحالة</td>
    </tr>
        </thead>
        <tbody id="custTicektBody">
          
        </tbody>
      </table>
    </div>
  </div>
</div>`;
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
  let customerId = btn.parentElement.parentElement.getAttribute("data-id");

  $.ajax({
    url: "customerHistoryTicket.php",
    method: "post",
    data: { query: customerId },

    success: function (response) {
      document.getElementById("custTicektBody").innerHTML = response;
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
  $.ajax({
    url: "customerHistoryParcels.php",
    method: "post",
    data: { query: customerId },

    success: function (response) {
      document.getElementById("custparcelsBody").innerHTML = response;
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
  modelTitleHistory.innerText = "سجل العميل";
  myModalHistory.style.display = "block";
  body.style.overflowY = "hidden";
}

// Edit Customer
function editBtn(btn) {
  bodyForm.innerHTML = `<form method="post" id="editCustomerTable">
  <div class="bodyForm bodyAddress">
    <div class="custDetails">
      <h2>معلومات العميل</h2>
      <div class="inputLabel">
        <label for="FNameEdit" required="required">الاسم الاول :</label>
        <input type="text" name="FNameEdit" id="FNameEdit"  />
      </div>
      <div class="inputLabel">
        <label for="LNameEdit">الاسم الثاني :</label>
        <input type="text" name="LNameEdit" id="LNameEdit"  required="required" />
      </div>
      <div class="inputLabel">
      <label for="passportEdit">رقم جواز السفر :</label>
      <input
        type="text"
        name="passportEdit"
        id="passportEdit"
        
      />
    </div>
      <div class="inputLabel">
      <label for="stateEdit"> حالة العميل  :</label>
      <input
        type="text"
        name="stateEdit"
        id="stateEdit"
       
      />
    </div>
    </div>
    
  </div>
  <button class="add" id="editCustomer">تعديل</button>
</form>`;
  let customerId = btn.parentElement.parentElement.getAttribute("data-id");

  $.ajax({
    url: "fetch_customerWithId.php",
    method: "post",
    data: { query: customerId },
    dataType: "json",
    success: function (response) {
      document.getElementById("FNameEdit").value = response["FName"];
      document.getElementById("LNameEdit").value = response["LName"];
      document.getElementById("passportEdit").value =
        response["customerPassport"];
      document.getElementById("stateEdit").value = response["customerState"];
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });

  modelTitle.innerText = "تعديل معلومات العميل";
  modal.style.display = "block";
  body.style.overflowY = "hidden";

  $("#editCustomerTable").submit(function (e) {
    e.preventDefault();
    let formData = $("#editCustomerTable").serialize();
    formData += "&customerId=" + encodeURIComponent(customerId);
    $.ajax({
      url: "updateCustomer.php",
      type: "post",
      data: formData,
      success: function () {
        modal.style.display = "none";
        body.style.overflowY = "unset";
        modelTitle.innerText = "";
        $("#search-form").submit();
      },
      error: function (xhr, status, error) {
        console.log("AJAX request error:", error);
      },
    });
  });
}

// get address

function refreshaddress() {
  let customerId = JSON.parse(localStorage.getItem("name")).nameId;
  $.ajax({
    url: "fetch_address.php",
    method: "GET",
    data: { customerId: customerId },
    success: function (response) {
      $("#addressBody").html(response);
    },
    error: function (xhr, status, error) {
      // Handle errors
      console.log("AJAX request error:", error);
    },
  });
}
$(document).ready(function () {
  $("#search-form").submit(function (event) {
    event.preventDefault();
    refreshCustomer();
  });
});

// Add Address

let addAddress = document.getElementById("addAddress");
if (addAddress) {
  addAddress.addEventListener("click", function () {
    bodyForm.innerHTML = `<form method="post" id="addaddressTable">
  <div class="bodyForm bodyAddress">
    <div class="addressDetails">
      <h2>عنوان العميل</h2>

      <div class="inputLabel">
        <label for="city"> المدينة :</label>
        <input type="text" name="city" id="city" required="required" />
      </div>
      <div class="inputLabel">
        <label for="aria"> المنطقة :</label>
        <input type="text" name="aria" id="aria"  />
      </div>
      <div class="inputLabel">
        <label for="streetName">اسم الشارع :</label>
        <input type="text" name="streetName" id="streetName" 
         />
      </div>
      <div class="inputLabel">
        <label for="buildingNumber">رقم المبنى  :</label>
        <input type="text" name="buildingNumber" id="buildingNumber" 
         />
      </div>
      <div class="inputLabel">
        <label for="descAddress">معلومات اضافية :</label>
        <textarea
          name="descAddress"
          id="descAddress"
          class="descAddress"
        ></textarea>
      </div>
    </div>
  </div>
  <button class="add">اضافة</button>
</form>`;
    let customerId = JSON.parse(localStorage.getItem("name")).nameId;

    $("#addaddressTable").submit(function (e) {
      e.preventDefault();
      let formData = $("#addaddressTable").serialize();
      formData += "&customerId=" + customerId;
      $.ajax({
        url: "insertAddress.php",
        type: "post",
        data: formData,
        success: function () {
          modal.style.display = "none";
          body.style.overflowY = "unset";
          modelTitle.innerText = "";
          refreshaddress();
        },
      });
    });
    modelTitle.innerText = "اضافة عنوان";
    modal.style.display = "block";
    body.style.overflowY = "hidden";
  });
}
// Edit Address
function editAddressBtn(btn) {
  bodyForm.innerHTML = `<form method="post" id="editAddressform">
    <div class="bodyForm bodyAddress">
      <div class="addressDetails">
        <h2> عنوان العميل</h2>
  
        <div class="inputLabel">
          <label for="cityEdit"> المدينة :</label>
          <input type="text" name="cityEdit" id="cityEdit" required="required"  />
        </div>
        <div class="inputLabel">
          <label for="ariaEdit"> المنطقة :</label>
          <input type="text" name="ariaEdit" id="ariaEdit" />
        </div>
        <div class="inputLabel">
          <label for="streetName">اسم الشارع :</label>
          <input type="text" name="streetNameEdit" id="streetNameEdit"  />
        </div>
        <div class="inputLabel">
          <label for="buildingNumberEdit">رقم المبنى  :</label>
          <input type="text" name="buildingNumberEdit" id="buildingNumberEdit"  />
        </div>
        <div class="inputLabel">
          <label for="descAddressEdit">معلومات اضافية :</label>
          <textarea
            name="descAddressEdit"
            id="descAddressEdit"
            class="descAddressEdit"
          ></textarea>
        </div>
      </div>
    </div>
    <button class="add">تعديل</button>
  </form>`;
  let addressId = btn.parentElement.parentElement.getAttribute("data-id");
  $.ajax({
    url: "fetch_addressWithId.php",
    method: "post",
    data: { query: addressId },
    success: function (r) {
      response = JSON.parse(r);
      document.getElementById("cityEdit").value = response["city"];
      document.getElementById("ariaEdit").value = response["area"];
      document.getElementById("streetNameEdit").value = response["street"];
      document.getElementById("buildingNumberEdit").value =
        response["buildingNumber"];
      document.getElementById("descAddressEdit").value = response["info"];
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });

  modelTitle.innerText = "تعديل عنوان العميل";
  modal.style.display = "block";
  body.style.overflowY = "hidden";

  $("#editAddressform").submit(function (e) {
    e.preventDefault();
    let addressId = btn.parentElement.parentElement.getAttribute("data-id");
    let formData = $("#editAddressform").serialize();
    formData += "&addressId=" + encodeURIComponent(addressId);
    $.ajax({
      url: "updateAddress.php",
      type: "post",
      data: formData,
      success: function (response) {
        modal.style.display = "none";
        body.style.overflowY = "unset";
        modelTitle.innerText = "";
        refreshaddress();
      },
      error: function (xhr, status, error) {
        console.log("AJAX request error:", error);
      },
    });
  });
}
//getTicketById
function getTicketById(rowId) {
  // let buttons = document.querySelectorAll("#indexTicketBody tr td");
  // console.log(1);
  // let rowId = button.parentNode.getAttribute("data-id");

  $.ajax({
    url: "fetchTicketById.php",
    type: "post",
    data: { id: rowId },
    success: function (response) {
      $("#bodyForm").html(response);
      // console.log(response);
      modal.style.display = "block";
      body.style.overflowY = "hidden";
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}

// /getParelsById
function getParelsById(rowId) {
  App.utils.showToast("Loader is init");

  $.ajax({
    url: "fetchParelsById.php",
    type: "post",
    data: { id: rowId },
    success: function (response) {
      $("#bodyForm").html(response);
      // console.log(response);
      modal.style.display = "block";
      body.style.overflowY = "hidden";
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}
// getParelsDriverById
function getParelsDriverById(rowId) {
  $.ajax({
    url: "fetchParelsDriverById.php",
    type: "post",
    data: { id: rowId },
    success: function (response) {
      $("#bodyForm").html(response);
      // console.log(response);
      modal.style.display = "block";
      body.style.overflowY = "hidden";
    },
    error: function (xhr, status, error) {
      console.log("AJAX request error:", error);
    },
  });
}

// Close Model
span.onclick = function () {
  modal.style.display = "none";
  body.style.overflowY = "unset";
  modelTitle.innerText = "";
  bodyForm.innerHTML = "";
};
if (closeHistory) {
  closeHistory.onclick = function () {
    myModalHistory.style.display = "none";
    body.style.overflowY = "unset";
    modelTitleHistory.innerText = "";
    bodyFormHistory.innerHTML = "";
  };
}
window.onclick = function (event) {
  if (event.target == modal) {
    modal.style.display = "none";
    body.style.overflowY = "unset";
    modelTitle.innerText = "";
    bodyForm.innerHTML = "";
  } else if (event.target == myModalHistory) {
    myModalHistory.style.display = "none";
    body.style.overflowY = "unset";
    modelTitleHistory.innerText = "";
    bodyFormHistory.innerHTML = "";
  }
};
