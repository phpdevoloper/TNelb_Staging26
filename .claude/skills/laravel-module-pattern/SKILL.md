---
name: laravel-module-pattern
description: Repeating FormA/B/P/S/W/WH controller + model + view + route + migration pattern
---

# Laravel Module Pattern Skill

The TNELB portal uses a **repeating module pattern** for license forms. When building a new form (FormA, FormB, FormP, FormS, FormW, FormWH) or extending an existing one, follow this structure exactly.

## The Pattern

### 1. Controller (`app/Http/Controllers/Form[X]Controller.php`)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Form[X];
use App\Http\Requests\Form[X]Request;
use App\Services\Form[X]Service;
use Illuminate\Http\Request;

class Form[X]Controller extends Controller
{
    protected $service;

    public function __construct(Form[X]Service $service)
    {
        $this->service = $service;
    }

    // Display the form (GET)
    public function create()
    {
        $licenseTypes = [...]; // fetch options
        return view('form-[x].create', compact('licenseTypes'));
    }

    // Process form submission (POST)
    public function store(Form[X]Request $request)
    {
        $form = $this->service->create($request->validated());
        return redirect()->route('form-[x].show', $form)->with('success', 'Form submitted');
    }

    // Display submitted form (GET)
    public function show(Form[X] $form)
    {
        $this->authorize('view', $form); // Check user owns the form
        return view('form-[x].show', compact('form'));
    }

    // Payment flow (if needed)
    public function payment(Form[X] $form)
    {
        $this->authorize('pay', $form);
        // Handle PayU integration
        return view('form-[x].payment', compact('form'));
    }

    // Generate PDF/certificate
    public function download_pdf(Form[X] $form)
    {
        $this->authorize('view', $form);
        return $this->service->generatePdf($form);
    }
}
```

Key principles:
- Use dependency injection (`Form[X]Service $service`)
- Validate input via Form Request class (`Form[X]Request`)
- Delegate business logic to Service layer
- Use `$this->authorize()` to check permissions
- Return views or responses consistently

### 2. Model (`app/Models/Form[X].php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form[X] extends Model
{
    use HasFactory;

    protected $table = 'form_[x]';

    protected $fillable = [
        'user_id',
        'status',
        'field1',
        'field2',
        // ... form-specific fields
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Form[X]Document::class);
    }

    // Query scopes
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'deleted');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d-m-Y');
    }

    public function getDisplayStatusAttribute()
    {
        return ucfirst($this->status);
    }
}
```

Key principles:
- Model name is singular: `FormA`, not `FormAs`
- Table name is lowercased: `form_a`
- Use `$fillable` to control mass assignment
- Define relationships with proper return types
- Use scopes for common queries
- Use accessors for computed/formatted properties

### 3. Migration (`database/migrations/xxxx_xx_xx_xxxxxx_create_form_[x]_table.php`)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('form_[x]', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('status')->default('pending'); // pending, submitted, approved, rejected
            
            // Form-specific fields
            $table->string('field1')->nullable();
            $table->text('field2')->nullable();
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('status');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('form_[x]');
    }
};
```

Key principles:
- Always include `id()` as primary key
- Always include `user_id` foreign key
- Always include `status` field (workflow tracking)
- Always include `timestamps()` (created_at, updated_at)
- Include additional timestamps as needed (`submitted_at`, `approved_at`)
- Add indexes for frequently queried columns
- Use proper data types (string, text, unsignedBigInteger, etc.)

### 4. Routes (`routes/web.php` or `routes/admin.php`)

```php
// Public routes
Route::middleware('auth')->prefix('forms')->group(function () {
    Route::get('/form-[x]/create', [Form[X]Controller::class, 'create'])->name('form-[x].create');
    Route::post('/form-[x]', [Form[X]Controller::class, 'store'])->name('form-[x].store');
    Route::get('/form-[x]/{form}', [Form[X]Controller::class, 'show'])->name('form-[x].show');
    Route::post('/form-[x]/{form}/payment', [Form[X]Controller::class, 'payment'])->name('form-[x].payment');
    Route::get('/form-[x]/{form}/pdf', [Form[X]Controller::class, 'download_pdf'])->name('form-[x].pdf');
});

// Or admin routes (protected by admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin/forms')->group(function () {
    Route::get('/form-[x]', [Admin\Form[X]Controller::class, 'index'])->name('admin.form-[x].index');
    Route::get('/form-[x]/{form}/approve', [Admin\Form[X]Controller::class, 'approve'])->name('admin.form-[x].approve');
});
```

Key principles:
- Use route names (`form-[x].create`, `form-[x].store`, etc.) for consistency
- Prefix routes by module (`/forms/`, `/admin/forms/`)
- Use middleware to protect routes (`auth`, `admin`, custom gates)
- Map to controller actions clearly

### 5. Views (`resources/views/form-[x]/`)

Directory structure:
```
resources/views/form-[x]/
  create.blade.php       # Form entry page
  show.blade.php         # View submitted form
  edit.blade.php         # Edit form (if allowed)
  success.blade.php      # Confirmation page
  payment.blade.php      # Payment gateway page
  pdf.blade.php          # PDF template
```

Example `create.blade.php`:
```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Form [X] - License Application</h1>
    
    <form action="{{ route('form-[x].store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label>License Type</label>
            <select name="license_type" class="form-control select2">
                <option value="">-- Select --</option>
                @foreach ($licenseTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('license_type')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- More fields -->
        
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
@endsection
```

Key principles:
- Use `@extends('layouts.app')` for layout inheritance
- Use `@csrf` in all forms
- Use `route('form-[x].*')` for links/form actions
- Use `@error()` for validation error display
- Use `old('field')` to repopulate after validation fails
- Match existing CSS classes and theme conventions

### 6. Form Request (`app/Http/Requests/Form[X]Request.php`)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Form[X]Request extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'license_type' => 'required|exists:mst_license_types,id',
            'field1' => 'required|string|max:255',
            'field2' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'license_type.required' => 'Please select a license type',
            'document.mimes' => 'Document must be PDF, JPG, or PNG',
        ];
    }
}
```

Key principles:
- Centralize validation rules (not in controller)
- Use `authorize()` to check user permission
- Provide custom error messages for better UX
- Validate file uploads (type, size, extensions)

## When to Use This Pattern

Use this pattern when:
- Adding a new license form (FormC, FormD, etc. if they don't exist)
- Extending an existing form with new fields or workflows
- Following the existing module structure for consistency

Do **NOT** deviate from this pattern without documented reason. Consistency makes the codebase easier to maintain and extend.
