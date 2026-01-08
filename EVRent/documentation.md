# EVRent Project Documentation

## 1. Project Structure (HMVC)
This project utilizes a **Hierarchical Model-View-Controller (HMVC)** architecture to improve maintainability and scalability. Each major feature is encapsulated within its own "Module" directory in `app/Modules/`.

### Folder Structure
```
app/
├── Modules/
│   ├── Auth/           # Authentication, User Management, Profile
│   ├── Kendaraan/      # Vehicle Management (Catalog, CRUD)
│   ├── Transaksi/      # Booking, Transaction History, Dashboard
│   ├── Pembayaran/     # Payment Gateway (Xendit), Invoicing
│   └── Laporan/        # Reports, Reviews (Ulasan)
│
├── Http/Controllers/   # Base Controllers
└── ...
```

### Module Contents
Each module typically contains:
- `Controllers/`: Logic for Web and API requests.
- `Models/`: Database models specific to the module.
- `Routes/`: `web.php` for browser routes (optional, as main routes are often centralized or loaded dynamically).

---

## 2. API Documentation
All API routes are prefixed with `/api`.
**Base URL**: `http://localhost:8000/api` (or your domain)

### A. Authentication (`Auth`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` | `/register` | Register new user | No |
| `POST` | `/login` | Login and get API Token | No |
| `POST` | `/logout` | Logout (revoke token) | **Yes** |
| `GET` | `/user` | Get current user profile | **Yes** |
| `PUT` | `/user` | Update profile (Name, Phone, etc) | **Yes** |

### B. Kendaraan / Vehicles (`Kendaraan`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/kendaraan` | List available vehicles | No |
| `GET` | `/kendaraan/{id}` | Get vehicle details | No |
| `POST` | `/kendaraan` | Create Vehicle (upload `gambar`) | **Yes (Owner/Admin)** |
| `PUT` | `/kendaraan/{id}` | Update Vehicle | **Yes (Owner/Admin)** |
| `DELETE` | `/kendaraan/{id}` | Delete Vehicle | **Yes (Owner/Admin)** |

> **Note**: For creating/updating vehicles with images, use `multipart/form-data` and the field `gambar`.

### C. Transaksi / Booking (`Transaksi`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` | `/booking` | Create new booking | **Yes** |
| `GET` | `/booking/history` | Get user's booking history | **Yes** |
| `GET` | `/booking/{id}` | Get booking details | **Yes** |
| `DELETE` | `/booking/{id}` | Cancel booking | **Yes** |

### D. Pembayaran / Payment (`Pembayaran`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` | `/payment/create-invoice` | Create Xendit Invoice | **Yes** |
| `POST` | `/xendit/webhook` | Xendit Callback Handler | No (Xendit only) |

### E. Dashboard (`Transaksi`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/dashboard/owner-stats` | Owner Statistics (Income, etc) | **Yes (Owner)** |

### F. Ulasan / Reviews (`Laporan`)
| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/ulasan` | List reviews | **Yes** |
| `POST` | `/ulasan` | Submit review | **Yes** |

---

## 3. Storage
- **Public Images**: `storage/app/public/kendaraan`
- **Access**: Via `asset('storage/kendaraan/filename.jpg')`
- **MIME**: Images (jpeg, png, jpg, gif, svg)
- **Max Size**: 2MB
