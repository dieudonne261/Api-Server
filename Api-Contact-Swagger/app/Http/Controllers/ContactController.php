<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Telephone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth",
 * )
 */
class ContactController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/contacts",
     *     summary="Obtenir tous les contacts de l'utilisateur",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Liste des contacts récupérée avec succès")
     * )
     */
    public function getAll()
    {
        $contacts = Auth::user()->contacts()->with('telephones')->get();
        return response()->json(['message' => 'Liste des contacts récupérée avec succès', 'contacts' => $contacts], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/contacts",
     *     summary="Créer un nouveau contact",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","telephones"},
     *             @OA\Property(property="avatar", type="string", example="red"),
     *             @OA\Property(property="nom", type="string", example="Koto"),
     *             @OA\Property(property="telephones", type="array", @OA\Items(type="string", example="0341272276"))
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Contact créé avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/api/contacts/{id}",
     *     summary="Obtenir un contact par ID",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response=200, description="Contact récupéré avec succès"),
     *     @OA\Response(response=404, description="Contact non trouvé")
     * )
     */
    public function getContactById($id)
    {
        try {
            $contact = Auth::user()->contacts()->with('telephones')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
        }

        return response()->json(['message' => 'Contact récupéré avec succès', 'contact' => $contact], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/contacts/{id}",
     *     summary="Mettre à jour un contact par ID",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom","telephones"},
     *             @OA\Property(property="avatar", type="string", example="red"),
     *             @OA\Property(property="nom", type="string", example="Koto 2"),
     *             @OA\Property(property="telephones", type="array", @OA\Items(type="string", example="0341272276"))
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Contact mis à jour avec succès"),
     *     @OA\Response(response=404, description="Contact non trouvé")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/contacts/{id}",
     *     summary="Supprimer un contact par ID",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response=200, description="Contact supprimé avec succès"),
     *     @OA\Response(response=404, description="Contact non trouvé")
     * )
     */
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