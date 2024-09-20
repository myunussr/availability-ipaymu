YUI.add(
  "moodle-availability_ipaymu-form",
  function (n, a) {
    (M.availability_ipaymu = M.availability_ipaymu || {}),
      (M.availability_ipaymu.form = n.Object(M.core_availability.plugin)),
      (M.availability_ipaymu.form.initInner = function (a) {
        this.currencies = a;
      }),
      (M.availability_ipaymu.form.getNode = function (a) {
        var i,
          e,
          l,
          t = "";
        for (i in this.currencies)
          t +=
            '<option value="' +
            i +
            '" ' +
            (a.currency === i ? ' selected="selected" ' : "") +
            " >" +
            this.currencies[i] +
            "</option>";

        e =
          '<div class="container-fluid">' +
          '<div class="row">' +
          '<div class="col"><label for="ipaymucurrency">' +
          M.util.get_string("currency", "availability_ipaymu") +
          '</label></div><div class="col"><select class="form-control" name="currency" id="ipaymucurrency">' +
          t +
          "</select></div></div>" +
          '<div class="row mt-3">' +
          '<div class="col"><label for="ipaymucost">' +
          M.util.get_string("cost", "availability_ipaymu") +
          '</label></div><div class="col"><input class="form-control" name="cost" type="text" id="ipaymucost" /></div></div>' +
          '<div class="row mt-3">' +
          '<div class="col"><label for="ipaymuitemname">' +
          M.util.get_string("itemname", "availability_ipaymu") +
          '</label></div><div class="col"><input class="form-control" name="itemname" type="text" /></div></div>' +
          "</div>";

        e = n.Node.create("<span>" + e + "</span>");

        a.cost && e.one("input[name=cost]").set("value", a.cost);
        a.itemname && e.one("input[name=itemname]").set("value", a.itemname);

        M.availability_ipaymu.form.addedEvents ||
          ((M.availability_ipaymu.form.addedEvents = !0),
          (l = n.one(".availability-field")).delegate(
            "change",
            function () {
              M.core_availability.form.update();
            },
            ".availability_ipaymu select[name=currency]"
          ),
          l.delegate(
            "change",
            function () {
              M.core_availability.form.update();
            },
            ".availability_ipaymu input"
          ));

        return e;
      }),
      (M.availability_ipaymu.form.fillValue = function (a, i) {
        (a.currency = i.one("select[name=currency]").get("value")),
          (a.cost = this.getValue("cost", i)),
          (a.itemname = i.one("input[name=itemname]").get("value"));
      }),
      (M.availability_ipaymu.form.getValue = function (a, i) {
        a = i.one("input[name=" + a + "]").get("value");
        return /^[0-9]+([.,][0-9]+)?$/.test(a)
          ? parseFloat(a.replace(",", "."))
          : a;
      }),
      (M.availability_ipaymu.form.fillErrors = function (a, i) {
        var e = {};
        this.fillValue(e, i),
          ((e.cost !== undefined && "string" == typeof e.cost) ||
            e.cost <= 0) &&
            a.push("availability_ipaymu:error_cost"),
          "" === e.itemname && a.push("availability_ipaymu:error_itemname");
      });
  },
  "@VERSION@",
  { requires: ["base", "node", "event", "moodle-core_availability-form"] }
);
