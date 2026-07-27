<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Handle incoming chatbot messages using local knowledge base.
     */
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $text = Str::lower($request->input('message'));
        $response = $this->getExpertResponse($text);

        // Simulate a small delay for "thinking"
        usleep(500000); 

        return response()->json(['response' => $response]);
    }

    /**
     * Local Knowledge Base Logic
     */
    private function getExpertResponse($text)
    {
        // 1. GREETINGS
        if (Str::contains($text, ['bonjour', 'salut', 'hello', 'coucou'])) {
            return "Bonjour ! Je suis l'assistant virtuel d'Universal Invest Strategy. Je peux vous aider avec la gestion de vos contrats, de la facturation, de la synchronisation Sage et de la domiciliation. Que souhaitez-vous savoir ?";
        }

        // 2. CREATION DE CONTRAT & PROCESSUS
        if (Str::contains($text, ['créer un contrat', 'nouveau contrat', 'faire un contrat', 'demande'])) {
            return "Pour créer un contrat : \n1. Si vous êtes client, allez dans 'Faire une demande'. \n2. Si vous êtes admin, allez dans 'Contrats > Créer' ou validez une demande client en attente. \nLe document sera automatiquement généré en PDF et Word avec les bons textes et le design officiel de M. Bachra.";
        }

        // 3. RENOUVELLEMENT & EXPIRATION
        if (Str::contains($text, ['renouveler', 'renouvellement', 'expire', 'expiration', 'fin'])) {
            return "Lorsqu'un contrat approche de sa fin, l'admin peut cliquer sur le bouton 'Renouveler' sur la page du contrat. Cela créera un nouveau contrat avec de nouvelles dates (généralement +1 an) et générera la facture correspondante.";
        }

        // 4. FACTURATION ET PRIX
        if (Str::contains($text, ['prix', 'tarif', 'coût', 'combien', '165', 'facture', 'payer', 'payée'])) {
            return "Le tarif standard de la domiciliation est de 165 DH par mois. Les factures sont générées automatiquement à la création ou au renouvellement du contrat. Vous pouvez changer leur statut en 'Payée' depuis l'onglet Factures.";
        }

        // 5. SYNCHRONISATION SAGE 100
        if (Str::contains($text, ['sage', 'synchroniser', 'export', 'comptabilité', 'journal'])) {
            return "L'intégration avec Sage 100 est 100% locale. L'export Sage génère automatiquement des fichiers TXT formatés (PNM) dans le dossier 'C:\Sage_Import'. Allez dans 'Export Sage' pour lancer la synchronisation directe ou télécharger les journaux Excel.";
        }

        // 6. DOCUMENTS ET ATTESTATION
        if (Str::contains($text, ['attestation', 'pdf', 'word', 'télécharger', 'document', 'imprimer'])) {
            return "Les attestations de domiciliation et les contrats sont générés dynamiquement. Allez sur la page d'un contrat, et vous verrez les boutons pour télécharger la version PDF ou Word. Le format respecte le modèle officiel.";
        }

        // 7. GESTION DES CLIENTS
        if (Str::contains($text, ['client', 'ice', 'entreprise', 'rc', 'if', 'supprimer client'])) {
            return "La section 'Clients' permet de voir tous les domiciliés. Vous y trouverez leurs numéros légaux (ICE, RC, IF) liés à leur entreprise. Vous pouvez également supprimer un client (ce qui supprimera ses factures et contrats).";
        }

        // 8. LIVRAISON DU PROJET & UTILISATION LOCALE
        if (Str::contains($text, ['livrer', 'livraison', 'déployer', 'local', 'installation', 'demarrer'])) {
            return "Puisque l'automatisation Sage est locale (C:\Sage_Import), le projet peut être utilisé facilement avec un script de démarrage (ex: start_saas.bat) fourni par votre développeur. Il lance le serveur localement et ouvre l'application dans votre navigateur sans besoin de cloud !";
        }

        // DEFAULT RESPONSE
        return "Désolé, je ne suis pas sûr d'avoir bien compris. Je suis programmé pour répondre aux questions sur : les contrats, les factures, la tarification (165 DH/mois), l'exportation Sage 100, et la gestion des clients. Pouvez-vous reformuler votre question sur l'un de ces sujets ?";
    }
}
