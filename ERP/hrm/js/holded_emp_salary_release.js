$(function () {
  var findClosestGroupWrapper = function (field) {
    return field.$element.closest('div[class^="col"], div[class*=" col"]');
  };

    // Adds Custom Validator Date After
    window.Parsley.addValidator('dateAfter', {
        messages: {en: 'The date should not be less than %s'},
        requirementType: 'string',
        validate: function(_value, requirement, instance) {
            var value = instance.$element.datepicker('getDate');
            var dt = new Date(requirement);
            dt.setHours(0,0,0,0);
            value && value.setHours(0,0,0,0);

            return !value || value.getTime() > dt.getTime();
        }
    });

      // Initialise the parsley form
      var pslyForm = $("#holded_salary_Release_form").parsley({
        errorClass: "is-invalid",
        successClass: "is-valid",
        errorsWrapper: '<ul class="errors-list"></ul>',
        classHandler: findClosestGroupWrapper,
        errorsContainer: findClosestGroupWrapper,
      });

      // Change handler for 'For Month'
      $('#trans_date').on('change', function() {
        var empID = document.getElementById('employee_id').value
        if (empID == '') {
            return;
        }

        setBusyState();
        $.ajax({
            url: route('API_Call', {method: 'getPayroll'}),
            method: 'GET',
            data: {
                selectedDate: this.value,
                employeeID: empID
            },
            dataType: 'json'
        }).done(function (res) {
            if (res.status && res.status == 200) {
                
            } else if (res.status && res.status == 422) {
                toastr.error("Sorry the salary can not be Hold becouse Salary for this month is already Processed...!");
                document.getElementById('trans_date').value = '';
            } else {
                toastr.error("Something went wrong! Could not retrieve the employee salary");
                document.getElementById('trans_date').value = '';
            }
        }).fail(function () {
            toastr.error("Something went wrong! Please try again later.");
            document.getElementById('trans_date').value = '';
        }).always(unsetBusyState);
    });

  // Handle the submission
  pslyForm.on("form:submit", function () {
    var form = this.element;
    var formData = new FormData(form);

    Swal.fire({
      title: "Are you sure?",
      text: "Are you sure to Release this Holded Salary...!",
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
              toastr.success("Salary Released Successfully...!");
              form.reset();
              window.history.back();
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

  // Handle the form reset
  pslyForm.$element.on("reset", function () {
    pslyForm.reset();
    $("#trans_date").datepicker("update", "");
    $("#trans_date").datepicker("setStartDate", "");
    $("#trans_date").removeAttr("data-parsley-date-after");
  });
});