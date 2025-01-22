<?php

namespace App\Services;

use App\Entities\AcademicProgram;
use App\Filters\AcademicProgramFilter;
use App\Repositories\AcademicProgramRepository;
use Core\Inject;

class AcademicProgramService
{
    #[Inject]
    private AcademicProgramRepository $academicProgramRepository;

    public function __construct(AcademicProgramRepository $academicProgramRepository)
    {
        $this->academicProgramRepository = $academicProgramRepository;
    }

    public function create(AcademicProgram $program): int
    {
        return $this->academicProgramRepository->insert($program);
    }

    public function update(AcademicProgram $program): void
    {
        $this->academicProgramRepository->update($program);
    }

    public function delete(int $id): void
    {
        $this->academicProgramRepository->delete($id);
    }

    public function get(int $id): ?AcademicProgram
    {
        return $this->academicProgramRepository->find($id);
    }

    public function list(?AcademicProgramFilter $filter = null): array
    {
        return $this->academicProgramRepository->findAll($filter);
    }
}
