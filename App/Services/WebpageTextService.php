<?php

namespace App\Services;

use App\Entities\WebpageText;
use App\Filters\WebpageTextFilter;
use App\Repositories\WebpageTextRepository;
use Core\Inject;

class WebpageTextService
{
    #[Inject]
    private WebpageTextRepository $webpageTextRepository;

    public function __construct(WebpageTextRepository $webpageTextRepository)
    {
        $this->webpageTextRepository = $webpageTextRepository;
    }

    public function get(string $textKey): ?WebpageText
    {
        return $this->webpageTextRepository->find($textKey);
    }

    public function list(?WebpageTextFilter $filter = null): array
    {
        return $this->webpageTextRepository->findAll($filter);
    }

    public function update(WebpageText $webpageText): void
    {
        $this->webpageTextRepository->update($webpageText);
    }
}

