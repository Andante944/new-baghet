<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = User::query()
            ->where('role', 'client')
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('company_name', 'like', $term);
                });
            })
            ->withCount('orders')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('admin.clients.form', ['client' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['role'] = 'client';
        $data['is_active'] = $request->boolean('is_active');

        $generatedPassword = null;
        if (blank($data['password'] ?? null)) {
            $generatedPassword = Str::random(10);
            $data['password'] = $generatedPassword;
        }

        $client = User::query()->create($data);

        $redirect = redirect()->route('admin.clients.index')
            ->with('success', __('site.client_created'));

        if ($generatedPassword !== null) {
            $redirect->with('generated_password', [
                'email' => $client->email,
                'password' => $generatedPassword,
            ]);
        }

        return $redirect;
    }

    public function edit(User $client): View
    {
        abort_unless($client->role === 'client', 404);

        return view('admin.clients.form', compact('client'));
    }

    public function update(Request $request, User $client): RedirectResponse
    {
        abort_unless($client->role === 'client', 404);

        $data = $this->validated($request, $client);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $data['is_active'] = $request->boolean('is_active');
        $client->update($data);

        return redirect()->route('admin.clients.index')
            ->with('success', __('site.client_updated'));
    }

    public function importForm(): View
    {
        return view('admin.clients.import');
    }

    public function import(Request $request): View
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimes:csv,txt', 'max:5120'],
            'rows' => ['nullable', 'string', 'max:1000000'],
        ]);

        $content = '';
        if ($request->hasFile('file')) {
            $content = (string) file_get_contents($request->file('file')->getRealPath());
        } elseif (filled($request->input('rows'))) {
            $content = (string) $request->input('rows');
        }

        if (trim($content) === '') {
            throw ValidationException::withMessages([
                'file' => __('site.client_import_need_source'),
            ]);
        }

        [$mapping, $rows, $firstLine] = $this->parseCsv($content);

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => __('site.client_import_empty'),
            ]);
        }

        $created = [];
        $skipped = [];
        $errors = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $line = $index + $firstLine;
            $value = fn (string $key): string => isset($mapping[$key])
                ? trim((string) ($row[$mapping[$key]] ?? ''))
                : '';

            $company = $value('company_name');
            $name = $value('name') ?: $company;
            $email = mb_strtolower($value('email'));
            $phone = $value('phone');
            $password = $value('password');
            $bitrixContact = $value('bitrix_contact_id');
            $bitrixCompany = $value('bitrix_company_id');

            if ($name === '') {
                $errors[] = __('site.client_import_line', ['line' => $line]).': '.__('site.client_import_no_name');

                continue;
            }

            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = __('site.client_import_line', ['line' => $line]).': '.__('site.client_import_bad_email');

                continue;
            }

            if (isset($seen[$email]) || User::query()->where('email', $email)->exists()) {
                $skipped[] = $email.' — '.__('site.client_import_exists');

                continue;
            }

            if ($password !== '' && mb_strlen($password) < 8) {
                $errors[] = __('site.client_import_line', ['line' => $line]).': '.__('site.client_import_short_password');

                continue;
            }

            if (($bitrixContact !== '' && ! ctype_digit($bitrixContact))
                || ($bitrixCompany !== '' && ! ctype_digit($bitrixCompany))) {
                $errors[] = __('site.client_import_line', ['line' => $line]).': '.__('site.client_import_bad_bitrix');

                continue;
            }

            $password = $password !== '' ? $password : Str::random(10);

            User::query()->create([
                'role' => 'client',
                'name' => $name,
                'company_name' => $company !== '' ? $company : null,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'password' => $password,
                'bitrix_contact_id' => $bitrixContact !== '' ? (int) $bitrixContact : null,
                'bitrix_company_id' => $bitrixCompany !== '' ? (int) $bitrixCompany : null,
                'is_active' => true,
            ]);

            $seen[$email] = true;
            $created[] = [
                'company_name' => $company,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
            ];
        }

        $token = null;
        if ($created !== []) {
            $token = Str::random(32);
            Storage::disk('local')->put(
                'client-imports/'.$token.'.csv',
                $this->resultCsv($created),
            );
        }

        return view('admin.clients.import-result', [
            'created' => $created,
            'skipped' => $skipped,
            'importErrors' => $errors,
            'token' => $token,
        ]);
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo "\xEF\xBB\xBF";
            echo "company_name;name;email;phone;password;bitrix_contact_id;bitrix_company_id\n";
            echo "SRL Exemplu;Ion Popescu;ion@example.com;+37360000000;;;\n";
        }, 'client-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadImport(string $token): StreamedResponse
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{32}$/', $token) === 1, 404);

        $path = 'client-imports/'.$token.'.csv';
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, 'clients-'.$token.'.csv');
    }

    /**
     * @param  list<array<string, string>>  $created
     */
    private function resultCsv(array $created): string
    {
        $csv = "\xEF\xBB\xBF";
        $csv .= "company_name;name;email;phone;password\n";

        foreach ($created as $row) {
            $cells = array_map(
                fn (string $value): string => '"'.str_replace('"', '""', $value).'"',
                [
                    $row['company_name'],
                    $row['name'],
                    $row['email'],
                    $row['phone'],
                    $row['password'],
                ],
            );
            $csv .= implode(';', $cells)."\n";
        }

        return $csv;
    }

    /**
     * @return array{0: array<string, int>, 1: list<list<string>>, 2: int}
     */
    private function parseCsv(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split('/\r\n|\n|\r/', $content) ?: [];
        $lines = array_values(array_filter(
            array_map('trim', $lines),
            fn (string $line): bool => $line !== '',
        ));

        if ($lines === []) {
            return [[], [], 1];
        }

        $delimiter = $this->detectDelimiter($lines[0]);
        $parsed = array_map(
            fn (string $line): array => array_map(
                fn ($cell): string => trim((string) $cell),
                str_getcsv($line, $delimiter),
            ),
            $lines,
        );

        $header = array_map(fn (string $cell): string => $this->normalizeHeader($cell), $parsed[0]);
        $known = ['company_name', 'name', 'email', 'phone', 'password', 'bitrix_contact_id', 'bitrix_company_id'];
        $hasHeader = count(array_intersect($header, $known)) > 0;

        if (! $hasHeader) {
            return [
                [
                    'company_name' => 0,
                    'name' => 1,
                    'email' => 2,
                    'phone' => 3,
                    'password' => 4,
                    'bitrix_contact_id' => 5,
                    'bitrix_company_id' => 6,
                ],
                $parsed,
                1,
            ];
        }

        return [$this->headerMap($header), array_slice($parsed, 1), 2];
    }

    private function detectDelimiter(string $line): string
    {
        $counts = [
            ';' => substr_count($line, ';'),
            ',' => substr_count($line, ','),
            "\t" => substr_count($line, "\t"),
        ];
        arsort($counts);

        return (string) array_key_first($counts);
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[\s\-]+/u', '_', $value) ?? $value;

        return trim($value, " \t\n\r\0\x0B\"'");
    }

    /**
     * @param  list<string>  $header
     * @return array<string, int>
     */
    private function headerMap(array $header): array
    {
        $aliases = [
            'company_name' => ['company', 'company_name', 'компания', 'фирма', 'firma', 'organizatie', 'întreprindere', 'intreprindere'],
            'name' => ['name', 'contact', 'contact_person', 'имя', 'контакт', 'контактное_лицо', 'nume', 'persoana_de_contact'],
            'email' => ['email', 'e_mail', 'mail', 'почта', 'электронная_почта'],
            'phone' => ['phone', 'phone_number', 'tel', 'telefon', 'телефон'],
            'password' => ['password', 'пароль', 'parola'],
            'bitrix_contact_id' => ['bitrix_contact_id', 'bitrix_contact', 'bitrix_contactid', 'bitrix_контакт'],
            'bitrix_company_id' => ['bitrix_company_id', 'bitrix_company', 'bitrix_companyid', 'bitrix_компания'],
        ];

        $map = [];
        foreach ($header as $index => $column) {
            foreach ($aliases as $field => $names) {
                if (in_array($column, $names, true)) {
                    $map[$field] = $index;

                    break;
                }
            }
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $client = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($client?->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'bitrix_contact_id' => ['nullable', 'integer', 'min:1'],
            'bitrix_company_id' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
