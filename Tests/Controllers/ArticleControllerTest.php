<?php

namespace Tests\Controllers;

require_once __DIR__ . '/../../autoload.php';
require_once __DIR__ . '/../../loadconfig.php';

use App\Controllers\ArticleController;
use App\Filters\ArticleFilter;
use App\Services\ArticleService;
use Core\DIContainer;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class ArticleControllerTest extends TestCase

{
    private ArticleController $articleController;
    static private int $articleId;


    protected function setUp(): void
    {
        $articleService = DIContainer::resolve(ArticleService::class);
        $this->articleController = $this->getMockBuilder(ArticleController::class)
            ->setConstructorArgs([$articleService])
            ->onlyMethods(['hasRole', 'createFilterFromRequest', 'saveImageFile', 'deleteImageFile'])
            ->getMock();

        $this->articleController->method('hasRole')
            ->willReturn(true);
        $this->articleController->method('saveImageFile')
            ->willReturn('/uploads/images/test_image.png');
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_FILES = [];
    }

    private function setRole($role): void
    {
        $_SESSION['role'] = $role;
    }

    public function testCreateArticle(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $_POST['title'] = 'Test Title';
        $_POST['subtitle'] = 'Test Subtitle';
        $_POST['body'] = 'This is the body of the test article.';
        $_POST['author'] = 'Test Author';
        $_POST['image_alt'] = 'Image Alt';
        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Article created successfully.', $response['message']);
        $this->assertEquals(201, http_response_code());

        $this->assertArrayHasKey('id', $response);
        $this->assertNotEmpty($response['id']);


        self::$articleId = $response['id'];
    }


    public function testCreateArticleNoTitle(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        //no title
        $_POST['subtitle'] = 'Subtitle Article.';
        $_POST['body'] = 'The article body';
        $_POST['author'] = 'Pepe Garcia Rodriguez';
        $_POST['image_alt'] = 'image';


        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateArticleNoSubtitle(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['title'] = 'Title Article';
        //no subtitle
        $_POST['body'] = 'The article body';
        $_POST['author'] = 'Pepe Garcia Rodriguez';
        $_POST['image_alt'] = 'image';


        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateArticleNoBody(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['title'] = 'Title Article';
        $_POST['subtitle'] = 'Subtitle Article.';
        //no body
        $_POST['author'] = 'Pepe Garcia Rodriguez';
        $_POST['image_alt'] = 'image';


        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateArticleNoAuthor(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['title'] = 'Title Article';
        $_POST['subtitle'] = 'Subtitle Article.';
        $_POST['body'] = 'The article body';
        //no author
        $_POST['image_alt'] = 'image';


        $_FILES['image_uri'] = [
            'name' => 'test_image.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateArticleNoImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['title'] = 'Title Article';
        $_POST['subtitle'] = 'Subtitle Article.';
        $_POST['body'] = 'The article body';
        //no image
        $_POST['image_alt'] = 'image';


        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid or missing required fields.', $response['error']);
    }

    public function testCreateArticleInvalidImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['title'] = 'Title Article';
        $_POST['subtitle'] = 'Subtitle Article.';
        $_POST['body'] = 'The article body';
        $_POST['author'] = 'Pepe Garcia Rodriguez';
        $_POST['image_alt'] = 'image';

        $_FILES['image_uri'] = [
            'name' => 'test_image.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();

        $this->articleController->create();

        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
    }

    public function testCreateArticleInvalidImageType(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['title'] = 'Title Article';
        $_POST['subtitle'] = 'Subtitle Article.';
        $_POST['body'] = 'The article body';
        $_POST['author'] = 'Pepe Garcia Rodriguez';
        $_POST['image_alt'] = 'image';

        $_FILES['image_uri'] = [
            'name' => 'document.pdf',
            'type' => 'application/pdf',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
        ];

        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid image file format. Only JPEG and PNG are allowed.', $response['error']);
    }

    public function testCreateArticleNoimage_alt(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['title'] = 'Title Article';
        $_POST['subtitle'] = 'Subtitle Article.';
        $_POST['body'] = 'The article body';
        $_POST['author'] = 'Pepe Garcia Rodriguez';

        $_FILES['image_uri'] = [
            'name' => 'example.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/php-example',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        //no alt

        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Image alt text is required when uploading an image.', $response['error']);
    }


    #[Depends('testCreateArticle')]
    public function testGetArticle(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['id'] = self::$articleId;

        ob_start();
        $this->articleController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(self::$articleId, $response['id']);
        $this->assertEquals('Test Title', $response['title']);
        $this->assertEquals('Test Subtitle', $response['subtitle']);
    }

    public function testInvalidArticleId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_GET['id'] = 'invalid_id';

        ob_start();
        $this->articleController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(400, http_response_code());

        $this->assertEquals('Invalid article ID.', $response['error']);
    }

    public function testGetArticleNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST['id'] = 483427;

        ob_start();
        $this->articleController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Article not found.', $response['error']);
    }

    #[Depends('testCreateArticle')]
    public function testUpdateArticle(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = self::$articleId;
        $_POST['title'] = 'Title Article update';
        $_POST['subtitle'] = 'Subtitle Article update.';
        $_POST['body'] = 'The article body update';
        $_POST['author'] = 'Pepe Garcia Fernandez';
        $_POST['image_alt'] = 'image update';

        $_FILES['image_uri'] = [
            'name' => 'test_image_update.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];
        $this->setRole('admin');


        ob_start();
        $this->articleController->update();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals('Article updated successfully.', $response['message']);
    }

    public function testUpdateInvalidId(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = 'invalid_id';
        $_POST['title'] = 'Title Article update';
        $_POST['subtitle'] = 'Subtitle Article update.';
        $_POST['body'] = 'The article body update';
        $_POST['author'] = 'Pepe Garcia Fernandez';
        $_POST['image_alt'] = 'image update';

        $_FILES['image_uri'] = [
            'name' => 'test_image_update.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');


        ob_start();
        $this->articleController->update();
        $output = ob_get_clean();

        // Verify that the response is successful
        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid article ID.', $response['error']);
    }

    public function testUpdateArticleInvalidImage(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['title'] = 'Title Article update';
        $_POST['subtitle'] = 'Subtitle Article update.';
        $_POST['body'] = 'The article body update';
        $_POST['author'] = 'Pepe Garcia Fernandez';
        $_POST['image_alt'] = 'image update';
        $_FILES['image_uri'] = [
            'name' => 'test_image',
            'type' => 'image',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->articleController->create();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
    }

    public function testUpdateArticleNotFound(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_POST['id'] = 483427;
        $_POST['title'] = 'Title Article update';
        $_POST['subtitle'] = 'Subtitle Article update.';
        $_POST['body'] = 'The article body update';
        $_POST['author'] = 'Pepe Garcia Fernandez';
        $_POST['image_alt'] = 'image update';
        $_FILES['image_uri'] = [
            'name' => 'test_image',
            'type' => 'image',
            'tmp_name' => '/tmp/phpYzdqkD',
            'error' => UPLOAD_ERR_OK,
            'size' => 12345,
        ];

        $this->setRole('admin');

        ob_start();
        $this->articleController->get();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Article not found.', $response['error']);
    }

    #[Depends('testCreateArticle')]
    public function testListArticle(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $filterData = ['name' => 'article'];

        ob_start();
        $this->articleController->method('createFilterFromRequest')
            ->willReturn(ArticleFilter::fromArray($filterData));
        $this->articleController->list();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        $this->assertTrue(isset($response['total_count']) && $response['total_count'] > 0);
    }

    #[Depends('testCreateArticle')]
    #[Depends('testListArticle')]
    public function testDeleteArticle(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = self::$articleId;

        ob_start();
        $this->articleController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertArrayHasKey('message', $response);
        $this->assertEquals(200, http_response_code());
    }

    public function testDeleteInvalidArticleId(): void
    {
        // Simulate a DELETE request to remove a team member with an invalid ID
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = 'invalid-id';

        ob_start();
        $this->articleController->delete();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        // Verify that the status code is 400
        $this->assertEquals(400, http_response_code());

        // Verify that the response contains the expected error message
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Invalid article ID.', $response['error']);
    }

    public function testDeleteArticleNotFound(): void
    {
        // Simulate a POST request to create a user
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_POST['id'] = 32849023;

        // Capture the output
        ob_start();
        $this->articleController->delete();
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Article not found.', $response['error']);
    }

}