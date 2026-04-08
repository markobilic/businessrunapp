<?php

namespace App\Imports;

use App\Models\Runner;
use App\Models\RunnerReservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;

class RunnersImport implements ToCollection
{
    protected $reservation;

    /**
     * Pass in the Reservation so we know the current reservation_id & captain_id.
     */
    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }
    
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $header = $rows->first()->toArray();

        $expectedHeader = [
            'Name',
            'LastName',
            'Email',
            'Sex',
            'WorkPositionID',
            'WorkSectorID',
            'WeekRunningID',
            'LongestRaceID',
            'SocksSizeID',
            'DateOfBirth',
            'Phone',
        ];

        if ($header !== $expectedHeader) 
        {
            // If the header row does not match exactly, throw a validation exception.
            throw ValidationException::withMessages([
                'importFile' => ['The spreadsheet header is invalid. Expected columns: ' . implode(', ', $expectedHeader)],
            ]);
        }

        // Skip the header row (assumes first row is column names)
        $dataRows = $rows->skip(1);

        foreach ($dataRows as $rowIndex => $row) 
        {
            $lineNumber = $rowIndex + 2;
            
            $rowName      = trim($row[0] ?? '');
            $rowLastName  = trim($row[1] ?? '');

            // Assume: $row[2] is the email column, adjust index if needed
            $email = trim($row[2]);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
            {
                /*
                $message = "Row {$lineNumber}: Skipped because email “{$email}” is invalid or missing.";
                Log::info($message);
                session()->push('import_messages', $message);
                */
                
                // Skip any row where email is missing or invalid
                continue;
            }

            // 2) Look up any runner belonging to this captain with that email
            $candidate = Runner::where('email', $email)
                ->where('captain_id', $this->reservation->captain_id)
                ->first();

            if ($candidate && strcasecmp($candidate->name, $rowName) === 0 && strcasecmp($candidate->last_name, $rowLastName) === 0) 
            {
                // 2a) If name & last_name also match (case‐insensitive), reuse it:
                $runner = $candidate;
            } 
            else 
            {
                $runner = Runner::create([
                    'captain_id'        => $this->reservation->captain_id,
                    'name'              => $row[0] ?? null,
                    'last_name'         => $row[1] ?? null,
                    'email'             => $email,
                    'sex'               => $row[3] ?? null,
                    'work_position_id'  => intval($row[4] ?? 0) ?: null,
                    'work_sector_id'    => intval($row[5] ?? 0) ?: null,
                    'week_running_id'   => intval($row[6] ?? 0) ?: null,
                    'longest_race_id'   => intval($row[7] ?? 0) ?: null,
                    'socks_size_id'     => intval($row[8] ?? 0) ?: null,
                    'date_of_birth'     => isset($row[9]) 
                                          ? Carbon::parse($row[9])->format('Y-m-d') 
                                          : null,
                    'phone'             => $row[10] ?? null,
                ]);
                
                /*
                $createData = [
                    'captain_id'        => $this->reservation->captain_id,
                    'name'              => $row[0] ?? null,
                    'last_name'         => $row[1] ?? null,
                    'email'             => $email,
                    'sex'               => $row[3] ?? null,
                    'work_position_id'  => intval($row[4] ?? 0) ?: null,
                    'work_sector_id'    => intval($row[5] ?? 0) ?: null,
                    'week_running_id'   => intval($row[6] ?? 0) ?: null,
                    'longest_race_id'   => intval($row[7] ?? 0) ?: null,
                    'socks_size_id'     => intval($row[8] ?? 0) ?: null,
                    'date_of_birth'     => isset($row[9]) 
                                          ? Carbon::parse($row[9])->format('Y-m-d') 
                                          : null,
                    'phone'             => $row[10] ?? null,
                ];

                $message = "Row {$lineNumber}: Would create Runner with: " . json_encode($createData);
                */
            }

            $slot = RunnerReservation::where('reservation_id', $this->reservation->id)
                ->whereNull('runner_id')
                ->orderBy('id')
                ->first();

            if ($slot) 
            {
                $slot->update(['runner_id' => $runner->id]);
                
                /*
                if ($candidate) 
                {
                    $message .= " Then would update RunnerReservation (ID {$slot->id}) → set runner_id = {$candidate->id}.";
                } 
                else 
                {
                    // We didn’t actually create, but show “would have created runner_id = NEW_ID”
                    $message .= " Then would update RunnerReservation (ID {$slot->id}) → set runner_id to NEW_RUNNER_ID.";
                }
                */
            } 
            
            /*
            else 
            {
                $message .= " No available RunnerReservation slot (all runner_id are filled).";
            }

            Log::info($message);
            session()->push('import_messages', $message);
            */
        }
    }
}
