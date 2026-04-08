<?php

namespace App\Http\Resources\PL;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PL\GrupaResource as PLGrupaResource;

class PrivredniSubjekatResource extends JsonResource
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
            'tip' => $this->tip,
            'maticniBroj' => $this->maticniBroj,
            'grupa' => $this->grupa ? 
            PLGrupaResource::collection(collect($this->grupa))
            : null
        ];
    }
}
