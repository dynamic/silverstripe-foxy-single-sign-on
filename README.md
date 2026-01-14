# SilverStripe Foxy Single Sign On

An add-on module for SilverStripe Foxy that enables Single Sign-On with your Foxy.io store.

[![Latest Stable Version](https://poser.pugx.org/dynamic/silverstripe-foxy-single-sign-on/v/stable)](https://packagist.org/packages/dynamic/silverstripe-foxy-single-sign-on)
[![Total Downloads](https://poser.pugx.org/dynamic/silverstripe-foxy-single-sign-on/downloads)](https://packagist.org/packages/dynamic/silverstripe-foxy-single-sign-on)
[![License](https://poser.pugx.org/dynamic/silverstripe-foxy-single-sign-on/license)](https://packagist.org/packages/dynamic/silverstripe-foxy-single-sign-on)

## Requirements

* PHP ^8.1
* SilverStripe CMS ^5.0
* dynamic/silverstripe-foxy-api ^2.0

## Installation

```bash
composer require dynamic/silverstripe-foxy-single-sign-on ^2.0
```

## Configuration

### Foxy Store Settings

In your [Foxy.io store admin](https://admin.foxy.io/), configure the following settings:

1. **Customer Password Hash Type**: `BCrypt`
2. **Customer Password Hash Config**: `10` (bcrypt cost factor - matches Silverstripe default)
3. **Enable Single Sign On**: ✓ **Checked** (Required)
4. **Single Sign On URL**: `https://www.example.com/foxysso`

### Silverstripe Configuration

No special password configuration is required - Silverstripe CMS 5 uses bcrypt by default, which is compatible with Foxy's BCrypt password hash type.

### Optional: API Configuration

To enable customer syncing between Silverstripe and Foxy, configure API credentials. Create a **Private Integration** at [Foxy Integrations](https://admin.foxy.io/admin.php?ThisAction=AddIntegration).

**Recommended: Add to `.env`:**

```bash
FOXY_API_CLIENT_ID="your-client-id"
FOXY_API_CLIENT_SECRET="your-client-secret"
FOXY_API_ACCESS_TOKEN="your-access-token"
FOXY_API_REFRESH_TOKEN="your-refresh-token"
```

**Enable API in YAML config:**

```yaml
Dynamic\Foxy\API\Client\APIClient:
  enable_api: true
```

## Documentation

* [Documentation readme](docs/en/readme.md)

## Maintainers

* [Dynamic](https://www.dynamicagency.com) (<dev@dynamicagency.com>)

## Bugtracker

Bugs are tracked in the issues section of this repository. Before submitting an issue please read over 
existing issues to ensure yours is unique.

If the issue does look like a new bug:

- Create a new issue
- Describe the steps required to reproduce your issue, and the expected outcome
- Describe your environment: SilverStripe version, PHP version, any installed modules

Please report security issues to the module maintainers directly.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.

## License

See [License](license.md)
