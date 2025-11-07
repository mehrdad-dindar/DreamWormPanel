<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use Illuminate\Http\Request;

class BugReportController extends Controller
{
    public function store(Request $request)
    {
        $bug = BugReport::create([
            'user_id' => auth()?->id() || 1,
            'title' => $request['data']['input_text'],
            'description' => $request['data']['description'],
        ]);

        // Dispatch job for async handling
        dispatch(new \App\Jobs\SendBugReportToGitHub($bug))->onQueue('github issue');

        return response()->json([
            'status' => 'success',
            'message' => 'success', "گزارش شما با شناسه #{$bug->id} ثبت شد و بررسی خواهد شد 🙏"
        ]);
    }

}
