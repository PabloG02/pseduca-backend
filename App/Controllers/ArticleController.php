<?php

namespace App\Controllers;

use App\Entities\Article;
use App\Filters\ArticleFilter;
use App\Services\ArticleService;
use Core\Inject;
use DateTimeImmutable;
use PDOException;
use RuntimeException;

class ArticleController extends BaseController
{
    #[Inject]
    private ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function create(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $title = filter_var($_POST['title'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $subtitle = filter_var($_POST['subtitle'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $body = filter_var($_POST['body'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $author = filter_var($_POST['author'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $imageFile = $_FILES['image_uri'] ?? null;
        $imageAlt = filter_var($_POST['image_alt'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        if (!isset($title) || !isset($subtitle) || !isset($body) || !isset($author)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing required fields.']);
            return;
        }

        $imageUri = null;
        if (isset($imageFile)) {
            $imageValidation = $this->validateImageFile($imageFile);
            if ($imageValidation !== null) {
                http_response_code(400);
                echo json_encode(['error' => $imageValidation]);
                return;
            }

            if (!isset($imageAlt)) {
                http_response_code(400);
                echo json_encode(['error' => 'Image alt text is required when uploading an image.']);
                return;
            }

            $imageUri = $this->saveImageFile($imageFile);
        } elseif (isset($imageAlt)) {
            http_response_code(400);
            echo json_encode(['error' => 'Image alt text provided but no image uploaded.']);
            return;
        }

        try {
            $article = new Article(
                0,
                $title,
                $subtitle,
                $body,
                $author,
                new DateTimeImmutable(),
                $imageUri,
                $imageAlt
            );
            $id = $this->articleService->create($article);

            http_response_code(201);
            echo json_encode(['message' => 'Article created successfully.', 'id' => $id]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);

            if ($imageUri) {
                $this->deleteImageFile($imageUri);
            }
        }
    }

    public function update(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $title = filter_var($_POST['title'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $subtitle = filter_var($_POST['subtitle'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $body = filter_var($_POST['body'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $author = filter_var($_POST['author'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $imageFile = $_FILES['image_uri'] ?? null;
        $imageAlt = filter_var($_POST['image_alt'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid article ID.']);
            return;
        }

        try {
            $article = $this->articleService->get($id);

            if (!$article) {
                http_response_code(404);
                echo json_encode(['error' => 'Article not found.']);
                return;
            }

            // Update fields if provided
            if ($title !== null) {
                $article->title = $title;
            }
            if ($subtitle !== null) {
                $article->subtitle = $subtitle;
            }
            if ($body !== null) {
                $article->body = $body;
            }
            if ($author !== null) {
                $article->author = $author;
            }

            // Handle image update
            if ($imageFile !== null) {
                $imageValidation = $this->validateImageFile($imageFile);
                if ($imageValidation !== null) {
                    http_response_code(400);
                    echo json_encode(['error' => $imageValidation]);
                    return;
                }

                if (!isset($imageAlt)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Image alt text is required when uploading an image.']);
                    return;
                }

                if ($article->imageUri) {
                    $this->deleteImageFile($article->imageUri);
                }
                $article->imageUri = $this->saveImageFile($imageFile);
                $article->imageAlt = $imageAlt;
            } elseif ($imageAlt !== null && $article->imageUri === null) {
                http_response_code(400);
                echo json_encode(['error' => 'Image alt text provided but no image uploaded.']);
                return;
            }

            $this->articleService->update($article);

            http_response_code(200);
            echo json_encode(['message' => 'Article updated successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function delete(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid article ID.']);
            return;
        }

        try {
            $article = $this->articleService->get($id);

            if (!$article) {
                http_response_code(404);
                echo json_encode(['error' => 'Article not found.']);
                return;
            }

            if ($article->imageUri) {
                $this->deleteImageFile($article->imageUri);
            }

            $this->articleService->delete($id);

            http_response_code(200);
            echo json_encode(['message' => 'Article deleted successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function get(): void
    {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid article ID.']);
            return;
        }

        try {
            $article = $this->articleService->get($id);

            if (!$article) {
                http_response_code(404);
                echo json_encode(['error' => 'Article not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($article);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $result = $this->articleService->list($filter);

            http_response_code(200);
            echo json_encode($result);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    protected function createFilterFromRequest(): ArticleFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new ArticleFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return ArticleFilter::fromArray($data);
    }

    protected function validateImageFile(array $imageFile): ?string
    {
        if ($imageFile['error'] !== UPLOAD_ERR_OK) {
            return 'Error uploading image file.';
        }

        $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return 'Invalid image file format. Only JPEG and PNG are allowed.';
        }

        return null;
    }

    protected function saveImageFile(array $imageFile): string
    {
        $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;

        $destinationDir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'uploads', 'images', 'articles']);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destination = $destinationDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($imageFile['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to save the uploaded image file.');
        }

        return '/uploads/images/articles/' . $filename;
    }

    protected function deleteImageFile(string $imageUri): void
    {
        $imagePath = explode('/', $imageUri);
        $imagePath = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', ...$imagePath]);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}