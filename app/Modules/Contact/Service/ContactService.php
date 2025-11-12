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
        // Verificar si ya existe un contacto con el mismo email
        if($this->contactRepository->exists('email', trim($email))){
            throw new \RuntimeException("Ya existe un contacto con el email: {$email}");
        }

        $contact = Contact::at($firstName, $lastName, $title, $email, $phone);
        $this->contactRepository->save($contact);
        return $contact;
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
        string $phone = ""
    ): Contact {
        // Obtener el modelo existente
        $contact = $this->contactRepository->findById($id);

        // Validar y normalizar con Contact::at
        $validated = Contact::at($firstName, $lastName, $title, $email, $phone);

        // Comprobar duplicado de email (si existe y no es este contacto)
        try {
            $existing = $this->contactRepository->findBy('email', $validated->email);
            if ($existing && $existing->id !== $contact->id) {
                throw new \RuntimeException("Ya existe otro contacto con el email: {$validated->email}");
            }
        } catch (\RuntimeException $e) {
            // findBy lanza RuntimeException cuando no encuentra la entidad; ignorar ese caso
            if (stripos($e->getMessage(), 'no encontrado') === false) {
                throw $e;
            }
        }

        $contact->fill($validated->toArray());
        $this->contactRepository->save($contact);

        return $contact;
    }

    public function findContactByEmail(string $email): Contact
    {
        return $this->contactRepository->findBy('email', trim($email));
    }
}