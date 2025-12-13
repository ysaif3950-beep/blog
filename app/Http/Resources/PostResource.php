<?php

namespace App\Http\Resources;

use App\Models\tag;
use App\Models\User;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
              'id'=> $this->id,
              "title"=>$this->title,
              'excerpt'=>Str::limit($this->description,100, '...'),
               'description' => $this->description,

              'image'=>$this->image_url,
              'user'=>new UserResource($this->whenloaded('user')),
              'tag'=>TagResourse::collection($this->whenLoaded('tags')),
              'created_at'=>$this->created_at->format('Y/m/d'),
              'updated_at'=>$this->updated_at->format('Y/m/d'),

        ];
    }
}
