<?php

namespace App\Services;

use App\Entities\TeamMember;
use App\Filters\TeamMemberFilter;
use App\Repositories\TeamMemberRepository;
use Core\Inject;

class TeamMemberService
{
    #[Inject]
    private TeamMemberRepository $teamMemberRepository;

    public function __construct(TeamMemberRepository $teamMemberRepository)
    {
        $this->teamMemberRepository = $teamMemberRepository;
    }

    public function create(TeamMember $teamMember): int
    {
        return $this->teamMemberRepository->insert($teamMember);
    }

    public function update(TeamMember $teamMember): void
    {
        $this->teamMemberRepository->update($teamMember);
    }

    public function delete(int $id): void
    {
        $this->teamMemberRepository->delete($id);
    }

    public function get(int $id): ?TeamMember
    {
        return $this->teamMemberRepository->find($id);
    }

    public function list(?TeamMemberFilter $filter = null): array
    {
        return $this->teamMemberRepository->findAll($filter);
    }
}