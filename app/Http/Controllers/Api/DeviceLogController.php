<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeviceLogController extends Controller
{
    /**
     * Receive logs from a device in JSON format.
     */
    public function sync(Request $request)
    {
        $deviceUid = $request->header('X-Device-UID');
        $apiToken = $request->header('X-API-Token');

        if (!$deviceUid || !$apiToken) {
            return response()->json(['message' => 'Missing credentials'], 401);
        }

        $device = Device::where('device_uid', $deviceUid)
            ->where('api_token', $apiToken)
            ->first();

        if (!$device) {
            return response()->json(['message' => 'Invalid credentials'], 403);
        }

        $logs = $request->json()->all();

        if (empty($logs)) {
            return response()->json(['message' => 'No logs provided'], 400);
        }

        // Handle both single object and array of objects
        if (!isset($logs[0])) {
            $logs = [$logs];
        }

        DB::beginTransaction();
        try {
            $insertedCount = 0;
            foreach ($logs as $logData) {
                // Map fields based on user's sample image
                $employeeCode = $logData['user_id'] ?? $logData['employee_code'] ?? null;
                $punchTime = $logData['punch_time'] ?? null;

                if ($employeeCode && $punchTime) {
                    DeviceLog::create([
                        'employee_code' => $employeeCode,
                        'punch_time' => Carbon::parse($punchTime),
                        'device_id' => $device->id,
                    ]);
                    $insertedCount++;
                }
            }

            $device->update(['last_sync_at' => now()]);
            DB::commit();

            return response()->json([
                'message' => 'Logs synced successfully',
                'inserted' => $insertedCount
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error syncing logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
