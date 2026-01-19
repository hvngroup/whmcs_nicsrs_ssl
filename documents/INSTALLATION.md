# NicSRS SSL Module - Installation Guide

## Prerequisites

Before installing the NicSRS SSL module, ensure your environment meets the following requirements:

### Server Requirements
- PHP 7.2 or higher
- cURL extension enabled
- OpenSSL extension enabled
- JSON extension enabled
- ZipArchive class available (for certificate downloads)

### WHMCS Requirements
- WHMCS version 7.0 or higher
- MySQL/MariaDB database
- Write permissions on the modules directory

### Account Requirements
- Active NicSRS reseller account
- Valid API Token from NicSRS portal

## Step-by-Step Installation

### Step 1: Download the Module

Download the latest version of the `nicsrs_ssl` module package.

### Step 2: Upload Files

Upload the entire `nicsrs_ssl` folder to your WHMCS installation:

```
your_whmcs_root/
└── modules/
    └── servers/
        └── nicsrs_ssl/          ← Upload here
            ├── nicsrs_ssl.php
            ├── lang/
            ├── src/
            └── view/
```

**Using FTP/SFTP:**
```bash
# Connect to your server
sftp user@your-server.com

# Navigate to modules directory
cd /path/to/whmcs/modules/servers/

# Upload the module folder
put -r nicsrs_ssl
```

**Using Command Line:**
```bash
# Extract and copy
unzip nicsrs_ssl.zip -d /path/to/whmcs/modules/servers/
```

### Step 3: Set File Permissions

Set appropriate permissions for the module files:

```bash
# Set directory permissions
find /path/to/whmcs/modules/servers/nicsrs_ssl -type d -exec chmod 755 {} \;

# Set file permissions
find /path/to/whmcs/modules/servers/nicsrs_ssl -type f -exec chmod 644 {} \;

# Ensure the main module file is readable
chmod 644 /path/to/whmcs/modules/servers/nicsrs_ssl/nicsrs_ssl.php
```

### Step 4: Verify Installation

1. Log in to your WHMCS Admin Area
2. Navigate to **Setup → Products/Services → Products/Services**
3. Click **Create a New Product** or edit an existing one
4. Go to the **Module Settings** tab
5. In the **Module Name** dropdown, you should see **nicsrs_ssl**

If the module doesn't appear:
- Clear WHMCS template cache
- Check file permissions
- Verify the folder structure is correct

### Step 5: Database Table Setup

The module automatically creates the required database table (`nicsrs_sslorders`) when first accessed by a customer. No manual database setup is required.

**Manual Table Creation (Optional):**

If you prefer to create the table manually:

```sql
CREATE TABLE IF NOT EXISTS `nicsrs_sslorders` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `userid` int(10) NOT NULL,
    `serviceid` int(10) NOT NULL,
    `addon_id` text NOT NULL,
    `remoteid` text NOT NULL,
    `module` text NOT NULL,
    `certtype` text NOT NULL,
    `configdata` text NOT NULL,
    `provisiondate` date NOT NULL,
    `completiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `status` text NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```

## Product Configuration

### Creating an SSL Product

1. Go to **Setup → Products/Services → Products/Services**
2. Select a product group or create a new one
3. Click **Create a New Product**
4. Fill in the basic product details:
   - **Product Type**: Other
   - **Product Name**: e.g., "Sectigo PositiveSSL Certificate"
   - **Welcome Email**: None (or custom SSL email)

### Module Settings

Navigate to the **Module Settings** tab and configure:

| Setting | Description |
|---------|-------------|
| **Module Name** | Select `nicsrs_ssl` |
| **Certificate Type** | Choose the SSL certificate type from dropdown |
| **NicSRS API Token** | Enter your API token from NicSRS portal |

### Pricing Configuration

Navigate to the **Pricing** tab:
- Set up pricing for different billing cycles (Annual recommended for SSL)
- Configure any setup fees if applicable

### Custom Fields (Optional)

If you need to collect additional information, go to **Custom Fields** tab:
- Domain Name (if not using domain field)
- Organization Name (for OV/EV certificates)
- Additional SANs quantity

## Obtaining Your API Token

1. Log in to [NicSRS Portal](https://portal.nicsrs.com)
2. Navigate to **Account Settings** or **API Settings**
3. Generate or copy your API Token
4. Keep this token secure - it provides full access to your account

## Post-Installation Verification

### Test the Module

1. Create a test product with the module
2. Place a test order in WHMCS
3. Access the Client Area → Services → Your SSL Order
4. Verify the certificate configuration form displays correctly

### Check API Connectivity

The module makes API calls to `https://portal.nicsrs.com/ssl`. Verify your server can reach this endpoint:

```bash
curl -I https://portal.nicsrs.com/ssl/validate
```

Expected response: HTTP 200 or appropriate API response

### Enable Module Logging

For troubleshooting, enable module logging:

1. Go to **Configuration → System Settings → Logging**
2. Enable **Module Logging**
3. Access logs at **Utilities → Logs → Module Log**

## Language Configuration

The module supports multiple languages:

| Language | File |
|----------|------|
| English | `lang/english.php` |
| Chinese (Traditional) | `lang/chinese.php` |
| Chinese (Simplified) | `lang/chinese-cn.php` |

The module automatically detects the user's WHMCS language setting.

**Adding Custom Languages:**

1. Copy `lang/english.php` to `lang/yourlanguage.php`
2. Translate all `$_LANG` strings
3. The file name must match the WHMCS language name (lowercase)

## Temporary Directory Configuration

Certificate downloads use `/tmp/cert/customer_certs/` for temporary file storage.

**Custom Temporary Directory:**

If needed, modify the path in `nicsrsFunc.php`:

```php
$filepath = "/your/custom/path/";
```

Ensure the directory:
- Exists or can be created
- Has write permissions (777 or appropriate)
- Is outside the web root for security

## Security Recommendations

1. **Protect API Token**: Store securely, never expose in client-side code
2. **Use HTTPS**: Ensure your WHMCS installation uses HTTPS
3. **Regular Updates**: Keep the module updated for security patches
4. **Access Control**: Limit admin access to module settings
5. **Log Monitoring**: Regularly review module logs for anomalies

## Uninstallation

To remove the module:

1. Delete all products using the nicsrs_ssl module first
2. Remove the module folder:
   ```bash
   rm -rf /path/to/whmcs/modules/servers/nicsrs_ssl
   ```
3. (Optional) Remove the database table:
   ```sql
   DROP TABLE IF EXISTS nicsrs_sslorders;
   ```

## Troubleshooting Installation

### Module Not Appearing in Dropdown

- Verify folder is named exactly `nicsrs_ssl`
- Check main file is named `nicsrs_ssl.php`
- Clear WHMCS cache: Delete `templates_c/*.php` files
- Check PHP error logs for syntax errors

### Permission Denied Errors

```bash
# Fix ownership
chown -R www-data:www-data /path/to/whmcs/modules/servers/nicsrs_ssl

# Fix permissions
chmod -R 755 /path/to/whmcs/modules/servers/nicsrs_ssl
```

### API Connection Issues

1. Test connectivity: `curl https://portal.nicsrs.com`
2. Check firewall rules for outbound HTTPS (port 443)
3. Verify SSL certificates are up to date on your server
4. Check if cURL is compiled with SSL support

---

**Author**: HVN GROUP  
**Support**: [https://hvn.vn](https://hvn.vn)