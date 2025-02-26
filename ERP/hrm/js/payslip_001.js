$(function() {
    /** @type {HTMLFormElement} */
    var storage = {
        employees: []
    };

    // read and initialise the employees data from the dom
    (function() {
        /** @type {HTMLSelectElement} */
        var elem = document.getElementById('employees');

        var employees = elem.options;
        for (var i = 0; i < employees.length; i++) {
            var employee = employees[i];
            storage.employees[i] = {
                id: employee.value,
                name: employee.text,
                department: employee.dataset.department,                working_company: employee.dataset.working_company
            }
        }
    })();

    // filter the employees when there is a change in department.
    (function() {
        var departmentElemId = 'department';
        var workingCompanyElemId = 'working_company_id';

        /** @type {HTMLSelectElement} */
        var payrollElem = document.getElementById('payroll_id');
        /** @type {HTMLSelectElement} */
        var departmentElem = document.getElementById(departmentElemId);
        /** @type {HTMLSelectElement} */
        var employeesElem = document.getElementById('employees');
        var workingCompanyElem = document.getElementById(workingCompanyElemId);
        
        // initialise the selects        
        $('#payroll_id, #department, #working_company_id').select2();
        regenerateEmployees();
        
        // add the change listener
        $(`#${departmentElemId}`)
            .on('change', regenerateEmployees);

        $(`#${workingCompanyElemId}`).on('change', regenerateEmployees);

        /**
         * Regenerates the employees HTMLSelectElement based
         * on the department
         */
        function regenerateEmployees() {
            var department = departmentElem.value;           
            var workingCompanyId = workingCompanyElem.value;
            
            
            // filter the employees as per the department
            var filteredEmployees = storage.employees
                .filter(function(employee) {
                    return ((!department.length || employee.department === department)
                    && (!workingCompanyId.length || employee.working_company === workingCompanyId))
                });
            
            // grab the currently selected employees
            var selectedEmployees = [];
            for (var i = 0; i < employeesElem.options.length; i++) {
                if (employeesElem.options[i].selected) {
                    selectedEmployees[selectedEmployees.length] = employeesElem.options[i].value;
                }
            }

            // prepare the dataSource for the select element
            var dataSource = filteredEmployees.map(function(employee) {
                return {
                    id: employee.id,
                    text: employee.name,
                    selected: selectedEmployees.indexOf(employee.id) !== -1
                }
            })
            
            if ($(employeesElem).hasClass('select2-hidden-accessible')) {
                $(employeesElem).select2('destroy');   
            }
            empty(employeesElem);
            $(employeesElem).select2({data: dataSource})
        }
    })();
});