<?php

namespace App\Services;

use App\Entities\TestingProgram;
use App\Filters\TestingProgramFilter;
use App\Repositories\TestingProgramRepository;
use Core\Inject;

class TestingProgramService
{
    #[Inject]
    private TestingProgramRepository $testingProgramRepository;

    public function __construct(TestingProgramRepository $testingProgramRepository)
    {
        $this->testingProgramRepository = $testingProgramRepository;
    }

    public function create(TestingProgram $testingProgram): int
    {
        return $this->testingProgramRepository->insert($testingProgram);
    }

    public function update(TestingProgram $testingProgram): void
    {
        $this->testingProgramRepository->update($testingProgram);
    }

    public function delete(int $id): void
    {
        $this->testingProgramRepository->delete($id);
    }

    public function get(int $id): ?TestingProgram
    {
        return $this->testingProgramRepository->find($id);
    }

    public function list(?TestingProgramFilter $filter = null): array
    {
        return $this->testingProgramRepository->findAll($filter);
    }
}
