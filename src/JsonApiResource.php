<?php

declare(strict_types=1);

namespace TiMacDonald\JsonApi;

use Closure;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TiMacDonald\JsonApi\Contracts\ResourceIdResolver;
use TiMacDonald\JsonApi\Contracts\ResourceTypeable;
use TiMacDonald\JsonApi\Contracts\ResourceTypeResolver;

abstract class JsonApiResource extends JsonResource
{
    use Concerns\Attributes;
    use Concerns\Relationships;
    use Concerns\ResourceIdentification;

    /**
     * @return array<string, mixed>
     */
    protected function toAttributes(Request $request): array
    {
        return [
            //
        ];
    }

    /**
     * @return array<string, Closure(Request $request): JsonApiResource>
     */
    protected function toRelationships(Request $request): array
    {
        return [
            //
        ];
    }

    /**
     * @param Request $request
     * @return array{
     *      id: string,
     *      type: string,
     *      attributes: array<string, mixed>,
     *      relationships: array<string, array{data: array{id: string, type: string}}>
     * }
     */
    public function toArray($request): array
    {
        return [
            'id' => self::resourceId($this->resource),
            'type' => self::resourceType($this->resource),
            'attributes' => $this->parseAttributes($request),
            'relationships' => $this->parseRelationships($request),
        ];
    }

    /**
     * @param Request $request
     * @return array{included?: array<JsonApiResource>}
     */
    public function with($request): array
    {
        $includes = $this->resolveNestedIncludes($request);

        if (count($includes) === 0) {
            return [];
        }

        return ['included' => $includes];
    }

    /**
     * @return JsonApiResourceCollection<JsonApiResource>
     */
    public static function collection(mixed $resource): JsonApiResourceCollection
    {
        return tap(new JsonApiResourceCollection($resource, static::class), function (JsonApiResourceCollection $collection): void {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true; // @phpstan-ignore-line
            }
        });
    }
}
