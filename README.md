# Event Booking System API

A Laravel-based Event Booking System with Role-Based Access Control (RBAC), simulated payments(mocked), and optimized performance.

## Features

- **Advanced RBAC**: Managed via Spatie (Roles: Admin, Organizer, Customer).
- **Event Management**: Organizers can create and manage their own events and tickets.
- **Booking Workflow**: Real-time inventory (ticket quantity) management with double-booking protection.
- **Mock Payments**: Simulated payment processing using a dedicated service class.
- **Queued Notifications**: Asynchronous email and database notifications for booking confirmations.
- **Optimization**: Frequently accessed events are cached with automatic invalidation.
- **RESTful API**: Consistent response structure using a dedicated `ApiResponse` trait.
- **Automated Testing**: Comprehensive Feature and Unit tests (Authentication, Booking, Payments, etc.).

---

## Setup Instructions

### 1. Prerequisites

- PHP 8.2+
- Composer
- MySQL
- Redis (optional, for caching/queues)

### 2. Installation

```bash
# Clone the repository
git clone https://github.com/preyash009/automatedPros-assessment.git
cd site

# Install dependencies
composer install
npm install && npm run build
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
CACHE_STORE=database
```

### 4. Database Setup & Seeding

```bash
# Run migrations
php artisan migrate

# Seed Roles and Permissions (CRITICAL)
php artisan db:seed --class=RolePermissionSeeder

# Seed Demo Data (Admins, Organizers, Events, Tickets, Bookings, Payments)
php artisan db:seed --class=DemoDataSeeder
```

### 5. Running the Application

```bash
# Start the local server
php artisan serve

# Start the queue worker (for notifications)
php artisan queue:work
```

---

## Testing

Run the automated test suite to verify the system:

```bash
php artisan test
```

---

## Postman Collection

A complete Postman collection is included in the root directory: `Event_Booking_System.postman_collection.json`.

**How to use:**

1. Import the `.json` file into Postman.
2. The `base_url` variable is set to `http://localhost:8000` by default.
3. Use the **Login** request first; it includes a test script that automatically sets the `auth_token` variable for all subsequent requests.
4. Ensure your local server is running (`php artisan serve`) before testing.

---

## API Documentation

All API responses follow a consistent structure:

```json
{
    "status": "success",
    "message": "Resource message here",
    "data": { ... }
}
```

### Authentication

| Method | Endpoint        | Description          | Access        |
| :----- | :-------------- | :------------------- | :------------ |
| `POST` | `/api/register` | Register a new user  | Public        |
| `POST` | `/api/login`    | Login and get token  | Public        |
| `POST` | `/api/logout`   | Revoke current token | Authenticated |
| `GET`  | `/api/me`       | Get profile details  | Authenticated |

### Role & Permission Management

| Method   | Endpoint                | Description             | Access |
| :------- | :---------------------- | :---------------------- | :----- |
| `GET`    | `/api/roles`            | List all roles          | Admin  |
| `POST`   | `/api/roles`            | Create a new role       | Admin  |
| `GET`    | `/api/roles/{id}`       | View role details       | Admin  |
| `PUT`    | `/api/roles/{id}`       | Update role permissions | Admin  |
| `DELETE` | `/api/roles/{id}`       | Delete a role           | Admin  |
| `GET`    | `/api/permissions`      | List all permissions    | Admin  |
| `POST`   | `/api/permissions`      | Create permission       | Admin  |
| `GET`    | `/api/permissions/{id}` | View permission details | Admin  |
| `PUT`    | `/api/permissions/{id}` | Update permission       | Admin  |
| `DELETE` | `/api/permissions/{id}` | Delete permission       | Admin  |

### Event Management

| Method   | Endpoint           | Description                   | Access                |
| :------- | :----------------- | :---------------------------- | :-------------------- |
| `GET`    | `/api/events`      | List events (Filtered/Cached) | Authenticated         |
| `GET`    | `/api/events/{id}` | View event with tickets       | Authenticated         |
| `POST`   | `/api/events`      | Create new event              | Organizer/Admin       |
| `PUT`    | `/api/events/{id}` | Update event                  | Organizer (Own)/Admin |
| `DELETE` | `/api/events/{id}` | Delete event                  | Organizer (Own)/Admin |

### Ticket Management

| Method   | Endpoint                         | Description           | Access          |
| :------- | :------------------------------- | :-------------------- | :-------------- |
| `POST`   | `/api/events/{event_id}/tickets` | Add tickets to event  | Organizer/Admin |
| `PUT`    | `/api/tickets/{id}`              | Update ticket details | Organizer/Admin |
| `DELETE` | `/api/tickets/{id}`              | Remove ticket         | Organizer/Admin |

### Booking & Payments

| Method | Endpoint                     | Description                     | Access               |
| :----- | :--------------------------- | :------------------------------ | :------------------- |
| `POST` | `/api/tickets/{id}/bookings` | Book a ticket                   | Customer/Admin       |
| `GET`  | `/api/bookings`              | View my booking history         | Customer/Admin       |
| `PUT`  | `/api/bookings/{id}/cancel`  | Cancel booking (restores stock) | Customer (Own)/Admin |
| `POST` | `/api/bookings/{id}/payment` | Process mock payment            | Customer (Own)       |
| `GET`  | `/api/payments/{id}`         | View payment details            | Customer (Own)/Admin |

---

## Default Seeded Accounts

| Role          | Count | Permissions                      |
| :------------ | :---- | :------------------------------- |
| **Admin**     | 2     | Full System Access               |
| **Organizer** | 3     | Manage their own Events/Tickets  |
| **Customer**  | 10    | Book tickets & View own bookings |

_Password for all accounts: `password`_
