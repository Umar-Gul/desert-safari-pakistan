# Desert Safari Pakistan — Tour Booking Platform

A full-featured desert safari tour-booking website for Pakistan (Cholistan, Thar, Thal and Kharan), built with **Laravel 12**. It includes a public booking site and a complete admin dashboard for managing tours, bookings, availability, payments, content and users.

> Live demo: [add URL] · Screenshots: see `/docs/screenshots`

## Features

**Public site**
- Browse tours by category and destination
- Tour detail pages with galleries, itinerary, pricing and trust badges
- Booking flow with availability checking
- Email confirmations and downloadable PDF booking voucher
- SEO-ready pages (meta tags, sitemap, structured content)

**Admin dashboard**
- Dashboard overview and site settings
- Tour editor (content, media, pricing, categories)
- Bookings, availability calendar and payment tracking
- Review/content moderation and user management
- Role-based access control (admin / staff permissions)

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Database | MySQL |
| Frontend | Blade, Tailwind CSS, Alpine.js |
| Auth | Laravel Breeze |
| Packages | spatie/laravel-permission, spatie/laravel-medialibrary, mews/purifier |
| Testing | PHPUnit / Pest feature and unit tests |

## Getting Started

### Requirements
- PHP 8.2+, Composer
- Node.js 18+ and npm
- MySQL (XAMPP works fine on Windows)

### Installation

```bash
git clone https://github.com/Umar-Gul/desert-safari.git
cd desert-safari

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create a MySQL database (for example `desert_safari`), then set the credentials in `.env`:

```env
DB_DATABASE=desert_safari
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations, seed demo data and link storage:

```bash
php artisan migrate --seed
php artisan storage:link
```

Start the app:

```bash
npm run dev
php artisan serve
```

Open http://127.0.0.1:8000

### Admin access

Seeded admin credentials are created by the database seeder. Check `database/seeders` for the default account and **change the password immediately** in any non-local environment.

## Running Tests

```bash
php artisan test
```

## Project Structure

```
app/
  Enums/          Domain enums (statuses, etc.)
  Models/         Eloquent models
  Observers/      Model observers
  Services/       Business logic (booking, availability, payments)
  Policies/       Authorization policies
  Http/Requests/  Form request validation
database/
  migrations/  factories/  seeders/
resources/views/  Blade templates (public + admin)
tests/            Feature and unit tests
```

## Deployment Checklist

- Set `APP_ENV=production` and `APP_DEBUG=false`
- Configure real database, mail and storage settings in `.env`
- Run `php artisan migrate --force`
- Run `php artisan config:cache route:cache view:cache`
- Build assets with `npm run build`
- Point the web server document root to `/public`

## Author

**Umar Gul** — Full Stack Developer (MERN · Laravel)
[GitHub](https://github.com/Umar-Gul) · [LinkedIn](https://linkedin.com/in/engr-umar-gul-670a69387) · [Portfolio](https://umar-gul-portfolio.netlify.app)

## License

This project is for portfolio and demonstration purposes. Add a license here if you plan to open-source it.