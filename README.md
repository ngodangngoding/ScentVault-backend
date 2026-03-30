# ScentVault Backend API

ScentVault Backend is a robust API built with the Laravel framework, designed to manage perfume-related data including brands, scent notes, and usage occasions.

## Core Features

- **Brand Management**: Full CRUD operations for perfume brands.
- **Note Management**: Manage various fragrance notes and compositions.
- **Occasion Management**: Organize perfumes based on specific moments or events.
- **Automatic API Documentation**: Real-time OpenAPI documentation generation using Scramble.

## Technology Stack

- **PHP**: ^8.3
- **Framework**: Laravel 13
- **Database**: SQLite (default) / MySQL
- **Documentation**: Dedoc Scramble

## Installation & Setup

Follow these steps to get the project running on your local machine:

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd ScentVault-backend
   ```

2. **Run the Setup Script**
   For convenience, a setup script has been provided in the project configuration. Run:
   ```bash
   composer setup
   ```
   *This command will automatically install dependencies, copy the `.env` file, generate an application key, and run database migrations.*

   **Manual alternative:**
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   ```

3. **Start the Development Server**
   ```bash
   php artisan serve
   ```
   The application will be accessible at `http://127.0.0.1:8000`.

## Accessing API Documentation

This project uses **Scramble** for interactive API documentation (similar to Swagger/Redoc). You don't need to manually write JSON/YAML documentation files; they are generated automatically from your code.

To explore all endpoints, required parameters, and test the API live:

1. Start the server using `php artisan serve`.
2. Open your browser and navigate to:
   **[http://127.0.0.1:8000/docs/api](http://127.0.0.1:8000/docs/api)**

In the documentation interface, you can see details for each resource:
- `GET /api/brands` - List all brands
- `POST /api/brands` - Create a new brand
- `GET /api/notes` - List all fragrance notes
- `POST /api/notes` - Create a new fragrance note
- `GET /api/occasions` - List all occasions
- `POST /api/occasions` - Create a new occasion
- ... and other detail/update/delete endpoints.

## API Structure

All API routes are defined in `routes/api.php` and use the `/api` prefix. When making requests from external tools like Postman or a frontend application, use the following URL format:
`http://127.0.0.1:8000/api/{resource}`
