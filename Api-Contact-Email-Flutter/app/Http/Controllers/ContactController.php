<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Telephone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContactController extends Controller
{
    public function getAll()
    {
        $contacts = Auth::user()->contacts()->with('telephones')->get();
        return response()->json(['message' => 'Liste des contacts récupérée avec succès', 'contacts' => $contacts], 200);
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

        return response()->json(['message' => 'Contact créé avec succès', 'contact' => $contact->load('telephones')], 201);
    }

    public function getContactById($id)
    {
        try {
            $contact = Auth::user()->contacts()->with('telephones')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
        }

        return response()->json(['message' => 'Contact récupéré avec succès', 'contact' => $contact], 200);
    }

    public function updateContact(Request $request, $id)
    {
        try {
            $contact = Auth::user()->contacts()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
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

        return response()->json(['message' => 'Contact mis à jour avec succès', 'contact' => $contact->load('telephones')], 200);
    }

    public function deleteContact($id)
    {
        try {
            $contact = Auth::user()->contacts()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
        }

        $contact->delete();

        return response()->json(['message' => 'Contact supprimé avec succès'], 200);
    }
}