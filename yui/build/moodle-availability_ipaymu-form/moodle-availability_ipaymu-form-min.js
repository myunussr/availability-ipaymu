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
          t =
            (t +=
              '<option value="' +
              i +
              '" ' +
              (a.currency === i ? ' selected="selected" ' : "") +
              " >") +
            this.currencies[i] +
            "</option>";
        return (
          (e =
            (e =
              (e =
                (e =
                  (e =
                    (e =
                      (e =
                        (e =
                          (e =
                            (e =
                              (e = '<div class="container-fluid">') +
                              '<div class="row">' +
                              '<div class="col"><label for="ipaymubusinessemail">') +
                            M.util.get_string(
                              "businessemail",
                              "availability_ipaymu"
                            ) +
                            '</label></div><div class="col"><input class="form-control" name="businessemail" type="email" id="ipaymubusinessemail" /></div></div>') +
                          '<div class="row mt-3">' +
                          '<div class="col"><label for="ipaymucurrency">') +
                        M.util.get_string("currency", "availability_ipaymu") +
                        ('</label></div><div class="col"><select class="form-control" name="currency" id="ipaymucurrency" />' +
                          t +
                          "</select></div></div>")) +
                      '<div class="row mt-3">' +
                      '<div class="col"><label for="ipaymucost">') +
                    M.util.get_string("cost", "availability_ipaymu") +
                    '</label></div><div class="col"><input class="form-control" name="cost" type="text" id="ipaymucost" /></div></div>') +
                  '<div class="row mt-3">' +
                  '<div class="col"><label for="ipaymuitemname">') +
                M.util.get_string("itemname", "availability_ipaymu") +
                '</label></div><div class="col"><input class="form-control" name="itemname" type="text" /></div></div>') +
              '<div class="row mt-3">' +
              '<div class="col"><label for="ipaymuitemnumber">') +
            M.util.get_string("itemnumber", "availability_ipaymu")),
          (e = n.Node.create(
            "<span>" +
              (e =
                e +
                '</label></div><div class="col"><input class="form-control" name="itemnumber" type="text" /></div></div>' +
                "</div>") +
              "</span>"
          )),
          a.businessemail &&
            e.one("input[name=businessemail]").set("value", a.businessemail),
          a.cost && e.one("input[name=cost]").set("value", a.cost),
          a.itemname && e.one("input[name=itemname]").set("value", a.itemname),
          a.itemnumber &&
            e.one("input[name=itemnumber]").set("value", a.itemnumber),
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
            )),
          e
        );
      }),
      (M.availability_ipaymu.form.fillValue = function (a, i) {
        (a.businessemail = i.one("input[name=businessemail]").get("value")),
          (a.currency = i.one("select[name=currency]").get("value")),
          (a.cost = this.getValue("cost", i)),
          (a.itemname = i.one("input[name=itemname]").get("value")),
          (a.itemnumber = i.one("input[name=itemnumber]").get("value"));
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
          "" === e.businessemail &&
            a.push("availability_ipaymu:error_businessemail"),
          ((e.cost !== undefined && "string" == typeof e.cost) ||
            e.cost <= 0) &&
            a.push("availability_ipaymu:error_cost"),
          "" === e.itemname && a.push("availability_ipaymu:error_itemname"),
          "" === e.itemnumber && a.push("availability_ipaymu:error_itemnumber");
      });
  },
  "@VERSION@",
  { requires: ["base", "node", "event", "moodle-core_availability-form"] }
);
