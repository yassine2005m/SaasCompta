<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\AccountingEntry;
use App\Services\SageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    protected $sageService;

    public function __construct(SageService $sageService)
    {
        $this->sageService = $sageService;
    }

    public function index()
    {
        return view('pages.export.index');
    }

    /**
     * Export Clients to CSV (Excel compatible)
     */
    public function exportClients()
    {
        $clients = Client::with('company')->get();
        $fileName = 'clients_export_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function () use ($clients) {
            $handle = fopen('php://output', 'w');
            // Adding BOM for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($handle, ['ID', 'Nom', 'Prénom', 'CIN', 'Téléphone', 'Email', 'Adresse', 'Entreprise', 'ICE', 'RC', 'IF'], ';');

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->id,
                    $client->last_name,
                    $client->first_name,
                    $client->cin,
                    $client->phone,
                    $client->email,
                    $client->address,
                    $client->company->company_name ?? '-',
                    $client->company->ice ?? '-',
                    $client->company->rc ?? '-',
                    $client->company->if ?? '-',
                ], ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    /**
     * Export Contracts to CSV (Excel compatible)
     */
    public function exportContracts()
    {
        $contracts = Contract::with('client.company')->get();
        $fileName = 'contracts_export_' . date('Y-m-d') . '.csv';

        $response = new StreamedResponse(function () use ($contracts) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($handle, ['ID', 'Client', 'Entreprise', 'Type', 'Date Début', 'Date Fin', 'Durée (Mois)', 'Prix (MAD)', 'Status'], ';');

            foreach ($contracts as $contract) {
                fputcsv($handle, [
                    $contract->id,
                    $contract->client->first_name . ' ' . $contract->client->last_name,
                    $contract->client->company->company_name ?? '-',
                    $contract->type,
                    $contract->start_date ? $contract->start_date->format('d/m/Y') : '-',
                    $contract->end_date ? $contract->end_date->format('d/m/Y') : '-',
                    $contract->duration,
                    $contract->price,
                    $contract->status,
                ], ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    /**
     * Export Accounting Entries to Sage-compatible Semicolon Format
     */
    public function exportInvoicesTxt()
    {
        $fileName = 'IMPORT_SAGE_' . date('Ymd') . '.txt';
        return response($this->sageService->generateSageContent())
            ->header('Content-Type', 'text/plain; charset=windows-1252')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Direct Sync: Write Sage Export file directly to local disk
     */
    public function directSyncSage()
    {
        $result = $this->sageService->syncNow();

        if ($result['success']) {
            return back()->with('success', "Synchronisation réussie ! Fichier déposé dans : " . $result['path']);
        } else {
            return back()->with('error', "Erreur de synchronisation locale : " . $result['error']);
        }
    }

    /**
     * Export Journal to CSV (Excel compatible)
    */
    public function exportJournalExcel()
    {
        $entries = AccountingEntry::with('invoice.contract.client.company')->orderBy('date', 'asc')->get();
        $fileName = 'EXPORT_EXCEL_' . date('Ymd') . '.csv';

        $response = new StreamedResponse(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            
            // Mirroring the Sage 9-column format for Excel reconciliation
            // Header for clarity in Excel
            fputcsv($handle, [
                'Journal', 'Date', 'Piece', 'CompteG', 'CompteT', 'Libelle', 'Echeance', 'Debit', 'Credit'
            ], ';');

            foreach ($entries as $entry) {
                $journal = 'VTE';
                $dateString = $entry->date ? $entry->date->format('d/m/Y') : date('d/m/Y');
                $piece = $entry->invoice->invoice_number ?? 'FAC';
                $acc = str_pad($entry->account_number, 8, '0', STR_PAD_RIGHT);
                $tier = $entry->third_party_account ?? '';
                $label = $entry->label;
                
                $dueDate = ($entry->account_number == '34210000') 
                    ? ($entry->invoice->date ? $entry->invoice->date->format('d/m/Y') : date('d/m/Y')) : '';
                
                $debit = number_format((float)$entry->debit, 2, ',', '');
                $credit = number_format((float)$entry->credit, 2, ',', '');

                fputcsv($handle, [
                    $journal, $dateString, $piece, $acc, $tier, $label, $dueDate, $debit, $credit
                ], ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    /**
     * API for Automated Sync: Returns Sage content (Token Protected)
     */
    public function apiSageSync(Request $request)
    {
        $token = $request->query('token');
        $validToken = config('services.sage.sync_token', env('SAGE_SYNC_TOKEN', 'sage_sync_protected_token_2026'));

        if ($token !== $validToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response($this->sageService->generateSageContent())
            ->header('Content-Type', 'text/plain; charset=windows-1252');
    }

    /**
     * API: Acknowledge sync from Cloud Agent (Token Protected)
     */
    public function apiSyncAck(Request $request)
    {
        $token = $request->query('token');
        $validToken = config('services.sage.sync_token', env('SAGE_SYNC_TOKEN', 'sage_sync_protected_token_2026'));

        if ($token !== $validToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Store last sync info in a simple file (no extra DB table needed)
        $syncInfo = [
            'last_sync_at' => now()->toDateTimeString(),
            'agent_ip' => $request->ip(),
            'entries_size' => $request->query('entries', 0),
            'status' => 'success'
        ];

        $syncLogPath = storage_path('app/sage_sync_log.json');
        
        // Load existing logs
        $logs = [];
        if (file_exists($syncLogPath)) {
            $logs = json_decode(file_get_contents($syncLogPath), true) ?? [];
        }
        
        // Keep only last 50 sync entries
        $logs[] = $syncInfo;
        if (count($logs) > 50) {
            $logs = array_slice($logs, -50);
        }
        
        file_put_contents($syncLogPath, json_encode($logs, JSON_PRETTY_PRINT));

        return response()->json(['status' => 'ok', 'message' => 'Sync acknowledged']);
    }

    /**
     * API: Get last sync status (for dashboard display)
     */
    public function apiSyncStatus(Request $request)
    {
        $token = $request->query('token');
        $validToken = config('services.sage.sync_token', env('SAGE_SYNC_TOKEN', 'sage_sync_protected_token_2026'));

        if ($token !== $validToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $syncLogPath = storage_path('app/sage_sync_log.json');
        
        if (!file_exists($syncLogPath)) {
            return response()->json([
                'last_sync' => null,
                'total_syncs' => 0,
                'message' => 'Aucune synchronisation enregistrée'
            ]);
        }

        $logs = json_decode(file_get_contents($syncLogPath), true) ?? [];
        $lastSync = end($logs);

        return response()->json([
            'last_sync' => $lastSync,
            'total_syncs' => count($logs),
            'message' => 'OK'
        ]);
    }
}
