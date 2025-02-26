<?php

namespace App\Http\Controllers\System;

use App\Amc;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AmcController extends Controller {
    /**
     * Returns the AMC Expiry details
     *
     * @return array
     */
    public function getSystemExpiryDetails()
    {
        $amc = app(\App\Amc::class);

        return response()->json([
            'data' => [
                'ackDueAt' => $amc->getAckDueAt(),
                'shouldShowExpiryMsg' => $amc->shouldShowExpiryMsg(),
                'expiryMsg' => $amc->getExpiryMsg(),
                'waitTimeBeforeAck' => $amc->getWaitTimeBeforeAck()
            ]
        ]);
    }

    /**
     * Acknowledge that the current logged in user has seen and read the Notification
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function acknowledgeSystemExpiry(Request $request)
    {
        $user = authUser()->fresh();

        abort_if(!$user->amc_expiry_ack_due_at, 422);

        abort_if($user->amc_expiry_ack_due_at != $request->input('ack_due_at'), 422);

        abort_if(
            $user->amc_expiry_ack_at && $user->amc_expiry_ack_due_at < $user->amc_expiry_ack_at,
            422,
            "The AMC expiry has already been acknowledged."
        );

        $user->query()->whereId($user->id)->update([
            'amc_expiry_ack_at' => date(DB_DATETIME_FORMAT),
            'amc_expiry_times_ack' => DB::raw('amc_expiry_times_ack + 1')
        ]);

        return response('', 204);
    }


    public function fetchFromUpstream(Request $request) {
        $amc = app(Amc::class);
        
        $endpoint = $amc->getUpstreamEndPoint();

        abort_unless($endpoint, Response::HTTP_UNAUTHORIZED);

        $parsed = parse_url($endpoint);

        $upstreamIp = \IPLib\Factory::parseAddressString(gethostbyname($parsed['host']));

        $requestFromIp = \IPLib\Factory::parseAddressString($request->ip());

        abort_if(
            $upstreamIp->getRangeType() == \IPLib\Range\Type::T_PUBLIC
            && $upstreamIp->toString() != $requestFromIp->toString(),
            Response::HTTP_UNAUTHORIZED
        );

        $amc->fetchFromUpstream();

        return response('', 204);
    }
}