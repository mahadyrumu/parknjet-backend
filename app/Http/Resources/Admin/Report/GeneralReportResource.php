<?php

namespace App\Http\Resources\Admin\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
        // return [
        //     'generalReport' => $this->generalReport,
        //     'reservationSummaries' => $this->reservationSummaries,
        //     'revenueSummaries' => $this->revenueSummaries
        // ];
    }
}
