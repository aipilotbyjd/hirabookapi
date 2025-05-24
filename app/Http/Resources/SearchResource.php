<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'from' => $this->from,
            'description' => $this->description,
            'amount' => $this->amount,
            'date' => $this->date->format('Y-m-d'),
            'source' => $this->when($this->source, [
                'id' => $this->source->id,
                'name' => $this->source->name,
                'icon' => $this->source->icon,
            ]),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
