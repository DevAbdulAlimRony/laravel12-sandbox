<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    // Transform the resource into array.

    // When returning a resource collection from a route, Laravel resets the collection's keys so that they are in numerical order. 
    // Can prevent that:
    public $preserveKeys = true;

    // We can access model properties directly from the $this variable. 
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Specify related model's resource:
            'posts' => PostResource::collection($this->posts),

            // Conditional Attribute: if no admin then attribute will be removed.
            'secret' => $this->when($request->user()->isAdmin(), 'secret-value'), 
            // Can give closure also: when($request->user()->isAdmin(), function (){}
            'name' => $this->whenHas('name'), // Add attribute if at actually presents.
            'name' => $this->whenNotNull($this->name), // Add if attribute's value is not null
            // If we have same condition for multiple attributes:
             $this->mergeWhen($request->user()->isAdmin(), []);

            // Conditional Relstionship:
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'posts_count' => $this->whenCounted('posts'), // add if relationship counted from controler
            'words_avg' => $this->whenAggregated('posts', 'words', 'avg'), // also for: sum, min, max.
            'expires_at' => $this->whenPivotLoaded('role_user', function () { return $this->pivot->expires_at; });
            // whenPivotLoaded(new Membership..., >whenPivotLoadedAs('subscription', 'role_user', function () {})/

            // Metadata in collection resource:
            'links' => [
                'self' => 'link-value',
            ], // can be done using public function with()
            // or in runtime: ->additional(['meta' => [.....
        ];
    }

    // For collection resource:
    // UserCollection will attempt to map the given user instances into the UserResource resource.
    // and we can access the resource: $this->collection
    // But we can specify the resource class if name is different: public $collects = Member::class
    // Wrapping: return ['data' => $this->collection];
    // aravel will never let resources be accidentally double-wrapped
    // If paginated data, automatically wrapped in data.

    // For pagination data, if we want to customize meta, links etc wrapper:
    public function paginationInformation($request, $paginated, $default)
    {
        $default['links']['custom'] = 'https://example.com';
        return $default;
    }

    // Customizing the ongoing resource:
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('X-Value', 'True');
    }
}