<?php

namespace App\Services;

use App\Entities\Resource;
use App\Filters\ResourceFilter;
use App\Repositories\ResourceRepository;
use Core\Inject;

class ResourceService
{
    #[Inject]
    private ResourceRepository $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function create(Resource $resource): int
    {
        return $this->resourceRepository->insert($resource);
    }

    public function update(Resource $resource): void
    {
        $this->resourceRepository->update($resource);
    }

    public function delete(int $id): void
    {
        $this->resourceRepository->delete($id);
    }

    public function get(int $id): Resource
    {
        return $this->resourceRepository->find($id);
    }

    public function list(?ResourceFilter $filter): array
    {
        return $this->resourceRepository->findAll($filter);
    }
}