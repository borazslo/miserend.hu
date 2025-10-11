<?php

namespace Html\Calendar;

use Html\Calendar\Model\CalMass;
use Html\Calendar\Model\CalSuggestion;
use Html\Calendar\Model\CalSuggestionPackage;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Log;


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(200);
    exit();
}

header("Access-Control-Allow-Origin: *");

class Suggestions extends \Html\Calendar\CalendarApi
{
    private bool $modify;

    public function __construct($path)
    {
        if (empty($path[0])) {
            $this->sendJsonError('Nem megfelelő URL!', 400);
            exit;
        }

        //ekkor konkrét javaslat elfogadás/elutasítás érkezik
        $this->modify = in_array($path[0], ['accept', 'reject']);

        if (empty($path[1])) {
            $this->sendJsonError('Hiányzó templom azonosító.', 400);
            exit;
        }

        if (!$this->modify) {
            $this->tid = $path[1];

            $this->church = \Eloquent\Church::find($this->tid);
            if (!$this->church) {
                $this->sendJsonError('Nincs ilyen templom.', 404);
                exit;
            }
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if ($this->modify) {
                    $this->sendJsonError('Method not allowed', 405);
                    exit;
                }
                $this->church->append(['writeAccess']);

                if (!$this->church->writeAccess) {
                    $this->sendJsonError('Hiányzó jogosultság!', 403);
                    exit;
                }

                $state = isset($path[2]) ? strtolower($path[2]) : null;

                $churchId = $this->tid;

                $query = CalSuggestionPackage::where('church_id', $churchId);

                if (!empty($state)) {
                    $query->whereRaw('LOWER(state) = ?', [strtolower($state)]);
                }

                $filtered = $query->with('suggestions')->get()
                    ->map(fn($mass) => $mass->toArray())
                    ->values();

                echo json_encode($filtered);

                exit;
            case 'POST':

                if ($this->modify) {
                    //$path[0]: accept/reject
                    $input = json_decode(file_get_contents('php://input'), true);
                    $this->handleModifiedPost($path[0], $path[1], $input);
                } else {
                    $this->handleNewSuggestionPackage();
                }

                exit();

            default:
                $this->sendJsonError('Method not allowed', 405);
                exit;
        }
    }

    private function handleModifiedPost($operation, $id, $input): void {

        $package = CalSuggestionPackage::with('suggestions')->findOrFail($id);
        $package->state = $input['state'];
        $package->save();


        //Azonos paraméterű javaslatok kezelése
        CalSuggestionPackage::whereIn('id', $this->findIdenticalSuggestions($package))
            ->update(['state' => $input['state']]);

        if ($input['state'] === 'ACCEPTED') {
            //Ugyanarra a misére vonatkozó javaslatok kezelése
            CalSuggestionPackage::whereIn('id', $this->findSuggestionsForMass($package))
                ->update(['state' => 'ACCEPTED']);

            Capsule::connection()->beginTransaction();

            try {
                foreach ($package->suggestions as $sug) {
                    $massId = $sug->mass_id;
                    $changes = $sug->changes ?? [];

                    if ($sug->mass_state === 'NEW') {
                        CalMass::create($changes);
                    } elseif ($sug->mass_state === 'MODIFIED') {
                        $mass = CalMass::findOrFail($massId);
                        $mass->update($changes);
                    } elseif ($sug->mass_state === 'DELETED') {
                        CalMass::where('id', $massId)->delete();
                    }
                }



                Capsule::connection()->commit();
            } catch (\Throwable $e) {
                Capsule::connection()->rollBack();
                echo json_encode(['error' => 'Hiba!', 'details' => $e->getMessage()], 500);
            }
        }

        $query = CalSuggestionPackage::where('church_id', $package->church_id);

        $query->whereRaw('LOWER(state) = ?', [strtolower('PENDING')]);

        $filtered = $query->with('suggestions')->get()
            ->map(fn($mass) => $mass->toArray())
            ->values();

        $calendarMasses = CalMass::where('church_id', $package->church_id)->get();

        echo json_encode([
            'suggestionPackages' => $filtered,
            'calendarMasses' => $calendarMasses->map(fn($mass) => $mass->toArray())->values(),
        ]);

    }

