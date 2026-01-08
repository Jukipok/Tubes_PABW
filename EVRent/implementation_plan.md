# Vehicle Image Storage & API

## Goal
Enable vehicle image storage by adding a database column and updating the API to handle file uploads.

## Proposed Changes

### 1. Database
- **Migration**: Create `add_gambar_to_kendaraan_listriks` to add a `gambar` column (string, nullable) to `kendaraan_listriks` table.

### 2. Backend (Model & Controller)
- **`app/Modules/Kendaraan/Models/M_KendaraanListrik.php`**:
    - Add `gambar` to `$fillable`.
- **`app/Modules/Kendaraan/Controllers/Api/KendaraanApiController.php`**:
    - Update `store` method:
        - Validate `gambar` (image, max 2MB).
        - Upload file to `storage/app/public/kendaraan`.
        - Save path to `gambar` column.
    - Update `update` method:
        - Handle new image upload.
        - Delete old image if verified.

## Verification Plan
1. Run migration.
2. Use Postman to `POST /api/kendaraan` with `multipart/form-data` including a file.
3. Verify file exists in `storage/app/public/kendaraan`.
4. Verify database record has the path.
