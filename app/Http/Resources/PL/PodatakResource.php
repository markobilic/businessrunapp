<?php

namespace App\Http\Resources\PL;

use Illuminate\Http\Resources\Json\JsonResource;

class PodatakResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'naziv' => $this->naziv,
            'vrednost' => $this->vrednost,
            'tip' => $this->tip
        ];
    }
}
