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
        if($this->contactRepository->exists('email', trim($email))){
            throw new \RuntimeException("There is already a contact with the email: {$email}");
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
        $contact = $this->contactRepository->findById($id);
        $validated = Contact::at($firstName, $lastName, $title, $email, $phone);
        try {
            $existing = $this->contactRepository->findBy('email', $validated->email);
            if ($existing && $existing->id !== $contact->id) throw new \RuntimeException("There is already another contact with the email: {$validated->email}");
            
        } catch (\RuntimeException $e) {
            if (stripos($e->getMessage(), 'not found') === false) throw $e;
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