$(function () {
    "use strict;"

    let timerId = null;
    window.Amc = { showExpiryReminder: () => {} };

    const modalEl = document.getElementById('amc-notification-modal');
    
    if (!modalEl) {
        return;
    }

    const bsModal = new bootstrap.Modal(modalEl, {
        backdrop: 'static',
        keyboard: false
    });

    const ackBtn = modalEl.querySelector('[data-amc-ack-btn]');

    $(ackBtn).on('click', function () {
        bsModal.hide();
        ajaxRequest({
            url: route('api.system.amc.expiry.acknowledge'),
            method: 'post',
            blocking: false,
            data: {
                ack_due_at: modalEl.querySelector('[data-ack-due-at]').value
            }
        })
        .done(() => {})
        .fail(() => {})
    });

    window.Amc.showExpiryReminder = () => {
        ajaxRequest({
            url: route('api.system.amc.expiry'),
            method: 'get',
            blocking: false
        })
        .done((respJson) => {
            if (typeof respJson.data == 'undefined') {
                return;
            }

            if (!respJson.data.shouldShowExpiryMsg) {
                return;
            }

            if (respJson.data.waitTimeBeforeAck) {
                ackBtn.dataset.waitTimeBeforeAck = respJson.data.waitTimeBeforeAck || '';
            }

            modalEl.querySelector('[data-ack-due-at]').value = respJson.data.ackDueAt || '';
            modalEl.querySelector('[data-amc-content]').innerHTML = respJson.data.expiryMsg || '';
            bsModal.show();
        })
        .fail(() => {});
    }

    function startTimer(waitSeconds = 5) {
        // Prevent multiple timers from running at the same time;
        if (timerId) return;

        const actualBtnContent = ackBtn.innerHTML;
        ackBtn.disabled = true;
        ackBtn.innerHTML = `Wait <span class="align-baseline" data-amc-btn-countdown>${waitSeconds}</span> Sec`;
        
        const secondsEl = ackBtn.querySelector('[data-amc-btn-countdown]');
        timerId = setInterval(() => {
            let seconds = Number(secondsEl.textContent);
    
            seconds--;
    
            if (seconds <= 0) {
                clearInterval(timerId);
                timerId = null;
                ackBtn.innerHTML = actualBtnContent;
                ackBtn.disabled = false;
                return;
            }

            secondsEl.textContent = seconds;
        }, 1000);
    }

    modalEl.addEventListener('shown.bs.modal', function (ev) {
        const waitTimeBeforeAck = parseFloat(ackBtn.dataset.waitTimeBeforeAck) || 0;

        if (waitTimeBeforeAck != 0) {
            startTimer(waitTimeBeforeAck);
        }
    });

    if (modalEl.classList.contains('show')) {
        modalEl.classList.remove('show');
        bsModal.show();
    }

    // If there is marquee set, duplicate it for seamless scrolling
    const marqueeEl = document.querySelector('.amc-expiry-marquee .marquee-container');
    if (marqueeEl) {
        marqueeEl.innerHTML = `${marqueeEl.innerHTML}\n${marqueeEl.innerHTML}`
    }
});