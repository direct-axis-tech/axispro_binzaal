if (KTUtil) {

    if (KTUtil.getUniqueID && !KTUtil.getUniqueId) {
        KTUtil.getUniqueId = KTUtil.getUniqueID;
    }

    if (!KTUtil.getResponsiveValue) {
        KTUtil.getResponsiveValue = function(value, defaultValue) {
            var width = this.getViewPort().width;
            var result;

            value = KTUtil.parseJson(value);

            if (typeof value === 'object') {
                var resultKey;
                var resultBreakpoint = -1;
                var breakpoint;

                for (var key in value) {
                    if (key === 'default') {
                        breakpoint = 0;
                    } else {
                        breakpoint = this.getBreakpoint(key) ? this.getBreakpoint(key) : parseInt(key);
                    }

                    if (breakpoint <= width && breakpoint > resultBreakpoint) {
                        resultKey = key;
                        resultBreakpoint = breakpoint;
                    }
                }

                if (resultKey) {
                    result = value[resultKey];
                } else {
                    result = value;
                }
            } else {
                result = value;
            }

            return result;
        }
    }

    if (!KTUtil.parseJson) {
        KTUtil.parseJson = function(value) {
            if (typeof value === 'string') {
                value = value.replace(/'/g, "\"");

                var jsonStr = value.replace(/(\w+:)|(\w+ :)/g, function(matched) {
                    return '"' + matched.substring(0, matched.length - 1) + '":';
                });

                try {
                    value = JSON.parse(jsonStr);
                } catch(e) { }
            }

            return value;
        }
    }

    if (!KTUtil.throttle) {
        KTUtil.throttle = function (timer, func, delay) {
        	// If setTimeout is already scheduled, no need to do anything
        	if (timer) {
        		return;
        	}

        	// Schedule a setTimeout after delay seconds
        	timer  =  setTimeout(function () {
        		func();

        		// Once setTimeout function execution is finished, timerId = undefined so that in <br>
        		// the next scroll event function execution can be scheduled by the setTimeout
        		timer  =  undefined;
        	}, delay);
        }
    }

    if (!KTUtil.snakeToCamel) {
        KTUtil.snakeToCamel = function(s){
            return s.replace(/(\-\w)/g, function(m){return m[1].toUpperCase();});
        }
    }
}

