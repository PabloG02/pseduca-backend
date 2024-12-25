<?php

namespace App\Services;

use App\Entities\Contact;
use App\Filters\ContactFilter;
use App\Repositories\ContactRepository;
use Core\Inject;

class ContactService
{
    #[Inject]
    private ContactRepository $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function create(Contact $contact): int
    {
        return $this->contactRepository->insert($contact);
    }

    public function update(Contact $contact): void
    {
        $this->contactRepository->update($contact);
    }

    public function delete(int $id): void
    {
        $this->contactRepository->delete($id);
    }

    public function get(int $id): ?Contact
    {
        return $this->contactRepository->find($id);
    }

    public function list(?ContactFilter $filter = null): array
    {
        return $this->contactRepository->findAll($filter);
    }
}