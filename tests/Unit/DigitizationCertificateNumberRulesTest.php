<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DigitizationCertificateNumberRulesTest extends TestCase
{
    private const REQUIRED = 'Certificate Number is required';

    private const DIGITS = 'Certificate Number must be 1 to 6 digits only (numbers only).';

    /** Both rule styles used by the controllers (P uses the array form). */
    /** @return array<string, array<int, mixed>> */
    public static function ruleStyles(): array
    {
        return [
            'string form (S/W/WH)' => ['required|digits_between:1,6'],
            'array form (P)' => [['required', 'digits_between:1,6']],
        ];
    }

    /** @param string|array<int, string> $rule */
    private function validate(mixed $value, string|array $rule): \Illuminate\Validation\Validator
    {
        return Validator::make(['ccnumber' => $value], ['ccnumber' => $rule], [
            'ccnumber.required' => self::REQUIRED,
            'ccnumber.digits_between' => self::DIGITS,
        ]);
    }

    /** @dataProvider ruleStyles */
    public function test_valid_numbers_pass($rule): void
    {
        foreach (['1', '123456', '000123', '12345', '999999'] as $value) {
            $this->assertTrue($this->validate($value, $rule)->passes(), var_export($value, true));
        }
    }

    /** @dataProvider ruleStyles */
    public function test_six_digits_are_accepted_for_all_forms(): void
    {
        $rule = func_get_args()[0];
        $this->assertTrue($this->validate('123456', $rule)->passes());
        $this->assertTrue($this->validate('654321', $rule)->passes());
    }

    /** @dataProvider ruleStyles */
    public function test_empty_value_fails_with_required_message($rule): void
    {
        foreach (['', null] as $value) {
            $v = $this->validate($value, $rule);
            $this->assertTrue($v->fails());
            $this->assertSame([self::REQUIRED], $v->errors()->get('ccnumber'));
        }
    }

    /** @dataProvider ruleStyles */
    public function test_non_numeric_or_wrong_length_fails_with_digits_message($rule): void
    {
        $bad = ['1234567', '12a', 'C1234', 'W12345', '12 34', '12-34', '1.5', '-1', 'abcdef', '+123', '1e3'];
        foreach ($bad as $value) {
            $v = $this->validate($value, $rule);
            $this->assertTrue($v->fails(), var_export($value, true));
            $this->assertSame([self::DIGITS], $v->errors()->get('ccnumber'), var_export($value, true));
        }
    }

    public function test_controllers_use_digits_between_rule_for_ccnumber(): void
    {
        $files = [
            'FormPController.php',
            'FormSDigitizationController.php',
            'FormWController.php',
            'FormWHController.php',
        ];
        foreach ($files as $file) {
            $src = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/'.$file);
            $this->assertMatchesRegularExpression(
                '/[\'"]ccnumber[\'"]\s*=>\s*(\[[^\]]*|[\'"][^\'"]*)digits_between:1,6/',
                $src,
                "$file ccnumber rule"
            );
            $this->assertStringContainsString("'ccnumber.digits_between' => '".self::DIGITS."'", $src, "$file message");
            $this->assertStringContainsString("'ccnumber.required' => '".self::REQUIRED."'", $src, "$file message");
            $this->assertDoesNotMatchRegularExpression(
                '/[\'"]ccnumber[\'"]\s*=>\s*(\[[^\]]*|[\'"][^\'"]*)digits_between:1,5/',
                $src,
                "$file ccnumber still has old 5-digit cap"
            );
        }
    }
}
