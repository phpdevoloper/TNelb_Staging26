<?php

namespace Tests\Feature;

use App\Models\OtpVerification;
use App\Models\Register;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the registration address refactoring: the single free-text `address`
 * column was replaced by `address_line1`, `address_line2`, `city` (plus the
 * pre-existing `state`, `district`, `pincode`).
 *
 * IMPORTANT — why this test does not use RefreshDatabase:
 * the configured connection points at the shared pgsql dev database
 * (tnelb_live) whose schema was built from SQL dumps, not migrations
 * (`php artisan migrate:status` reports every migration as Pending).
 * RefreshDatabase would therefore drop the live tables and rebuild an
 * out-of-date schema. Instead each test runs against a throwaway in-memory
 * sqlite database whose `tnelb_registers` definition mirrors the live table
 * plus the refactored address columns.
 */
class RegisterControllerAddressRefactoringTest extends TestCase
{
    /** @var string */
    private const MOBILE = '9876543210';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        $this->createSchema();
    }

    /*
    |--------------------------------------------------------------------------
    | store() validation
    |--------------------------------------------------------------------------
    */

    public function test_store_accepts_valid_address_fields()
    {
        $this->verifiedOtp();

        $response = $this->postJson(route('register.store'), $this->payload());

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'login_id' => 'tnelb_1120',
        ]);
    }

    public function test_store_rejects_missing_required_address_fields()
    {
        $this->verifiedOtp();

        $payload = $this->payload();
        unset($payload['address_line1'], $payload['city'], $payload['district'], $payload['pincode']);

        $response = $this->postJson(route('register.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'address_line1',
            'city',
            'district',
            'pincode',
        ]);
        $this->assertSame(0, Register::count());
    }

    public function test_store_rejects_blank_required_address_fields()
    {
        $this->verifiedOtp();

        $response = $this->postJson(route('register.store'), $this->payload([
            'address_line1' => '',
            'city' => '',
            'district' => '',
            'pincode' => '',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address_line1', 'city', 'district', 'pincode']);
        $this->assertSame(0, Register::count());
    }

    public function test_store_allows_address_line2_to_be_omitted()
    {
        $this->verifiedOtp();

        $payload = $this->payload();
        unset($payload['address_line2']);

        $response = $this->postJson(route('register.store'), $payload);

        $response->assertOk();
        $this->assertNull(Register::first()->getAttributes()['address_line2']);
    }

    public function test_store_allows_address_line2_to_be_null()
    {
        $this->verifiedOtp();

        $response = $this->postJson(route('register.store'), $this->payload([
            'address_line2' => null,
        ]));

        $response->assertOk();
        $this->assertNull(Register::first()->getAttributes()['address_line2']);
    }

    public function test_store_allows_address_line2_to_be_empty_string()
    {
        $this->verifiedOtp();

        // Sent as a form post so the empty string survives to the global
        // ConvertEmptyStringsToNull middleware, which is how the browser
        // submits an untouched optional field.
        $response = $this->post(route('register.store'), $this->payload([
            'address_line2' => '',
        ]), ['Accept' => 'application/json']);

        $response->assertOk();
        $this->assertNull(Register::first()->getAttributes()['address_line2']);
    }

    public function test_store_rejects_over_long_address_parts()
    {
        $this->verifiedOtp();

        $response = $this->postJson(route('register.store'), $this->payload([
            'address_line1' => str_repeat('a', 101),
            'address_line2' => str_repeat('b', 101),
            'city' => str_repeat('c', 51),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address_line1', 'address_line2', 'city']);
    }

    public function test_store_rejects_non_six_digit_pincode()
    {
        $this->verifiedOtp();

        $response = $this->postJson(route('register.store'), $this->payload([
            'pincode' => '60001',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pincode']);
    }

    public function test_store_allows_missing_district_outside_tamil_nadu()
    {
        $this->verifiedOtp();

        $payload = $this->payload(['state' => 'Kerala']);
        unset($payload['district']);

        $response = $this->postJson(route('register.store'), $payload);

        $response->assertOk();
        // Controller normalises an absent district to 0.
        $this->assertSame('0', (string) Register::first()->district);
    }

    /*
    |--------------------------------------------------------------------------
    | store() persistence
    |--------------------------------------------------------------------------
    */

    public function test_store_saves_address_parts_under_new_column_names()
    {
        $this->verifiedOtp();

        $this->postJson(route('register.store'), $this->payload())->assertOk();

        $this->assertDatabaseHas('tnelb_registers', [
            'mobile' => self::MOBILE,
            'address_line1' => '12/4 Gandhi Street',
            'address_line2' => 'Near Water Tank',
            'city' => 'Coimbatore',
            'state' => 'Tamil Nadu',
            'district' => 'Coimbatore',
            'pincode' => '641001',
            'login_id' => 'tnelb_1120',
        ]);
    }

    public function test_store_does_not_write_the_legacy_address_column()
    {
        $this->verifiedOtp();

        $this->postJson(route('register.store'), $this->payload())->assertOk();

        $this->assertNull(Register::first()->getAttributes()['address']);
    }

    public function test_store_ignores_a_submitted_legacy_address_value()
    {
        $this->verifiedOtp();

        $this->postJson(route('register.store'), $this->payload([
            'address' => 'Some legacy single-line address',
        ]))->assertOk();

        $this->assertNull(Register::first()->getAttributes()['address']);
    }

    /*
    |--------------------------------------------------------------------------
    | OTP gate still guards the refactored payload
    |--------------------------------------------------------------------------
    */

    public function test_store_rejects_valid_address_payload_when_otp_is_not_verified()
    {
        OtpVerification::create([
            'mobile' => self::MOBILE,
            'otp' => '123456',
            'otp_expiry' => now()->addMinutes(5),
            'is_verified' => false,
        ]);

        $response = $this->postJson(route('register.store'), $this->payload());

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Please verify your OTP first',
        ]);
        $this->assertSame(0, Register::count());
    }

    public function test_store_rejects_valid_address_payload_when_no_otp_exists()
    {
        $response = $this->postJson(route('register.store'), $this->payload());

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertSame(0, Register::count());
    }

    /*
    |--------------------------------------------------------------------------
    | Register::getAddressAttribute()
    |--------------------------------------------------------------------------
    */

    public function test_address_accessor_composes_the_new_address_parts()
    {
        $this->verifiedOtp();
        $this->postJson(route('register.store'), $this->payload())->assertOk();

        $register = Register::first();

        $this->assertSame(
            '12/4 Gandhi Street, Near Water Tank, Coimbatore',
            $register->address
        );
    }

    public function test_address_accessor_skips_an_empty_address_line2()
    {
        $register = new Register([
            'address_line1' => '12/4 Gandhi Street',
            'address_line2' => null,
            'city' => 'Coimbatore',
        ]);

        $this->assertSame('12/4 Gandhi Street, Coimbatore', $register->address);
    }

    public function test_address_accessor_returns_legacy_value_for_old_rows()
    {
        $legacy = new Register();
        $legacy->setRawAttributes([
            'address' => 'Old Door No 7, Mount Road, Chennai - 600002',
            'address_line1' => null,
            'address_line2' => null,
            'city' => null,
        ]);

        $this->assertSame(
            'Old Door No 7, Mount Road, Chennai - 600002',
            $legacy->address
        );
    }

    public function test_address_accessor_prefers_legacy_value_when_both_are_present()
    {
        $row = new Register();
        $row->setRawAttributes([
            'address' => 'Legacy single line',
            'address_line1' => '12/4 Gandhi Street',
            'address_line2' => 'Near Water Tank',
            'city' => 'Coimbatore',
        ]);

        $this->assertSame('Legacy single line', $row->address);
    }

    public function test_address_accessor_returns_null_when_nothing_is_set()
    {
        $row = new Register();
        $row->setRawAttributes([
            'address' => null,
            'address_line1' => null,
            'address_line2' => null,
            'city' => null,
        ]);

        $this->assertNull($row->address);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'salutation' => 'Mr',
            'first_name' => 'Arun',
            'last_name' => 'Kumar',
            'gender' => 'Male',
            'mobile' => self::MOBILE,
            'email' => 'arun.kumar@example.com',
            'address_line1' => '12/4 Gandhi Street',
            'address_line2' => 'Near Water Tank',
            'city' => 'Coimbatore',
            'state' => 'Tamil Nadu',
            'district' => 'Coimbatore',
            'pincode' => '641001',
        ], $overrides);
    }

    private function verifiedOtp(string $mobile = self::MOBILE): OtpVerification
    {
        return OtpVerification::create([
            'mobile' => $mobile,
            'otp' => '123456',
            'otp_expiry' => now()->addMinutes(5),
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Mirrors the live `tnelb_registers` columns (post-refactor) plus the
     * empty lookup tables BaseController reads on every request.
     */
    private function createSchema(): void
    {
        Schema::create('tnelb_registers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('salutation')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('mobile', 10)->unique();
            $table->string('email')->nullable();
            $table->text('address')->nullable();          // legacy, read-only
            $table->string('address_line1', 100)->nullable();
            $table->string('address_line2', 100)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('pincode', 6)->nullable();
            $table->string('login_id')->default('');
            $table->timestamps();
        });

        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 10)->unique();
            $table->string('otp', 6);
            $table->dateTime('otp_expiry');
            $table->boolean('is_verified')->default(false);
            $table->dateTime('verified_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();
        });

        $lookups = [
            'tnelb_homeslider_tbls',
            'tnelb_menus',
            'tnelb_submenus',
            'tnelb_whatsnew',
            'tnelb_newsboards',
            'tnelb_quicklinks',
            'tnelb_footerbottoms',
            'tnelb_footercopyrights',
        ];

        foreach ($lookups as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->integer('status')->default(1);
                $table->integer('order_id')->default(0);
                $table->integer('slider_status')->default(1);
                $table->timestamps();
            });
        }

        Schema::create('tnelb_menupages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->unsignedBigInteger('submenu_id')->nullable();
            $table->unsignedBigInteger('footer_quicklinks_id')->nullable();
            $table->unsignedBigInteger('footer_bottom_id')->nullable();
            $table->timestamps();
        });
    }
}
