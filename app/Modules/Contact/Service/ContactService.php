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

        $contact->fill($validated->toArray());
        $this->contactRepository->save($contact);

        return $contact;
    }

    public function findContactByEmail(string $email): Contact
    {
        return $this->contactRepository->findBy('email', trim($email));
    }
}