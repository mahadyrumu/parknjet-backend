<?php

namespace App\Services\Dispatch;

use DateTime;

class DispatchCommonService
{
    public function getDispatchData($claim_id, $reservation, $island, $delay, $to = null)
    {
        $lot_id = getLotIdFromClaimId($claim_id);
        $dispatch_data['phone'] = $to;
        $dispatch_data['cid'] = $claim_id;
        $dispatch_data['island'] = $island;
        $dispatch_data['delay'] = $delay;

        date_default_timezone_set('America/Los_Angeles');
        $dt = new DateTime();
        $dt_tm = date_format($dt, "Y-m-d H:i:s");

        $dispatch['cid'] = $claim_id;
        $dispatch['lot_id'] = $lot_id;
        $dispatch['rsvn'] = $reservation;
        $dispatch['type'] = "pu";
        $dispatch['delay'] = $delay;
        $dispatch['start'] = $dt_tm;

        $dt->modify("+$delay minutes");
        $dt_tm = date_format($dt, "Y-m-d H:i:s");
        $dispatch['e0'] = $dt_tm;

        // $dispatch['e1'] = $dt_tm;
        // $dispatch['e2'] = $dt_tm;

        $dispatch['phone'] = $to;
        $dispatch['active'] = 1;
        $dispatch['island'] = $island;

        $comment = implode('|', $dispatch_data);
        $dispatch['comment'] = $comment;
        $dispatch['e0_ipa'] = 0;
        $dispatch['e1_ipa'] = 0;
        $dispatch['e2_ipa'] = 0;
        $dispatch['x'] = 0;
        $dispatch['xflags'] = 0;
        return $dispatch;
    }
}
