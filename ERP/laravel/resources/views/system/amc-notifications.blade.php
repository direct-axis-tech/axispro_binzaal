<div
    id="amc-notification-modal"
    class="{{ class_names([
        "modal fade",
        "show" => app(\App\Amc::class)->shouldShowExpiryMsg()
    ]) }}"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <input type="hidden" data-ack-due-at value="{{ app(\App\Amc::class)->getAckDueAt() }}">
                <img class="logo" src="{{ media('misc/aws.png') }}" alt="Logo">
                <div class="expiry-msg" data-amc-content>
                    {!! app(\App\Amc::class)->getExpiryMsg() !!}
                </div>
                <div class="text-end">
                    <button
                        class="btn"
                        type="button"
                        data-amc-ack-btn
                        @if(app(\App\Amc::class)->getWaitTimeBeforeAck())
                        disabled
                        data-wait-time-before-ack="{{ app(\App\Amc::class)->getWaitTimeBeforeAck() }}">
                        @else
                        data-wait-time-before-ack="0">
                        @endif
                        Acknowledge
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
