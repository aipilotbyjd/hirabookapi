<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SponsoredAdResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'imageUrl' => $this->image_url,
            'targetUrl' => $this->target_url,
            'sponsorName' => $this->sponsor_name,
            'sponsorLogo' => $this->sponsor_logo,
            'ctaText' => $this->cta_text,
            'backgroundColor' => $this->background_color,
            'textColor' => $this->text_color,
            'stats' => [
                'impressions' => $this->impressions_count,
                'clicks' => $this->clicks_count,
                'ctr' => $this->impressions_count > 0
                    ? round(($this->clicks_count / $this->impressions_count) * 100, 2)
                    : 0,
            ],
        ];
    }
}
