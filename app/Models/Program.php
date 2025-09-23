<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Program extends Model
{
    private $name;
    private $description;
    private $banner_img;
    private $start_date;
    private $end_date;
    private $program_url;
    private $contact_first_name;
    private $contact_last_name;
    private $contact_title;
    private $contact_email;
    private $contact_phone;

    public function __construct(
        string $name,
        string $description,
        string $banner_img,
        string $start_date,
        string $end_date,
        string $program_url,
        string $contact_first_name,
        string $contact_last_name,
        string $contact_title,
        string $contact_email,
        int $contact_phone
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->banner_img = $banner_img;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->program_url = $program_url;
        $this->contact_first_name = $contact_first_name;
        $this->contact_last_name = $contact_last_name;
        $this->contact_title = $contact_title;
        $this->contact_email = $contact_email;
        $this->contact_phone = $contact_phone;
    }

    public static function at($name, $description, $banner_img, $start_date, $end_date, $program_url, $contact_first_name, $contact_last_name, $contact_title, $contact_email, $contact_phone): Program
    {
        // Validaciones del nombre
        if ($name === null) {
            throw new RuntimeException('el nombre del programa no debe ser null');
        }
        if (strlen((string)$name) == 0) {
            throw new RuntimeException('el nombre del programa no debe ir vacio');
        }

        // Validaciones de la descripción
        if ($description === null) {
            throw new RuntimeException('la descripción del programa no debe ser null');
        }
        if (strlen((string)$description) == 0) {
            throw new RuntimeException('la descripción del programa no debe ir vacio');
        }

        // Validaciones del banner_img
        if ($banner_img === null) {
            throw new RuntimeException('la imagen banner del programa no debe ser null');
        }
        if (strlen((string)$banner_img) == 0) {
            throw new RuntimeException('la imagen banner del programa no debe ir vacio');
        }

        // Validaciones de start_date
        if ($start_date === null) {
            throw new RuntimeException('la fecha de inicio del programa no debe ser null');
        }
        if (strlen((string)$start_date) == 0) {
            throw new RuntimeException('la fecha de inicio del programa no debe ir vacio');
        }
        if (!self::isValidDate($start_date)) {
            throw new RuntimeException('la fecha de inicio debe tener formato válido (YYYY-MM-DD)');
        }

        // Validaciones de end_date
        if ($end_date === null) {
            throw new RuntimeException('la fecha de fin del programa no debe ser null');
        }
        if (strlen((string)$end_date) == 0) {
            throw new RuntimeException('la fecha de fin del programa no debe ir vacio');
        }
        if (!self::isValidDate($end_date)) {
            throw new RuntimeException('la fecha de fin debe tener formato válido (YYYY-MM-DD)');
        }
        if (strtotime($end_date) <= strtotime($start_date)) {
            throw new RuntimeException('la fecha de fin debe ser posterior a la fecha de inicio');
        }

        // Validaciones de program_url
        if ($program_url === null) {
            throw new RuntimeException('la URL del programa no debe ser null');
        }
        if (strlen((string)$program_url) == 0) {
            throw new RuntimeException('la URL del programa no debe ir vacio');
        }
        if (!filter_var($program_url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('la URL del programa debe tener un formato válido');
        }

        // Validaciones de contact_first_name
        if ($contact_first_name === null) {
            throw new RuntimeException('el nombre del contacto no debe ser null');
        }
        if (strlen((string)$contact_first_name) == 0) {
            throw new RuntimeException('el nombre del contacto no debe ir vacio');
        }
        if (strlen($contact_first_name) < 2) {
            throw new RuntimeException('el nombre del contacto debe tener al menos 2 caracteres');
        }

        // Validaciones de contact_last_name
        if ($contact_last_name === null) {
            throw new RuntimeException('el apellido del contacto no debe ser null');
        }
        if (strlen((string)$contact_last_name) == 0) {
            throw new RuntimeException('el apellido del contacto no debe ir vacio');
        }
        if (strlen($contact_last_name) < 2) {
            throw new RuntimeException('el apellido del contacto debe tener al menos 2 caracteres');
        }

        // Validaciones de contact_title
        if ($contact_title === null) {
            throw new RuntimeException('el título del contacto no debe ser null');
        }
        if (strlen((string)$contact_title) == 0) {
            throw new RuntimeException('el título del contacto no debe ir vacio');
        }

        // Validaciones de contact_email
        if ($contact_email === null) {
            throw new RuntimeException('el email del contacto no debe ser null');
        }
        if (strlen((string)$contact_email) == 0) {
            throw new RuntimeException('el email del contacto no debe ir vacio');
        }
        if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('el email del contacto debe tener un formato válido');
        }

        // Validaciones de contact_phone
        if ($contact_phone === null) {
            throw new RuntimeException('el teléfono del contacto no debe ser null');
        }
        if ($contact_phone === 0) {
            throw new RuntimeException('el teléfono del contacto es requerido');
        }
        if ($contact_phone < 0) {
            throw new RuntimeException('el teléfono del contacto no debe ser negativo');
        }
        if (strlen((string)$contact_phone) < 7) {
            throw new RuntimeException('el teléfono del contacto debe tener al menos 7 dígitos');
        }

        return new Program(
            $name,
            $description,
            $banner_img,
            $start_date,
            $end_date,
            $program_url,
            $contact_first_name,
            $contact_last_name,
            $contact_title,
            $contact_email,
            $contact_phone
        );
    }

    private static function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getBannerImg(): string
    {
        return $this->banner_img;
    }

    public function getStartDate(): string
    {
        return $this->start_date;
    }

    public function getEndDate(): string
    {
        return $this->end_date;
    }

    public function getProgramUrl(): string
    {
        return $this->program_url;
    }

    public function getContactFirstName(): string
    {
        return $this->contact_first_name;
    }

    public function getContactLastName(): string
    {
        return $this->contact_last_name;
    }

    public function getContactTitle(): string
    {
        return $this->contact_title;
    }

    public function getContactEmail(): string
    {
        return $this->contact_email;
    }

    public function getContactPhone(): int
    {
        return $this->contact_phone;
    }
}
