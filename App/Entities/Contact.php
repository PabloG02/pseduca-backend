<?php

namespace App\Entities;

use ValueError;

class Contact
{
    public int $id;
    public string $address {
        get => $this->address;
        set {
            if (strlen($value) < 2) {
                throw new ValueError('Address must be at least 2 characters long.');
            }
            if (strlen($value) > 255) {
                throw new ValueError('Address cannot be more than 255 characters long.');
            }
            $this->address = $value;
        }
    }
    public string $email {
        get => $this->email;
        set {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new ValueError('Invalid email format.');
            }
            if (strlen($value) > 100) {
                throw new ValueError('Email cannot be more than 100 characters long.');
            }
            $this->email = $value;
        }
    }
    public string $phone {
        get => $this->phone;
        set {
            if (strlen($value) > 20) {
                throw new ValueError('Phone number cannot be more than 20 characters long.');
            }
            // Basic phone format validation
            if (!preg_match('/^\d{9}$/', $value)) {
                throw new ValueError('Invalid phone number format.');
            }
            $this->phone = $value;
        }
    }
    public string $googleMapsEmbedUrl {
        get => $this->googleMapsEmbedUrl;
        set {
            if (!str_starts_with($value, 'https://www.google.com/maps/embed?')) {
                throw new ValueError('Invalid Google Maps embed URL format.');
            }
            if (strlen($value) > 511) {
                throw new ValueError('Google Maps embed URL cannot be more than 511 characters long.');
            }
            $this->googleMapsEmbedUrl = $value;
        }
    }

    public function __construct(
        int $id,
        string $address,
        string $email,
        string $phone,
        string $googleMapsEmbedUrl
    ) {
        $this->id = $id;
        $this->address = $address;
        $this->email = $email;
        $this->phone = $phone;
        $this->googleMapsEmbedUrl = $googleMapsEmbedUrl;
    }
}
