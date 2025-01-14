/**
 * JavaScript for form editing date conditions.
 *
 * @module moodle-availability_ipaymu-form
 */
M.availability_ipaymu = M.availability_ipaymu || {};

/**
 * @class M.availability_ipaymu.form
 * @extends M.core_availability.plugin
 */
M.availability_ipaymu.form = Y.Object(M.core_availability.plugin);

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} currencies Array of currency_code => localised string
 */
M.availability_ipaymu.form.initInner = function (currencies) {
  this.currencies = currencies;
};

M.availability_ipaymu.form.getNode = function (json) {
  var selected_string = "";
  var currencies_options = "";
  for (var curr in this.currencies) {
    if (json.currency === curr) {
      selected_string = ' selected="selected" ';
    } else {
      selected_string = "";
    }
    currencies_options +=
      '<option value="' + curr + '" ' + selected_string + " >";
    currencies_options += this.currencies[curr];
    currencies_options += "</option>";
  }

  var html = '<div class="container-fluid">';
  html += '<div class="row">';
  html += '<div class="col"><label for="ipaymubusinessemail">';
  html += M.util.get_string("businessemail", "availability_ipaymu");
  html +=
    "</label></div>" +
    '<div class="col"><input class="form-control" name="businessemail" type="email" id="ipaymubusinessemail" />' +
    "</div></div>";

  html += '<div class="row mt-3">';
  html += '<div class="col"><label for="ipaymucurrency">';
  html += M.util.get_string("currency", "availability_ipaymu");
  html +=
    '</label></div><div class="col"><select class="form-control" name="currency" id="ipaymucurrency" />' +
    currencies_options +
    "</select></div></div>";

  html += '<div class="row mt-3">';
  html += '<div class="col"><label for="ipaymucost">';
  html += M.util.get_string("cost", "availability_ipaymu");
  html +=
    '</label></div><div class="col"><input class="form-control" name="cost" type="text" id="ipaymucost" /></div></div>';

  html += '<div class="row mt-3">';
  html += '<div class="col"><label for="ipaymuitemname">';
  html += M.util.get_string("itemname", "availability_ipaymu");
  html +=
    '</label></div><div class="col"><input class="form-control" name="itemname" type="text" /></div></div>';

  html += '<div class="row mt-3">';
  html += '<div class="col"><label for="ipaymuitemnumber">';
  html += M.util.get_string("itemnumber", "availability_ipaymu");
  html +=
    '</label></div><div class="col"><input class="form-control" name="itemnumber" type="text" /></div></div>';
  html += "</div>";

  var node = Y.Node.create("<span>" + html + "</span>");

  // Set initial values based on the value from the JSON data in Moodle
  // database. This will have values undefined if creating a new one.
  if (json.businessemail) {
    node.one("input[name=businessemail]").set("value", json.businessemail);
  }
  if (json.cost) {
    node.one("input[name=cost]").set("value", json.cost);
  }
  if (json.itemname) {
    node.one("input[name=itemname]").set("value", json.itemname);
  }
  if (json.itemnumber) {
    node.one("input[name=itemnumber]").set("value", json.itemnumber);
  }

  // Add event handlers (first time only).
  if (!M.availability_ipaymu.form.addedEvents) {
    M.availability_ipaymu.form.addedEvents = true;

    var root = Y.one(".availability-field");
    root.delegate(
      "change",
      function () {
        M.core_availability.form.update();
      },
      ".availability_ipaymu select[name=currency]"
    );

    root.delegate(
      "change",
      function () {
        // The key point is this update call. This call will update
        // the JSON data in the hidden field in the form, so that it
        // includes the new value of the checkbox.
        M.core_availability.form.update();
      },
      ".availability_ipaymu input"
    );
  }

  return node;
};

M.availability_ipaymu.form.fillValue = function (value, node) {
  // This function gets passed the node (from above) and a value
  // object. Within that object, it must set up the correct values
  // to use within the JSON data in the form. Should be compatible
  // with the structure used in the __construct and save functions
  // within condition.php.
  value.businessemail = node.one("input[name=businessemail]").get("value");

  value.currency = node.one("select[name=currency]").get("value");

  value.cost = this.getValue("cost", node);

  value.itemname = node.one("input[name=itemname]").get("value");

  value.itemnumber = node.one("input[name=itemnumber]").get("value");
};

/**
 * Gets the numeric value of an input field. Supports decimal points (using
 * dot or comma).
 *
 * @method getValue
 * @return {Number|String} Value of field as number or string if not valid
 */
M.availability_ipaymu.form.getValue = function (field, node) {
  // Get field value.
  var value = node.one("input[name=" + field + "]").get("value");

  // If it is not a valid positive number, return false.
  if (!/^[0-9]+([.,][0-9]+)?$/.test(value)) {
    return value;
  }

  // Replace comma with dot and parse as floating-point.
  var result = parseFloat(value.replace(",", "."));
  return result;
};

M.availability_ipaymu.form.fillErrors = function (errors, node) {
  var value = {};
  this.fillValue(value, node);

  if (value.businessemail === "") {
    errors.push("availability_ipaymu:error_businessemail");
  }
  if (
    (value.cost !== undefined && typeof value.cost === "string") ||
    value.cost <= 0
  ) {
    errors.push("availability_ipaymu:error_cost");
  }
  if (value.itemname === "") {
    errors.push("availability_ipaymu:error_itemname");
  }
  if (value.itemnumber === "") {
    errors.push("availability_ipaymu:error_itemnumber");
  }
};
