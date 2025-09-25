<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Program extends Model
{
    private string $name;
    private string $description;
    private string $bannerImg;
    private string $startDate;
    private string $endDate;
    private string $programUrl;
    private string $contactFirstName;
    private string $contactLastName;
    private string $contactTitle;
    private string $contactEmail;
    private string $contactPhone;
    private ProgramBeneficiary $programBeneficiary;
    private ProgramState $programState;
    private Country $country;
    private Agency $agency;
    private array $sdgs;
    private array $programDonors;

    public function __construct(
        string $name,
        string $description,
        string $bannerImg,
        string $startDate,
        string $endDate,
        string $programUrl,
        string $contactFirstName,
        string $contactLastName,
        string $contactTitle,
        string $contactEmail,
        string $contactPhone,
        ProgramBeneficiary $programBeneficiary,
        ProgramState $programState,
        Country $country,
        Agency $agency,
        array $sdgs,
        array $programDonors
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->bannerImg = $bannerImg;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->programUrl = $programUrl;
        $this->contactFirstName = $contactFirstName;
        $this->contactLastName = $contactLastName;
        $this->contactTitle = $contactTitle;
        $this->contactEmail = $contactEmail;
        $this->contactPhone = $contactPhone;
        $this->programBeneficiary = $programBeneficiary;
        $this->programState = $programState;
        $this->country = $country;
        $this->agency = $agency;
        $this->sdgs = $sdgs;
        $this->programDonors = $programDonors;
    }

    public static function at($name, $description, $bannerImg, $startDate, $endDate, $programUrl, $contactFirstName, $contactLastName, $contactTitle, $contactEmail, $contactPhone, $programBeneficiary, $programState, $country, $agency, $sdgs, $programDonors): Program
    {
        // Validaciones del nombre
        if (empty(trim($name))) {
            throw new RuntimeException('el nombre del programa no debe ir vacio');
        }
        if (strlen(trim($name)) < 3) {
            throw new RuntimeException('el nombre del programa debe tener al menos 3 caracteres');
        }
        if (strlen(trim($name)) > 255) {
            throw new RuntimeException('el nombre del programa no debe exceder 255 caracteres');
        }

        // Validaciones de la descripción
        if (empty(trim($description))) {
            throw new RuntimeException('la descripción del programa no debe ir vacio');
        }
        if (strlen(trim($description)) < 10) {
            throw new RuntimeException('la descripción del programa debe tener al menos 10 caracteres');
        }
        if (strlen(trim($description)) > 2000) {
            throw new RuntimeException('la descripción del programa no debe exceder 2000 caracteres');
        }

        // Validaciones del bannerImg
        if (empty(trim($bannerImg))) {
            throw new RuntimeException('la imagen banner del programa no debe ir vacio');
        }
        // Validar que sea una URL válida o path válido
        if (!filter_var($bannerImg, FILTER_VALIDATE_URL) && !preg_match('/^[a-zA-Z0-9\/_\-\.]+\.(jpg|jpeg|png|gif|webp)$/i', $bannerImg)) {
            throw new RuntimeException('la imagen banner debe ser una URL válida o un path de imagen válido');
        }

        // Validaciones de startDate
        if (empty(trim($startDate))) {
            throw new RuntimeException('la fecha de inicio del programa no debe ir vacio');
        }
        if (!self::isValidDate($startDate)) {
            throw new RuntimeException('la fecha de inicio debe tener formato válido (YYYY-MM-DD)');
        }
        // Validar que no sea una fecha en el pasado muy lejano (más de 10 años)
        $startDateTime = new \DateTime($startDate);
        $tenYearsAgo = new \DateTime('-10 years');
        if ($startDateTime < $tenYearsAgo) {
            throw new RuntimeException('la fecha de inicio no puede ser anterior a 10 años');
        }

        // Validaciones de endDate
        if (empty(trim($endDate))) {
            throw new RuntimeException('la fecha de fin del programa no debe ir vacio');
        }
        if (!self::isValidDate($endDate)) {
            throw new RuntimeException('la fecha de fin debe tener formato válido (YYYY-MM-DD)');
        }

        // DateTime para comparaciones más precisas
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        if ($endDateTime < $startDateTime) {
            throw new RuntimeException('la fecha de fin debe ser posterior a la fecha de inicio');
        }
        
        // Validar duración máxima razonable (ej: 20 años)
        $maxDuration = $startDateTime->add(new \DateInterval('P20Y'));
        if ($endDateTime > $maxDuration) {
            throw new RuntimeException('la duración del programa no puede exceder 20 años');
        }

        // Validaciones de programUrl
        if (empty(trim($programUrl))) {
            throw new RuntimeException('la URL del programa no debe ir vacio');
        }
        
        // Validación robusta de URL con filtros múltiples
        if (!filter_var($programUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('la URL del programa debe tener un formato válido');
        }
        
        // Validar que use HTTPS o HTTP solamente
        $parsedUrl = parse_url($programUrl);
        if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'], true)) {
            throw new RuntimeException('la URL del programa debe usar protocolo HTTP o HTTPS');
        }

        // Validaciones de contactFirstName
        if (empty(trim($contactFirstName))) {
            throw new RuntimeException('el nombre del contacto no debe ir vacio');
        }
        if (strlen(trim($contactFirstName)) < 2) {
            throw new RuntimeException('el nombre del contacto debe tener al menos 2 caracteres');
        }
        if (strlen(trim($contactFirstName)) > 50) {
            throw new RuntimeException('el nombre del contacto no debe exceder 50 caracteres');
        }
        // Validar que contenga solo letras, espacios y caracteres válidos para nombres
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', trim($contactFirstName))) {
            throw new RuntimeException('el nombre del contacto contiene caracteres no válidos');
        }

        // Validaciones de contactLastName
        if (empty(trim($contactLastName))) {
            throw new RuntimeException('el apellido del contacto no debe ir vacio');
        }
        if (strlen(trim($contactLastName)) < 2) {
            throw new RuntimeException('el apellido del contacto debe tener al menos 2 caracteres');
        }
        if (strlen(trim($contactLastName)) > 50) {
            throw new RuntimeException('el apellido del contacto no debe exceder 50 caracteres');
        }
        // Validar que contenga solo letras, espacios y caracteres válidos para apellidos
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', trim($contactLastName))) {
            throw new RuntimeException('el apellido del contacto contiene caracteres no válidos');
        }

        // Validaciones de contactTitle
        if (empty(trim($contactTitle))) {
            throw new RuntimeException('el título del contacto no debe ir vacio');
        }
        if (strlen(trim($contactTitle)) < 2) {
            throw new RuntimeException('el título del contacto debe tener al menos 2 caracteres');
        }
        if (strlen(trim($contactTitle)) > 100) {
            throw new RuntimeException('el título del contacto no debe exceder 100 caracteres');
        }
        // Validar formato de título profesional
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\.\,\/]+$/u', trim($contactTitle))) {
            throw new RuntimeException('el título del contacto contiene caracteres no válidos');
        }

        // Validaciones de contactEmail
        if (empty(trim($contactEmail))) {
            throw new RuntimeException('el email del contacto no debe ir vacio');
        }

        // validacion robusta con RFC
        $email = trim(strtolower($contactEmail));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            throw new RuntimeException('el email del contacto debe tener un formato válido');
        }
        
        if (strlen($email) > 254) { // RFC 5321 limit
            throw new RuntimeException('el email del contacto excede la longitud máxima permitida');
        }
        
        // Validar dominio 
        $domain = substr(strrchr($email, '@'), 1);
        if (empty($domain) || !checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            throw new RuntimeException('el dominio del email no es válido o no existe');
        }

        // Validaciones de contactPhone 
        if (empty(trim($contactPhone))) {
            throw new RuntimeException('el teléfono del contacto no debe ir vacio');
        }

        $phoneString = trim($contactPhone);

        // Acepta formatos: +1234567890, +12 345 678 9012, +1-234-567-8901
        if (!preg_match('/^(\+?\d{1,4})?[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{6,14}$/', $phoneString)) {
            throw new RuntimeException('el formato del teléfono no es válido - use formato internacional');
        }
        
        // Validar longitud total 
        $digitsOnly = preg_replace('/\D/', '', $phoneString);
        if (strlen($digitsOnly) < 7) {
            throw new RuntimeException('el teléfono debe tener al menos 7 dígitos');
        }
        if (strlen($digitsOnly) > 15) {
            throw new RuntimeException('el teléfono no debe exceder 15 dígitos');
        }

        // Validaciones de programBeneficiary
        if ($programBeneficiary === null) {
            throw new RuntimeException('el beneficiario del programa no debe ser null');
        }
        if (!($programBeneficiary instanceof ProgramBeneficiary)) {
            throw new RuntimeException('el beneficiario debe ser una instancia de ProgramBeneficiary');
        }

        // Validaciones de programState
        if ($programState === null) {
            throw new RuntimeException('el estado del programa no debe ser null');
        }
        if (!($programState instanceof ProgramState)) {
            throw new RuntimeException('el estado debe ser una instancia de ProgramState');
        }

        // Validaciones de country
        if ($country === null) {
            throw new RuntimeException('el país del programa no debe ser null');
        }
        if (!($country instanceof Country)) {
            throw new RuntimeException('el país debe ser una instancia de Country');
        }

        // Validaciones de agency
        if ($agency === null) {
            throw new RuntimeException('la agencia del programa no debe ser null');
        }
        if (!($agency instanceof Agency)) {
            throw new RuntimeException('la agencia debe ser una instancia de Agency');
        }

        // Validaciones de sdgs
        if ($sdgs === null) {
            throw new RuntimeException('los SDGs del programa no deben ser null');
        }
        if (!is_array($sdgs)) {
            throw new RuntimeException('los SDGs deben ser un array');
        }
        if (empty($sdgs)) {
            throw new RuntimeException('debe seleccionar al menos un SDG');
        }
        foreach ($sdgs as $sdg) {
            if (!($sdg instanceof Sdg)) {
                throw new RuntimeException('todos los SDGs deben ser instancias de Sdg');
            }
        }

        // Validaciones de programDonors
        if ($programDonors === null) {
            throw new RuntimeException('los donantes del programa no deben ser null');
        }
        if (!is_array($programDonors)) {
            throw new RuntimeException('los donantes deben ser un array');
        }
        if (empty($programDonors)) {
            throw new RuntimeException('debe seleccionar al menos un donante');
        }
        foreach ($programDonors as $donor) {
            if (!($donor instanceof Donor)) {
                throw new RuntimeException('todos los donantes deben ser instancias de Donor');
            }
        }

        return new Program(
            $name,
            $description,
            $bannerImg,
            $startDate,
            $endDate,
            $programUrl,
            $contactFirstName,
            $contactLastName,
            $contactTitle,
            $contactEmail,
            $contactPhone,
            $programBeneficiary,
            $programState,
            $country,
            $agency,
            $sdgs,
            $programDonors
        );
    }

    
    private static function isValidDate(string $date): bool
    {
        $date = trim($date);
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        $errors = \DateTime::getLastErrors();
        
        if ($errors && ($errors['error_count'] > 0 || $errors['warning_count'] > 0)) {
            return false;
        }
        
        return $dateTime && $dateTime->format('Y-m-d') === $date;
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
        return $this->bannerImg;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getProgramUrl(): string
    {
        return $this->programUrl;
    }

    public function getContactFirstName(): string
    {
        return $this->contactFirstName;
    }

    public function getContactLastName(): string
    {
        return $this->contactLastName;
    }

    public function getContactTitle(): string
    {
        return $this->contactTitle;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function getContactPhone(): string
    {
        return $this->contactPhone;
    }

    public function getProgramBeneficiary(): ProgramBeneficiary
    {
        return $this->programBeneficiary;
    }

    public function getProgramState(): ProgramState
    {
        return $this->programState;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getAgency(): Agency
    {
        return $this->agency;
    }

    public function getSdgs(): array
    {
        return $this->sdgs;
    }

    public function getProgramDonors(): array
    {
        return $this->programDonors;
    }
}
