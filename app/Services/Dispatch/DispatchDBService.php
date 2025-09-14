<?php

namespace App\Services\Dispatch;

use App\Models\Dispatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DispatchDBService
{
    public function storeDispatch($dispatch)
    {
        return Dispatch::insert($dispatch);
    }

    public function findDispatch($claimId)
    {
        return Dispatch::where("cid", $claimId)->where('start','>=',Carbon::today())->orderBy('start', 'desc')->first();
    }

    public function storeOrUpdate($claimId, $dispatch_data)
    {
        $dispatch = $this->findDispatch($claimId);
        if ($dispatch) {
            Log::info("Dispatch Found ");
            $dispatch->update($dispatch_data);
            Log::info($dispatch);
        } else {
            $dispatch = $this->storeDispatch($dispatch_data);
            Log::info("Dispatch Created ");
        }
        return $dispatch;
    }
}
