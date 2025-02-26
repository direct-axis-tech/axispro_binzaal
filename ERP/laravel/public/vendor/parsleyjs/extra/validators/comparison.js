(function () {
    // gt, gte, lt, lte, ne extra validators
    var parseRequirement = function (requirement) {
        if (isNaN(+requirement))
            return parseFloat(jQuery(requirement).val());
        else
            return +requirement;
    };

    // Greater than validator
    window.Parsley.addValidator('gt', {
        requirementType: 'string',
        messages: {
            en: "This value must be greater than %s"
        },
        validate: function (value, requirement) {
            return parseFloat(value) > parseRequirement(requirement);
        },
        priority: 32
    });

    // Greater than or equal to validator
    window.Parsley.addValidator('gte', {
        requirementType: 'string',
        messages: {
            en: "This value must be greater than or equal to %s"
        },
        validate: function (value, requirement) {
            return parseFloat(value) >= parseRequirement(requirement);
        },
        priority: 32
    });

    // Less than validator
    window.Parsley.addValidator('lt', {
        requirementType: 'string',
        messages: {
            en: "This value must be less than %s"
        },
        validate: function (value, requirement) {
            return parseFloat(value) < parseRequirement(requirement);
        },
        priority: 32
    });

    // Less than or equal to validator
    window.Parsley.addValidator('lte', {
        requirementType: 'string',
        messages: {
            en: "This value must be less than or equal to %s"
        },
        validate: function (value, requirement) {
            return parseFloat(value) <= parseRequirement(requirement);
        },
        priority: 32
    });

    // Not equal to validator
    window.Parsley.addValidator('ne', {
        requirementType: 'string',
        messages: {
            en: "This value must not be equal to %s"
        },
        validate: function (value, requirement) {
            return parseFloat(value) != parseRequirement(requirement);
        },
        priority: 32
    });
})();
