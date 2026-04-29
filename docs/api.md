# MHC Parish System — API Documentation

## Base URL
`https://your-domain.com`

## Authentication
Most API endpoints require authentication via Laravel Sanctum.
Include the token in the `Authorization` header:
```
Authorization: Bearer {token}
```

---

## Public Endpoints

### QR Code Verification
**GET** `/verify/{token}`
- Returns HTML verification page

**GET** `/api/verify/{token}`
- Returns JSON verification data
- Response: `{ valid: bool, data: { ... } }`

### Chatbot
**POST** `/chatbot`
- Body: `{ message: string, session_id: string }`
- Response: `{ message: string, intent: string }`

**POST** `/chatbot/escalate`
- Body: `{ session_id: string, message: string }`
- Response: `{ message: string }`

---

## Authenticated Endpoints

### Parishioner Search (Admin)
**GET** `/admin/parishioners/search?q={term}`
- Returns: `[{ id, text, extra }]`

### Dashboard Stats
**GET** `/api/dashboard/stats?period={week|month|year}`
- Returns aggregated statistics

### Booking Calendar Events
**GET** `/api/bookings/calendar-events`
- Returns bookings formatted for calendar display

---

## Payment Webhooks

### PayMongo Webhook
**POST** `/webhooks/paymongo`
- Handles `payment.paid` and `payment.failed` events
- Requires `Paymongo-Signature` header for verification

---

## Admin Routes (require admin role)

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/admin/dashboard` | Dashboard |
| GET/POST | `/admin/parishioners` | List/Create parishioners |
| GET/PUT/DELETE | `/admin/parishioners/{id}` | Show/Update/Delete |
| GET/POST | `/admin/sacramental-records` | List/Create records |
| GET/POST | `/admin/bookings` | List/Create bookings |
| POST | `/admin/bookings/{id}/confirm` | Confirm booking |
| POST | `/admin/bookings/{id}/cancel` | Cancel booking |
| GET/POST | `/admin/certificates` | List/Create certificates |
| GET | `/admin/certificates/{id}/download` | Download PDF |
| GET | `/admin/payments` | List payments |
| POST | `/admin/payments/{id}/refund` | Refund payment |
| POST | `/admin/payments/{id}/void` | Void payment |
| GET | `/admin/payments/report` | Financial report |

---

## Error Responses

All errors return JSON with:
```json
{
  "message": "Error description",
  "errors": { "field": ["validation message"] }
}
```

HTTP Status Codes:
- `200` — Success
- `201` — Created
- `401` — Unauthenticated
- `403` — Forbidden
- `404` — Not Found
- `422` — Validation Error
- `500` — Server Error
