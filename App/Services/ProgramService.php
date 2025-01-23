<?php

namespace App\Services;

use App\Entities\Program;
use App\Filters\ProgramFilter;
use App\Repositories\ProgramRepository;
use Core\Inject;

class ProgramService
{
    #[Inject]
    private ProgramRepository $programRepository;

    public function __construct(ProgramRepository $programRepository)
    {
        $this->programRepository = $programRepository;
    }

    public function create(Program $program): int
    {
        return $this->programRepository->insert($program);
    }

    public function update(Program $program): void
    {
        $this->programRepository->update($program);
    }

    public function delete(int $id): void
    {
        $this->programRepository->delete($id);
    }

    public function get(int $id): ?Program
    {
        return $this->programRepository->find($id);
    }

    public function list(?ProgramFilter $filter = null): array
    {
        return $this->programRepository->findAll($filter);
    }
}
