<?php
namespace App\Modules\Contact\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Contact\Service\ContactService;
use Illuminate\Http\Request;

class ContactController extends Controller{
    private ContactService $contactService;
    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * list all contacts
     */
    public function index(){
        try {
            $contacts = $this->contactService->getAllContacts();
            return response()->json([
                'status' => 'success',
                'data' => $contacts
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /*
    * create a new contact
    */
    public function store(Request $request){
        try {   
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'title' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
            ]);
            $contact = $this->contactService->createContact(
                $request->input('first_name'),
                $request->input('last_name'),
                $request->input('title'),
                $request->input('email'),
                $request->input('phone')
            );
            return response()->json([
                'status' => 'success',
                'data' => $contact
            ], 201);    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, int $id){
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'title' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);
        try {
            $contact = $this->contactService->updateContact(
                $id,
                $request->input('first_name'),
                $request->input('last_name'),
                $request->input('title'),
                $request->input('email'),
                $request->input('phone')
            );
            return response()->json([
                'status' => 'success',
                'data' => $contact
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
    /*
     *show a contact by id
    */
     public function show(int $id){
        try {
            $contact = $this->contactService->getContactById($id);
            return response()->json([
                'status' => 'success',
                'data' => $contact
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}