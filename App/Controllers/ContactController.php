<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Filters\ContactFilter;
use App\Services\ContactService;
use Core\Inject;
use PDOException;

class ContactController extends BaseController
{
    #[Inject]
    private ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function create(): void
    {
        if (!$this->hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized.']);
            return;
        }

        $address = filter_var($_POST['address'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $phone = filter_var($_POST['phone'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $googleMapsEmbedUrl = filter_var($_POST['google_maps_embed_url'], FILTER_VALIDATE_URL);

        if (!$address || !$email || !$phone || !$googleMapsEmbedUrl ||
            !str_starts_with($googleMapsEmbedUrl, 'https://www.google.com/maps/embed?')) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing required fields.']);
            return;
        }

        try {
            $contact = new Contact(
                0, // ID will be auto-generated
                $address,
                $email,
                $phone,
                $googleMapsEmbedUrl
            );
            $id = $this->contactService->create($contact);

            http_response_code(201);
            echo json_encode(['message' => 'Contact created successfully.', 'id' => $id]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
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

        if ($id === false) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid contact ID.']);
            return;
        }

        try {
            $contact = $this->contactService->get($id);

            if (!$contact) {
                http_response_code(404);
                echo json_encode(['error' => 'Contact not found.']);
                return;
            }

            $address = filter_var($_POST['address'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $email = filter_var($_POST['email'] ?? null, FILTER_VALIDATE_EMAIL);
            $phone = filter_var($_POST['phone'] ?? null, FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
            $googleMapsEmbedUrl = filter_var($_POST['google_maps_embed_url'] ?? null, FILTER_VALIDATE_URL);

            if ($address !== null) {
                $contact->address = $address;
            }
            if ($email !== false) {
                $contact->email = $email;
            }
            if ($phone !== null) {
                $contact->phone = $phone;
            }
            if ($googleMapsEmbedUrl !== false) {
                if (!str_starts_with($googleMapsEmbedUrl, 'https://www.google.com/maps/embed?')) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid Google Maps embed URL format.']);
                    return;
                }
                $contact->googleMapsEmbedUrl = $googleMapsEmbedUrl;
            }

            $this->contactService->update($contact);

            http_response_code(200);
            echo json_encode(['message' => 'Contact updated successfully.']);
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
            echo json_encode(['error' => 'Invalid contact ID.']);
            return;
        }

        try {
            $contact = $this->contactService->get($id);

            if (!$contact) {
                http_response_code(404);
                echo json_encode(['error' => 'Contact not found.']);
                return;
            }

            $this->contactService->delete($id);

            http_response_code(200);
            echo json_encode(['message' => 'Contact deleted successfully.']);
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
            echo json_encode(['error' => 'Invalid contact ID.']);
            return;
        }

        try {
            $contact = $this->contactService->get($id);

            if (!$contact) {
                http_response_code(404);
                echo json_encode(['error' => 'Contact not found.']);
                return;
            }

            http_response_code(200);
            echo json_encode($contact);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function list(): void
    {
        try {
            $filter = $this->createFilterFromRequest();
            $result = $this->contactService->list($filter);

            http_response_code(200);
            echo json_encode($result);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    protected function createFilterFromRequest(): ContactFilter
    {
        $jsonData = file_get_contents('php://input');
        if (empty($jsonData)) {
            return new ContactFilter();
        }

        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        return ContactFilter::fromArray($data);
    }

    public function sendEmail(): void
    {
        $name = filter_var($_POST['name'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $subject = filter_var($_POST['subject'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $message = filter_var($_POST['message'], FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        if (!$name || !$email || !$subject || !$message) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing required fields.']);
            return;
        }

        try {
            $this->contactService->sendEmail($name, $email, $subject, $message);

            http_response_code(200);
            echo json_encode(['message' => 'Email sent successfully.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}