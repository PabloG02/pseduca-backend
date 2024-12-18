<?php

namespace App\Services;

use App\Entities\Course;
use App\Filters\CourseFilter;
use App\Repositories\CourseRepository;
use Core\Inject;

class CourseService
{
    #[Inject]
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function create(Course $course): int
    {
        return $this->courseRepository->insert($course);
    }

    public function update(Course $course): void
    {
        $this->courseRepository->update($course);
    }

    public function delete(int $id): void
    {
        $this->courseRepository->delete($id);
    }

    public function get(int $id): ?Course
    {
        return $this->courseRepository->find($id);
    }

    public function list(?CourseFilter $filter = null): array
    {
        return $this->courseRepository->findAll($filter);
    }
}
