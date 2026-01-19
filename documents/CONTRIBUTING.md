# Contributing to NicSRS SSL Module

Thank you for your interest in contributing to the NicSRS SSL WHMCS Module. This document provides guidelines for contributing to this project.

## Code of Conduct

By participating in this project, you agree to maintain a professional and respectful environment for all contributors.

## Getting Started

### Development Environment Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/hvn-group/nicsrs-ssl-whmcs.git
   cd nicsrs-ssl-whmcs
   ```

2. **WHMCS Development Instance**
   - Set up a local WHMCS installation for testing
   - Use the latest stable WHMCS version
   - Enable development/debug mode

3. **PHP Requirements**
   - PHP 7.2 or higher
   - Required extensions: curl, openssl, json, zip

### Project Structure

```
nicsrs_ssl/
├── nicsrs_ssl.php          # Main module entry point
├── lang/                   # Translation files
├── src/
│   ├── config/            # Configuration files
│   └── model/
│       ├── Controller/    # Business logic controllers
│       ├── Dispatcher/    # Request dispatchers
│       └── Service/       # Service layer classes
└── view/                  # Smarty templates
```

## Development Guidelines

### Code Style

Follow PSR-12 coding standards with these additions:

1. **Indentation**: 4 spaces, no tabs
2. **Line Length**: Maximum 120 characters
3. **Naming Conventions**:
   - Classes: `PascalCase`
   - Methods/Functions: `camelCase`
   - Variables: `camelCase`
   - Constants: `UPPER_SNAKE_CASE`
4. **Comments**: Use English for all code comments

### File Header

All PHP files should include:

```php
<?php
/**
 * NicSRS SSL WHMCS Module
 *
 * @package    nicsrs_ssl
 * @author     HVN GROUP
 * @copyright  Copyright (c) HVN GROUP
 */
```

### Namespace Convention

Use the `nicsrsSSL` namespace for all classes:

```php
namespace nicsrsSSL;

class MyNewClass {
    // ...
}
```

### Error Handling

Always use try-catch blocks for external API calls:

```php
try {
    $result = nicsrsAPI::call('endpoint', $data);
    if ($result->code !== 1) {
        throw new \Exception($result->msg);
    }
} catch (\Exception $e) {
    logModuleCall('nicsrs_ssl', __FUNCTION__, $data, $e->getMessage());
    return nicsrsResponse::error($e->getMessage());
}
```

### Database Operations

Use WHMCS Capsule ORM for all database operations:

```php
use WHMCS\Database\Capsule;

// Query
$result = Capsule::table('nicsrs_sslorders')
    ->where('serviceid', $serviceId)
    ->first();

// Insert
$id = Capsule::table('nicsrs_sslorders')
    ->insertGetId($data);

// Update
Capsule::table('nicsrs_sslorders')
    ->where('id', $id)
    ->update($data);
```

### Security Guidelines

1. **Input Validation**: Always validate and sanitize user input
2. **SQL Injection**: Use parameterized queries via Capsule
3. **XSS Prevention**: Escape output in templates
4. **API Token**: Never log or expose API tokens
5. **Session Validation**: Verify user permissions on all actions

## Testing

### Manual Testing Checklist

Before submitting changes, test the following:

- [ ] New certificate order flow
- [ ] DCV method selection and update
- [ ] Certificate download (all formats)
- [ ] Certificate reissuance
- [ ] Multi-language display
- [ ] Error handling for invalid inputs
- [ ] Admin configuration changes

### Test Data

Use test certificates and domains when possible. Never use production API credentials in development.

## Submitting Changes

### Pull Request Process

1. **Create a Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make Your Changes**
   - Follow coding standards
   - Add comments where necessary
   - Update documentation if needed

3. **Commit Your Changes**
   ```bash
   git commit -m "Add: Brief description of changes"
   ```

   Commit message prefixes:
   - `Add:` New feature
   - `Fix:` Bug fix
   - `Update:` Enhancement to existing feature
   - `Docs:` Documentation changes
   - `Refactor:` Code restructuring

4. **Push and Create PR**
   ```bash
   git push origin feature/your-feature-name
   ```

### PR Requirements

- Clear description of changes
- Reference any related issues
- Include testing steps
- Update CHANGELOG.md if applicable

## Adding New Certificate Types

To add support for a new certificate product:

1. **Update Certificate List** in `nicsrsFunc.php`:
   ```php
   'new-cert-code' => [
       'name' => 'New Certificate Name',
       'maxDomain' => '1',
       'isWildCard' => '0',
       'isMultiDomain' => '0',
       'sslValidationType' => 'dv',
       'sslType' => 'website_ssl',
       'supportWild' => '0',
       'supportNormal' => '1',
       'supportIp' => '0'
   ],
   ```

2. **Test the Certificate**
   - Verify ordering flow
   - Confirm DCV methods work
   - Test certificate issuance

## Adding New Languages

1. **Create Language File**
   ```bash
   cp lang/english.php lang/newlanguage.php
   ```

2. **Translate All Strings**
   ```php
   $_LANG['key'] = 'Translated text';
   ```

3. **Test in WHMCS**
   - Set user language preference
   - Verify all strings display correctly

## Reporting Issues

### Bug Reports

Include the following information:
- WHMCS version
- PHP version
- Module version
- Steps to reproduce
- Expected vs actual behavior
- Error messages (from module log)

### Feature Requests

Describe:
- The feature you'd like
- Use case / benefit
- Any implementation suggestions

## Contact

For questions about contributing:
- **Email**: support@hvn.vn
- **Website**: [https://hvn.vn](https://hvn.vn)

---

**Author**: HVN GROUP