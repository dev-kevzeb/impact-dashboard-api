<?php
namespace App\Modules\Contact\Service;
use App\Modules\Contact\Domain\Contact;
use App\Modules\Contact\Repository\ContactRepository;

class ContactService {
    private ContactRepository $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }
    public function createContact(string $firstName, string $lastName, string $title, string $email, string $phone): Contact
    {
        // verificar si ya existe un contacto con el mismo email
        // damos por implicito esto porque no se deberia repetir emails en contactos
        // pero si el nombre o telefono pueden repetirse
        if($this->contactRepository->exists('email', trim($email))){
            throw new \RuntimeException("Ya existe un contacto con el email: {$email}");

        }
       if($firstName && $lastName && $email && $phone){
            $contact = Contact::at($firstName, $lastName, $title, $email, $phone);
            $this->contactRepository->save($contact);
            return $contact;
       } else {
            throw new \RuntimeException("Faltan datos obligatorios para crear el contacto.");

       }
    }
    public function getContactById(int $id): Contact
    {
        return $this->contactRepository->findById($id);
    }
  
    public function getAllContacts()
    {
        return $this->contactRepository->getAll();
    }
    public function updateContact(
    int $id, 
    string $firstName, 
    string $lastName, 
    string $title, 
    string $email, 
<<<<<<< HEAD
    string $phone
): Contact {
        $contact = $this->contactRepository->findById($id);
        $trimFirst = trim($firstName);
        $trimLast = trim($lastName);
        $trimTitle = trim($title);
        $emailNorm = mb_strtolower(trim($email), 'UTF-8');
        $trimPhone = trim($phone);
        if (!filter_var($emailNorm, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException("Email inválido: {$email}");
        }
        try {
            $existing = $this->contactRepository->findBy('email', $emailNorm);
            if ($existing && $existing->id !== $contact->id) {
                throw new \RuntimeException("Ya existe otro contacto con el email: {$emailNorm}");
            }
        } catch (\RuntimeException $e) {
=======
    string $phone = ""
): Contact {
        // 1) Obtener el modelo existente (lanza RuntimeException si no existe)
        $contact = $this->contactRepository->findById($id);

        // 2) Validar y normalizar con Contact::at (usarlo como fábrica/validador)
        //    No vamos a guardar la instancia devuelta, solo la usamos para obtener
        //    los atributos ya validados y normalizados.
        $validated = Contact::at($firstName, $lastName, $title, $email, $phone);

        // 3) Comprobar duplicado de email (si existe y no es este contacto)
        try {
            $existing = $this->contactRepository->findBy('email', $validated->email);
            if ($existing && $existing->id !== $contact->id) {
                throw new \RuntimeException("Ya existe otro contacto con el email: {$validated->email}");
            }
        } catch (\RuntimeException $e) {
            // findBy lanza RuntimeException cuando no encuentra la entidad; ignorar ese caso
>>>>>>> origin/develop
            if (stripos($e->getMessage(), 'no encontrado') === false) {
                throw $e;
            }
        }
<<<<<<< HEAD
        $firstNorm = mb_convert_case(mb_strtolower($trimFirst, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $lastNorm = mb_convert_case(mb_strtolower($trimLast, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $titleNorm = mb_convert_case(mb_strtolower($trimTitle, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $contact->first_name = $firstNorm;
        $contact->last_name  = $lastNorm;
        $contact->title      = $titleNorm;
        $contact->email      = $emailNorm;
        $contact->phone      = $trimPhone;
=======

        if (method_exists($contact, 'fill')) {
            $contact->fill($validated->toArray());
        } else {
            $contact->first_name = $validated->first_name;
            $contact->last_name  = $validated->last_name;
            $contact->title      = $validated->title;
            $contact->email      = $validated->email;
            $contact->phone      = $validated->phone;
        }
>>>>>>> origin/develop

        $this->contactRepository->save($contact);

        return $contact;
<<<<<<< HEAD
}
=======
    }
>>>>>>> origin/develop
private function validateContactData(string $firstName, string $email): void {
    if (empty($firstName)) {
        throw new \RuntimeException("El nombre es obligatorio");
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \RuntimeException("Email inválido");
    }
}
}