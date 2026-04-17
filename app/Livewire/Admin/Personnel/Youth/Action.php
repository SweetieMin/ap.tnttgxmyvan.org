<?php

namespace App\Livewire\Admin\Personnel\Youth;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Action extends Component
{
    use AuthorizesRequests;
    use ProfileValidationRules;

    #[Url]
    public string $tab;

    public ?int $editingUserId = null;

    public ?int $deletingUserId = null;

    public string $holy_name = '';

    public string $name = '';

    public ?string $birthday = null;

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $accountSource = 'manual';

    public string $accountCode = '';

    public string $role = '';

    public bool $keepModal = false;

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public function mount(): void
    {
        $this->resetForm();
        $this->tab = $this->tab ?? 'manual';
    }

    public function updatedAccountSource($value): void
    {
        $this->tab = $value;
    }

    #[On('create-youth')]
    public function openCreateModal(): void
    {
        $this->authorize('personnel.youth.create');

        $this->editingUserId = null;
        $this->resetForm();
        Flux::modal('showFormModal')->show();
    }

    #[On('edit-youth')]
    public function openEditModal($userId = null): void
    {
        $this->authorize('personnel.youth.update');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return;
        }

        $userId = (int) $userId;
        $user = User::query()
            ->with('roles')
            ->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->holy_name = $user->holy_name;
        $this->name = $user->name;
        $this->birthday = filled($user->birthday)
            ? Str::of((string) $user->birthday)->substr(0, 10)->toString()
            : null;
        $this->username = $user->username;
        $this->email = $user->email ?? '';
        $this->password = '';
        $this->password_confirmation = '';

