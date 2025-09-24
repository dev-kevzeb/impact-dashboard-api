<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Program extends Model
{
    private string $name;
    private string $description;
    private string $banner_img;
    private string $start_date;
    private string $end_date;
    private string $program_url;
    private string $contact_first_name;
    private string $contact_last_name;
    private string $contact_title;
    private string $contact_email;
    private string $contact_phone;
    private ProgramBeneficiary $program_beneficiary;
    private ProgramState $program_state;
    private Country $country;
    private Agency $agency;
    private array $sdgs;
    private array $program_donors;

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
        string $contact_phone,
        ProgramBeneficiary $program_beneficiary,
        ProgramState $program_state,
        Country $country,
        Agency $agency,
        array $sdgs,
        array $program_donors
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
        $this->program_beneficiary = $program_beneficiary;
        $this->program_state = $program_state;
        $this->country = $country;
        $this->agency = $agency;
        $this->sdgs = $sdgs;
        $this->program_donors = $program_donors;
    }

    public static function at($name, $description, $banner_img, $start_date, $end_date, $program_url, $contact_first_name, $contact_last_name, $contact_title, $contact_email, $contact_phone, $program_beneficiary, $program_state, $country, $agency, $sdgs, $program_donors): Program
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

        // Validaciones del banner_img
        if (empty(trim($banner_img))) {
            throw new RuntimeException('la imagen banner del programa no debe ir vacio');
        }
        // Validar que sea una URL válida o path válido
        if (!filter_var($banner_img, FILTER_VALIDATE_URL) && !preg_match('/^[a-zA-Z0-9\/_\-\.]+\.(jpg|jpeg|png|gif|webp)$/i', $banner_img)) {
            throw new RuntimeException('la imagen banner debe ser una URL válida o un path de imagen válido');
        }

        // Validaciones de start_date
        if (empty(trim($start_date))) {
            throw new RuntimeException('la fecha de inicio del programa no debe ir vacio');
        }
        if (!self::isValidDate($start_date)) {
            throw new RuntimeException('la fecha de inicio debe tener formato válido (YYYY-MM-DD)');
        }
        // Validar que no sea una fecha en el pasado muy lejano (más de 10 años)
        $startDateTime = new \DateTime($start_date);
        $tenYearsAgo = new \DateTime('-10 years');
        if ($startDateTime < $tenYearsAgo) {
            throw new RuntimeException('la fecha de inicio no puede ser anterior a 10 años');
        }

        // Validaciones de end_date
        if (empty(trim($end_date))) {
            throw new RuntimeException('la fecha de fin del programa no debe ir vacio');
        }
        if (!self::isValidDate($end_date)) {
            throw new RuntimeException('la fecha de fin debe tener formato válido (YYYY-MM-DD)');
        }
        
        // DateTime para comparaciones más precisas 
        $startDateTime = new \DateTime($start_date);
        $endDateTime = new \DateTime($end_date);
        
        if ($endDateTime <= $startDateTime) {
            throw new RuntimeException('la fecha de fin debe ser posterior a la fecha de inicio');
        }
        
        // Validar duración máxima razonable (ej: 20 años)
        $maxDuration = $startDateTime->add(new \DateInterval('P20Y'));
        if ($endDateTime > $maxDuration) {
            throw new RuntimeException('la duración del programa no puede exceder 20 años');
        }

        // Validaciones de program_url 
        if (empty(trim($program_url))) {
            throw new RuntimeException('la URL del programa no debe ir vacio');
        }
        
        // Validación robusta de URL con filtros múltiples
        if (!filter_var($program_url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('la URL del programa debe tener un formato válido');
        }
        
        // Validar que use HTTPS o HTTP solamente
        $parsedUrl = parse_url($program_url);
        if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'], true)) {
            throw new RuntimeException('la URL del programa debe usar protocolo HTTP o HTTPS');
        }

        // Validaciones de contact_first_name 
        if (empty(trim($contact_first_name))) {
            throw new RuntimeException('el nombre del contacto no debe ir vacio');
        }
        if (strlen(trim($contact_first_name)) < 2) {
            throw new RuntimeException('el nombre del contacto debe tener al menos 2 caracteres');
        }
        if (strlen(trim($contact_first_name)) > 50) {
            throw new RuntimeException('el nombre del contacto no debe exceder 50 caracteres');
        }
        // Validar que contenga solo letras, espacios y caracteres válidos para nombres
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', trim($contact_first_name))) {
            throw new RuntimeException('el nombre del contacto contiene caracteres no válidos');
        }

        // Validaciones de contact_last_name 
        if (empty(trim($contact_last_name))) {
            throw new RuntimeException('el apellido del contacto no debe ir vacio');
        }
        if (strlen(trim($contact_last_name)) < 2) {
            throw new RuntimeException('el apellido del contacto debe tener al menos 2 caracteres');
        }
        if (strlen(trim($contact_last_name)) > 50) {
            throw new RuntimeException('el apellido del contacto no debe exceder 50 caracteres');
        }
        // Validar que contenga solo letras, espacios y caracteres válidos para apellidos
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\'\.]+$/u', trim($contact_last_name))) {
            throw new RuntimeException('el apellido del contacto contiene caracteres no válidos');
        }

        // Validaciones de contact_title 
        if (empty(trim($contact_title))) {
            throw new RuntimeException('el título del contacto no debe ir vacio');
        }
        if (strlen(trim($contact_title)) < 2) {
            throw new RuntimeException('el título del contacto debe tener al menos 2 caracteres');
        }
        if (strlen(trim($contact_title)) > 100) {
            throw new RuntimeException('el título del contacto no debe exceder 100 caracteres');
        }
        // Validar formato de título profesional
        if (!preg_match('/^[a-zA-ZÀ-ÿñÑ\s\-\.\,\/]+$/u', trim($contact_title))) {
            throw new RuntimeException('el título del contacto contiene caracteres no válidos');
        }

        // Validaciones de contact_email 
        if (empty(trim($contact_email))) {
            throw new RuntimeException('el email del contacto no debe ir vacio');
        }
        
        // validacion robusta con RFC 
        $email = trim(strtolower($contact_email));
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

        // Validaciones de contact_phone - Professional string validation
        if (empty(trim($contact_phone))) {
            throw new RuntimeException('el teléfono del contacto no debe ir vacio');
        }
        
        $phoneString = trim($contact_phone);
        
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

        // Validaciones de program_beneficiary
        if ($program_beneficiary === null) {
            throw new RuntimeException('el beneficiario del programa no debe ser null');
        }
        if (!($program_beneficiary instanceof ProgramBeneficiary)) {
            throw new RuntimeException('el beneficiario debe ser una instancia de ProgramBeneficiary');
        }

        // Validaciones de program_state
        if ($program_state === null) {
            throw new RuntimeException('el estado del programa no debe ser null');
        }
        if (!($program_state instanceof ProgramState)) {
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

        // Validaciones de program_donors
        if ($program_donors === null) {
            throw new RuntimeException('los donantes del programa no deben ser null');
        }
        if (!is_array($program_donors)) {
            throw new RuntimeException('los donantes deben ser un array');
        }
        if (empty($program_donors)) {
            throw new RuntimeException('debe seleccionar al menos un donante');
        }
        foreach ($program_donors as $donor) {
            if (!($donor instanceof Donor)) {
                throw new RuntimeException('todos los donantes deben ser instancias de Donor');
            }
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
            $contact_phone,
            $program_beneficiary,
            $program_state,
            $country,
            $agency,
            $sdgs,
            $program_donors
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

    public function getContactPhone(): string
    {
        return $this->contact_phone;
    }

    public function getProgramBeneficiary(): ProgramBeneficiary
    {
        return $this->program_beneficiary;
    }

    public function getProgramState(): ProgramState
    {
        return $this->program_state;
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
        return $this->program_donors;
    }
}
