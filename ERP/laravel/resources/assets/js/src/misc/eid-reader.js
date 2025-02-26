$(function() {
    "use strict";

    window.EmiratesIDReader = {read}

    function read(timeout) {
        return  new Promise((resolve, reject) => {
            try {
                timeout = timeout || 8000;
    
                const extensionId = 'bjccioinbpgikcjgjomjikdinkogihcj';
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeout);
                
                setBusyState();

                chrome.runtime.sendMessage(
                    extensionId,
                    {message: 'getEndPoint'},
                    resp => {
                        const url = resp.apiEndpoint;
                        if (!url || !url.startsWith('http')) {
                            toastr.error("Extension is not configured. Please configure the emirates ID reader extension");
                            return unsetBusyState();
                        }
    
                        fetch(url, {
                            headers: {
                                "accept": "application/json, text/javascript, */*; q=0.01",
                            },
                            method: "GET",
                            signal: controller.signal,
                        })
                        .then(res => res.json())
                        .then(resolve)
                        .catch(function (err) {
                            if (err.message == 'Failed to fetch') {
                                toastr.error(
                                    'Please check if the Emirates ID is inserted correctly.',
                                    'Failed to read from Emirates ID',
                                    {timeOut: 0, extendedTimeOut: 0, closeButton: true}
                                );
                            }
                            
                            else {
                                console.error(err);
                            }
                        })
                        .finally(unsetBusyState);
                    }
                );
            }
            catch (err) {
                console.error(err);
                unsetBusyState();
            }
        });
    }
})