    private function findIdenticalSuggestions($package)
    {
        $suggestions = $package->suggestions;

        if ($suggestions->isEmpty()) {
            return collect();
        }

        $matchedSuggestions = collect();
        $allowedPackageIds = null;

        foreach ($suggestions as $index => $suggestion) {
            $baseNormalizedChanges = $this->normalizeChanges($suggestion->changes);

            $query = CalSuggestion::where('id', '!=', $suggestion->id)
                ->where('mass_state', $suggestion->mass_state)
                ->where('mass_id', $suggestion->mass_id)
                ->whereHas('package', function ($q) use ($package) {
                    $q->where('church_id', $package->church_id)
                        ->where('state', 'PENDING');
                });

            if ($index === 0) {
                $query->where('package_id', '!=', $suggestion->package_id);
            } else {
                if ($allowedPackageIds && $allowedPackageIds->isNotEmpty()) {
                    $query->whereIn('package_id', $allowedPackageIds);
                } else {
                    break;
                }
            }
            $candidates = $query->get();

            $found = $candidates->filter(function ($cand) use ($baseNormalizedChanges) {
                return $this->normalizeChanges($cand->changes) === $baseNormalizedChanges;
            });

            $matchedSuggestions = $matchedSuggestions->merge($found);

            $allowedPackageIds = $found->pluck('package_id')->unique();
        }

        return $matchedSuggestions->pluck('package_id')->unique();
    }

    private function normalizeChanges($changes): string
    {
        if (is_string($changes)) {
            $decoded = json_decode($changes, true);
        } else {
            $decoded = $changes;
        }

        if (!is_array($decoded)) {
            return '';
        }

        ksort($decoded);
        return json_encode($decoded);
    }


    private function findSuggestionsForMass($package)
    {
        $originalSuggestions = $package->suggestions;

        if ($originalSuggestions->isEmpty() || $originalSuggestions->pluck('mass_id')->filter()->isEmpty()) {
            return collect();
        }

        $originalMassIds = $originalSuggestions->pluck('mass_id')->unique();
        $originalChurchId = $package->church_id;
        $originalPackageId = $package->id;

        $candidatePackages = CalSuggestionPackage::with('suggestions')
            ->where('id', '!=', $originalPackageId)
            ->where('church_id', $originalChurchId)
            ->where('state', 'PENDING')
            ->get();

        $validPackageIds = collect();

        foreach ($candidatePackages as $candidate) {
            $suggestions = $candidate->suggestions;

            if ($suggestions->isEmpty()) {
                continue;
            }

            $allValid = $suggestions->every(function ($suggestion) use ($originalMassIds, $originalPackageId) {
                return $originalMassIds->contains($suggestion->mass_id) &&
                    $suggestion->package_id !== $originalPackageId;
            });

            if ($allValid) {
                $validPackageIds->push($candidate->id);
            }
        }

        return $validPackageIds->unique();
    }

    private function handleNewSuggestionPackage(): void {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['churchId']) || !isset($input['suggestions']) || !isset($input['state'])) {
            http_response_code(400);
            echo json_encode(["error" => "Érvénytelen adat"]);
            exit;
        }

        Capsule::connection()->transaction(function () use ($input) {
            $package = CalSuggestionPackage::create([
                'church_id' => $input['churchId'] ?? null,
                'sender_name' => $input['senderName'] ?? null,
                'sender_email' => $input['senderEmail'] ?? null,
                'sender_user_id' => $input['senderUserId'] ?? null,
                'state' => $input['state'] ?? 'PENDING',
                'created_at' => $input['created_at'] ?? null,
            ]);

            if (!empty($input['suggestions']) && is_array($input['suggestions'])) {
                foreach ($input['suggestions'] as $suggestion) {
                    $package->suggestions()->create([
                        'period_id' => $suggestion['periodId'] ?? null,
                        'mass_id' => $suggestion['massId'] ?? null,
                        'mass_state' => $suggestion['massState'],
                        'changes' => $suggestion['changes'] ?? null,
                    ]);
                }
            }

            echo json_encode(["success" => true, "id" => $package->id]);
        });
    }

    private function sendJsonError($message, $code): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code,
        ]);
    }
}