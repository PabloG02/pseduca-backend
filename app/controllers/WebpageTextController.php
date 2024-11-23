<?php

namespace App\Controllers;

use App\Entities\WebpageText;
use App\Filters\WebpageTextFilter;
use App\Services\WebpageTextService;
use Core\Inject;
use PDOException;

class WebpageTextController
{
    #[Inject]
    private WebpageTextService $webpageTextService;

    public function __construct(WebpageTextService $webpageTextService)
    {
        $this->webpageTextService = $webpageTextService;
    }

    public function get(): void
    {
        try {
            $textKey = filter_input(INPUT_POST, 'textKey');
            if (!isset($textKey)) {
                http_response_code(400);
                echo json_encode(['error' => 'textKey is required.']);
                return;
            }

            $webpageText = $this->webpageTextService->get($textKey);

            if (!$webpageText) {
                http_response_code(404);
                echo json_encode(['error' => 'Text not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($webpageText);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $webpageTexts = $this->webpageTextService->list($filter);
            http_response_code(200);
            echo json_encode($webpageTexts);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function update(): void
    {
        $textKey = filter_input(INPUT_POST, 'textKey');
        $text = filter_input(INPUT_POST, 'text');

        if (!isset($textKey) || !isset($text)) {
            http_response_code(400);
            echo json_encode(['error' => 'Both textKey and text are required.']);
            return;
        }

        try {
            $this->webpageTextService->update(new WebpageText($textKey, $text));
            http_response_code(200);
            echo json_encode(['message' => 'WebpageText updated successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function createFilterFromRequest(): WebpageTextFilter {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new WebpageTextFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return WebpageTextFilter::fromArray($data);
    }
}
