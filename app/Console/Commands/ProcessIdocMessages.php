<?php

namespace App\Console\Commands;

use App\Models\IdocMessage;
use App\Jobs\ProcessIdocMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessIdocMessages extends Command
{
    protected $signature = 'idoc:process {--limit=50}';
    protected $description = 'Procesează mesajele IDOC în așteptare';

    public function handle()
    {
        $limit = $this->option('limit');

        $this->info("Începe procesarea mesajelor IDOC...");

        try {
            // Găsim mesajele în așteptare
            $messages = IdocMessage::where('status', 'pending')
                ->orderBy('created_at')
                ->limit($limit)
                ->get();

            $count = $messages->count();
            $this->info("S-au găsit {$count} mesaje de procesat.");

            // Procesăm fiecare mesaj
            foreach ($messages as $message) {
                $this->line("Procesez mesajul #{$message->id} de tip {$message->type}...");

                try {
                    // Trimitem mesajul în queue pentru procesare
                    ProcessIdocMessage::dispatch($message);
                    $this->info("Mesajul #{$message->id} a fost trimis pentru procesare.");

                } catch (\Exception $e) {
                    $this->error("Eroare la procesarea mesajului #{$message->id}: " . $e->getMessage());
                    Log::error('Eroare la procesarea IDOC', [
                        'message_id' => $message->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->info("Procesare finalizată.");

        } catch (\Exception $e) {
            $this->error("Eroare generală: " . $e->getMessage());
            Log::error('Eroare la comanda de procesare IDOC', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }

        return 0;
    }
} 