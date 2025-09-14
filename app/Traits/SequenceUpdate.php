<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait SequenceUpdate
{
    public function updateSequence($connection_name, $parent_db, $seq_db)
    {
        $parent_last_id = DB::connection($connection_name)
            ->table($parent_db)
            ->select('id')
            ->orderBy('id', 'desc')
            ->first()->id;
        Log::info("update Sequence table : " . $seq_db . " last_val : " . $parent_last_id);
        DB::connection($connection_name)
            ->table($seq_db)
            ->update(['next_val' => $parent_last_id + 1]);
    }
}
