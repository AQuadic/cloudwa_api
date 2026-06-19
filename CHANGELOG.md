# Changelog

All notable changes to `cloudwa_api` will be documented in this file.

## [Unreleased]

### Added
- Support for array and comma-separated formats for private OTP numbers (`cloudwa.otp.private`).
- Dynamic instance context support via `getWaCallback()` method in `Cloudwa` class.
- Comprehensive Pest tests in `tests/OTPTest.php` to cover OTP parsing and callback behaviors.

### Fixed
- Fixed bug in `sendOTP()` where results mapping returned `null` instead of callback details.
- Fixed `throwOnException` option which was previously defined but ignored during `sendMessage()` and `sendOTP()`.
- Fixed PHPStan configuration by removing the non-existent `database` path.
