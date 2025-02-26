$(function() {
  var findClosestGroupWrapper = function (field) {
    return field.$element.closest('div[class^="col"], div[class*=" col"]');
  };

  // Initialise the select 2
  $("#status, #cancel_approved_by, #emp_id").select2();

  var pslyForm = $("#add-cancelation-form").parsley({
    errorClass: "is-invalid",
    successClass: "is-valid",
    errorsWrapper: '<ul class="errors-list"></ul>',
    classHandler: findClosestGroupWrapper,
    errorsContainer: findClosestGroupWrapper,
  });

  pslyForm.$element.on("reset", function () {
    pslyForm.reset();
    $("#emp_id, #status, #cancel_approved_by")
      .val("")
      .trigger("change.select2");
    $("#cancel_requested_on, #cancel_leaving_on").datepicker("update", "");
  });

  // Handle the submission
  pslyForm.on("form:submit", function () {
    var form = this.element;
    var formData = new FormData(form);

    Swal.fire({
      title: "Are you sure?",
      text:
        "Are you sure to cancel this Employee...!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, I Confirm!",
    }).then(function (result) {
      if (result.value) {
        setBusyState();
        $.ajax({
          url: form.action,
          method: form.method,
          data: formData,
          processData: false,
          contentType: false,
          dataType: "json",
        })
          .done(function (res) {
            if (res.status && res.status == 201) {
              toastr.success("Cancelation Added Successfully");
              // form.reset();
              setTimeout(function() {
                window.location.reload();
              }, 1000);
            } else {
              toastr.error("Something went wrong!");
            }
          })
          .fail(function () {
            toastr.error(
              "Somthing went wrong. Please try again or contact the administrator"
            );
          })
          .always(function () {
            setBusyState(false);
          });
      }
    });
    return false;
  });
})