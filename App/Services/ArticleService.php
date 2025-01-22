<?php

namespace App\Services;

use App\Entities\Article;
use App\Filters\ArticleFilter;
use App\Repositories\ArticleRepository;
use Core\Inject;

class ArticleService
{
    #[Inject]
    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function create(Article $article): int
    {
        return $this->articleRepository->insert($article);
    }

    public function update(Article $article): void
    {
        $this->articleRepository->update($article);
    }

    public function delete(int $id): void
    {
        $this->articleRepository->delete($id);
    }

    public function get(int $id): ?Article
    {
        return $this->articleRepository->find($id);
    }

    public function list(?ArticleFilter $filter = null): array
    {
        return $this->articleRepository->findAll($filter);
    }
}