        $this->accountCode = $user->username;
        $this->role = $user->roles->pluck('name')->first() ?? $this->defaultRole();
        $this->resetErrorBag();
        $this->resetValidation();
        Flux::modal('showFormModal')->show();
    }

    #[On('delete-youth')]
    public function openDeleteModal($userId = null): void
    {
        $this->authorize('personnel.youth.delete');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return;
        }

        $this->deletingUserId = (int) $userId;
        $this->resetErrorBag();
        $this->resetValidation();
        Flux::modal('showDeleteModal')->show();
    }

    public function saveUser(): void
    {
        $isEditing = $this->editingUserId !== null;

        $this->authorize($isEditing ? 'personnel.youth.update' : 'personnel.youth.create');

        $this->username = Str::upper($this->username);

        $validated = $this->validate($isEditing
            ? $this->updateRules($this->editingUserId)
            : $this->createRules());

        $validated['email'] = blank($validated['email'] ?? null) ? null : $validated['email'];

        $roleName = $validated['role'];

        unset($validated['role'], $validated['password_confirmation']);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        if ($isEditing) {
            $user = User::query()->findOrFail($this->editingUserId);
            $user->update($validated);
        } else {
            $user = User::query()->create($validated);
        }

        $user->syncRoles([$roleName]);

        if ($isEditing || ! $this->keepModal) {
            Flux::modal('showFormModal')->close();
        }

        $this->editingUserId = null;
        $this->resetForm();

        $this->dispatch('youth-updated');

        Flux::toast(
            variant: 'success',
            text: $isEditing ? __('Đã cập nhật thiếu nhi.') : __('Đã tạo thiếu nhi mới.'),
        );
    }

    public function saveAndCreate(): void
    {
        $this->keepModal = true;
        $this->saveUser();
        $this->keepModal = false;
    }

    public function saveAndClose(): void
    {
        $this->keepModal = false;
        $this->saveUser();
    }

    public function deleteUser(): void
    {
        $this->authorize('personnel.youth.delete');

        $user = User::query()->findOrFail($this->deletingUserId);

        if ($user->is(Auth::user())) {
            $this->addError('delete', __('Bạn không thể xoá chính tài khoản đang đăng nhập.'));

            return;
        }

        $user->delete();

        Flux::modal('showDeleteModal')->close();
        $this->deletingUserId = null;

        $this->dispatch('youth-updated');

        Flux::toast(variant: 'success', text: __('Đã xoá thiếu nhi.'));
    }

    public function closeFormModal(): void
    {
        Flux::modal('showFormModal')->close();
        $this->editingUserId = null;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        Flux::modal('showDeleteModal')->close();
        $this->deletingUserId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedBirthday($value): void
    {
        if (blank($value) || $this->accountSource !== 'manual') {
            return;
        }

        try {
            $date = Carbon::parse($value);
            $dd = $date->format('d');
            $mm = $date->format('m');
            $yy = $date->format('y');
            $rand = str_pad((string) mt_rand(0, 99), 2, '0', STR_PAD_LEFT);
            $this->username = 'MV'.$dd.$mm.$yy.$rand;
        } catch (\Exception $e) {
            // Ignore invalid dates.
        }
    }

    public function updatedUsername(string $value): void
    {
        $this->username = Str::upper($value);
    }

    public function fetchUserByAccountCode(): void
    {
        $this->authorize('personnel.youth.create');

        if ($this->editingUserId !== null) {
            return;
        }

        $validated = $this->validate([
            'accountCode' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/'],
        ]);

        $accountCode = $this->normalizeAccountCode($validated['accountCode']);

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get($this->accountLookupUrl().'/'.$accountCode);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'accountCode' => __('Không thể kết nối tới trang chính để lấy dữ liệu tài khoản.'),
            ]);
        }

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'accountCode' => __('Không tìm thấy tài khoản với mã này trên trang chính.'),
            ]);
        }

        $payload = $response->json();

        if (($payload['success'] ?? false) !== true || ! is_array($payload['data'] ?? null)) {
            throw ValidationException::withMessages([
                'accountCode' => __('Dữ liệu trả về từ trang chính không hợp lệ.'),
            ]);
        }

        /** @var array<string, mixed> $accountData */
        $accountData = $payload['data'];

        $this->accountSource = 'account_code';
        $this->accountCode = $accountCode;
        $this->holy_name = (string) ($accountData['holy_name'] ?? '');
        $this->name = (string) ($accountData['name'] ?? '');
        $this->email = (string) ($accountData['email'] ?? '');
        $this->username = Str::upper((string) ($accountData['username'] ?? $accountCode));
        $this->birthday = $this->normalizeBirthdayFromAccountLookup($accountData['birthday'] ?? null);
        $this->password = $accountCode;
        $this->password_confirmation = $accountCode;
        $this->resetErrorBag('accountCode');

        Flux::toast(variant: 'success', text: __('Đã lấy dữ liệu tài khoản từ trang chính.'));
    }

    #[Computed]
    public function availableRoles(): array
    {
        return Role::query()
            ->orderBy('name')
            ->where('name', 'thiếu nhi')
            ->pluck('name')
            ->all();
    }

    #[Computed]
    public function userPendingDeletion(): ?User
    {
        if ($this->deletingUserId === null) {
            return null;
        }

        return User::query()->find($this->deletingUserId);
    }

    protected function createRules(): array
    {
        return [
            'holy_name' => ['required', 'string', 'max:255'],
            'name' => $this->nameRules(),
            'birthday' => ['nullable', 'date'],
            'username' => $this->usernameRules(),
            'email' => $this->emailRules(),
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ];
    }

    protected function updateRules(int $userId): array
    {
        return [
            'holy_name' => ['required', 'string', 'max:255'],
            'name' => $this->nameRules(),
            'birthday' => ['nullable', 'date'],
            'username' => $this->usernameRules($userId),
            'email' => $this->emailRules($userId),
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ];
    }

    protected function resetForm(): void
    {
        $this->holy_name = '';
        $this->name = '';
        $this->birthday = null;
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->accountSource = 'manual';
        $this->accountCode = '';
        $this->role = $this->defaultRole();
        $this->keepModal = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    protected function accountLookupUrl(): string
    {
        return rtrim((string) config('services.tnttgxmyvan.user_account_lookup_url', 'https://tnttgxmyvan.org/api/users/by-account-code'), '/');
    }

    protected function normalizeAccountCode(string $value): string
    {
        return Str::upper((string) preg_replace('/\s+/', '', trim($value)));
    }

    protected function normalizeBirthdayFromAccountLookup(mixed $value): ?string
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        $birthday = trim($value);

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $birthday) === 1) {
                return Carbon::createFromFormat('d/m/Y', $birthday)->format('Y-m-d');
            }

            return Carbon::parse($birthday)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    protected function defaultRole(): string
    {
        if (Role::query()->where('name', 'thiếu nhi')->exists()) {
            return 'thiếu nhi';
        }

        return Role::query()->orderBy('name')->value('name') ?? '';
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.youth.action');
    }
}
