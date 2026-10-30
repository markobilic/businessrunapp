<?php

namespace App\Http\Resources\PL;

use App\Http\Resources\PL\PodatakResource as PLPodatakResource;
use Illuminate\Http\Resources\Json\JsonResource;

class GrupaResource extends JsonResource
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
            'id' => $this->id,
            'podatak' => $this->podatak ?
            PLPodatakResource::collection(collect($this->podatak))
            : null,
        ];
    }
}
