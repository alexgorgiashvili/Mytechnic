<?php

namespace App\Http\Resources\SiteResource;

use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'link'              => $this->link,
            'slider_bg_image'   => $this->slider_bg_image,
            'text'              => $this->text,         // Added text field
            'button_text'       => $this->button_text,  // Added button_text field
        ];
    }
}
