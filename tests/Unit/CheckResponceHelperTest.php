<?php

namespace Tests\Unit;

use Tests\TestCase;
use Exception;
use Illuminate\Support\Facades\Config;

class CheckResponceHelperTest extends TestCase
{
    public function test_check_responce_returns_actual_error_in_debug_mode()
    {
        Config::set('app.debug', true);
        Config::set('app.env', 'local');

        $exception = new Exception('Specific internal debug error occurred');
        $message = checkResponce($exception);

        $this->assertEquals('Specific internal debug error occurred', $message);
    }

    public function test_check_responce_hides_sensitive_error_in_production()
    {
        Config::set('app.debug', false);
        Config::set('app.env', 'production');

        $exception = new Exception('SQLSTATE[42S22]: Column not found: 1054 Unknown column users.secret');
        $message = checkResponce($exception);

        $this->assertEquals('Something went wrong, please try again later.', $message);
    }

    public function test_check_responce_accepts_custom_fallback_message()
    {
        Config::set('app.debug', false);
        Config::set('app.env', 'production');

        $exception = new Exception('Database connection failed');
        $message = checkResponce($exception, 'Unable to process your request at this time.');

        $this->assertEquals('Unable to process your request at this time.', $message);
    }

    public function test_check_responce_sanitizes_string_database_errors_in_production()
    {
        Config::set('app.debug', false);
        Config::set('app.env', 'production');

        $rawDbError = 'SQLSTATE[HY000] [2002] Connection refused';
        $message = checkResponce($rawDbError);

        $this->assertEquals('Something went wrong, please try again later.', $message);

        $safeUserMessage = 'Invalid coupon code provided';
        $safeResult = checkResponce($safeUserMessage);

        $this->assertEquals('Invalid coupon code provided', $safeResult);
    }

    public function test_check_response_alias_works_identically()
    {
        Config::set('app.debug', false);
        Config::set('app.env', 'production');

        $exception = new Exception('Database timeout');
        $this->assertEquals('Something went wrong, please try again later.', checkResponse($exception));
    }
}
