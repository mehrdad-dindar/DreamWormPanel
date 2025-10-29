<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use Illuminate\Http\Request;

class BugReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
        ]);

        $bug = BugReport::create([
            'user_id' => auth()->id() || 1,
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        // Dispatch job for async handling
        dispatch(new \App\Jobs\SendBugReportToGitHub($bug))->onQueue('github issue');

        return back()->with('success', "گزارش شما با شناسه #{$bug->id} ثبت شد و بررسی خواهد شد 🙏");
    }

}
