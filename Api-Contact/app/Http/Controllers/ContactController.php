<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Telephone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function getAll()
    {
        $contacts = Auth::user()->contacts()->with('telephones')->get();
        return response()->json($contacts);
    }

    public function createContact(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|string',
            'nom' => 'required|string|max:255',
            'telephones' => 'required|array',
            'telephones.*' => 'required|string|max:255',
        ]);

        $contact = Contact::create([
            'avatar' => $request->avatar,
            'nom' => $request->nom,
            'user_id' => Auth::id(),
        ]);

        foreach ($request->telephones as $numero) {
            Telephone::create([
                'numero' => $numero,
                'contact_id' => $contact->id,
            ]);
        }

        return response()->json($contact->load('telephones'), 201);
    }

    public function getContactById($id)
    {
        $contact = Auth::user()->contacts()->with('telephones')->find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact non trouver'], 404);
        }

        return response()->json($contact);
    }

    public function updateContact(Request $request, $id)
    {
        $contact = Auth::user()->contacts()->find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact non trouver'], 404);
        }

        $request->validate([
            'avatar' => 'nullable|string',
            'nom' => 'required|string|max:255',
            'telephones' => 'required|array',
            'telephones.*' => 'required|string|max:255',
        ]);

        $contact->update([
            'avatar' => $request->avatar,
            'nom' => $request->nom,
        ]);

        $contact->telephones()->delete();

        foreach ($request->telephones as $numero) {
            Telephone::create([
                'numero' => $numero,
                'contact_id' => $contact->id,
            ]);
        }

        return response()->json($contact->load('telephones'));
    }

    public function deleteContact($id)
    {
        $contact = Auth::user()->contacts()->find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact non trouver'], 404);
        }

        $contact->delete();

        return response()->json(['message' => 'Contact supprimer avec succes']);
    }
}