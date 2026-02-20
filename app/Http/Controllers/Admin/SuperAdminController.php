<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuperAdminController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        $currentTheme = SiteSetting::getTheme();

        return view('admin.super', compact('users', 'currentTheme'));
    }

    public function backup()
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $filename = $database . '_backup_' . date('Y-m-d_His') . '.sql';

        return new StreamedResponse(function () use ($host, $port, $database, $username, $password) {
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database)
            );

            $process = popen($command, 'r');
            if ($process) {
                while (!feof($process)) {
                    echo fread($process, 8192);
                    flush();
                }
                pclose($process);
            }
        }, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:102400',
        ]);

        $file = $request->file('sql_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['sql'])) {
            return redirect()->route('admin.super.index')->with('error', 'Only .sql files are allowed.');
        }

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $tmpPath = $file->getRealPath();

        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($tmpPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return redirect()->route('admin.super.index')->with('error', 'Restore failed: ' . implode("\n", $output));
        }

        return redirect()->route('admin.super.index')->with('success', 'Database restored successfully from ' . $file->getClientOriginalName());
    }

    public function updateUserRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent modifying own role
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.super.index')->with('error', 'You cannot change your own role.');
        }

        $validated = $request->validate([
            'role' => 'required|in:user,admin,super_admin',
        ]);

        $user->is_admin = in_array($validated['role'], ['admin', 'super_admin']);
        $user->is_super_admin = $validated['role'] === 'super_admin';
        $user->save();

        $roleLabel = match($validated['role']) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            default => 'User',
        };

        return redirect()->route('admin.super.index')->with('success', $user->name . ' is now ' . $roleLabel . '.');
    }

    public function resetUserPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent resetting own password (use profile page instead)
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.super.index')
                ->with('error', 'You cannot reset your own password here. Use your profile page.');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->setRememberToken(Str::random(60));
        $user->save();

        return redirect()->route('admin.super.index')
            ->with('success', 'Password for ' . $user->name . ' has been reset.');
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme_name' => 'required|string|max:50',
            'primary_color' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary_color' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        SiteSetting::set('theme_name', $validated['theme_name']);
        SiteSetting::set('theme_primary_color', $validated['primary_color']);
        SiteSetting::set('theme_secondary_color', $validated['secondary_color']);

        return redirect()->route('admin.super.index')->with('success', 'Site theme updated successfully.');
    }
}
