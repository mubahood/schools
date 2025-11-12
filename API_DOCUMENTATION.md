# School Management System - API Documentation

## Base URL
```
https://your-domain.com/api/v1
```

## Authentication
All API requests require authentication using Bearer token or session.

```http
Authorization: Bearer {your-token}
```

---

## 📱 SMS API Endpoints

### Send SMS
Send a single SMS message.

**Endpoint:** `POST /api/v1/sms/send`

**Request:**
```json
{
  "receiver_number": "+256783204665",
  "message_body": "Your message here",
  "enterprise_id": 7,
  "administrator_id": 123
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "SMS sent successfully",
  "data": {
    "id": 19566,
    "status": "Sent",
    "receiver_number": "+256783204665",
    "api_message_id": "3122752",
    "cost": 50,
    "sent_at": "2025-11-08T10:30:00Z"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Invalid phone number",
  "errors": {
    "receiver_number": ["Invalid phone number format"]
  }
}
```

---

### Send Bulk SMS
Send SMS to multiple recipients.

**Endpoint:** `POST /api/v1/sms/bulk-send`

**Request:**
```json
{
  "receivers": ["+256783204665", "+256701234567"],
  "message_body": "Bulk message",
  "enterprise_id": 7
}
```

**Response:**
```json
{
  "success": true,
  "message": "Bulk SMS queued",
  "data": {
    "total": 2,
    "queued": 2,
    "failed": 0,
    "job_id": "bulk-sms-1699440000"
  }
}
```

---

### Get SMS Status
Check status of sent SMS.

**Endpoint:** `GET /api/v1/sms/{id}/status`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 19566,
    "status": "Sent",
    "delivery_status": "Delivered",
    "receiver_number": "+256783204665",
    "sent_at": "2025-11-08T10:30:00Z",
    "delivered_at": "2025-11-08T10:30:15Z"
  }
}
```

---

## 📊 Attendance API Endpoints

### Submit Attendance
Submit attendance for a class session.

**Endpoint:** `POST /api/v1/attendance/submit`

**Request:**
```json
{
  "session_id": 420,
  "enterprise_id": 7,
  "type": "CLASS_ATTENDANCE",
  "participants": [
    {"student_id": 123, "is_present": 1},
    {"student_id": 124, "is_present": 0}
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Attendance recorded",
  "data": {
    "session_id": 420,
    "total_students": 2,
    "present": 1,
    "absent": 1,
    "recorded_at": "2025-11-08T10:30:00Z"
  }
}
```

---

### Generate Attendance Report
Generate PDF attendance report.

**Endpoint:** `POST /api/v1/reports/attendance`

**Request:**
```json
{
  "enterprise_id": 7,
  "start_date": "2025-11-01",
  "end_date": "2025-11-08",
  "type": "CLASS_ATTENDANCE",
  "teacher_1_on_duty_id": 45,
  "teacher_2_on_duty_id": 46
}
```

**Response:**
```json
{
  "success": true,
  "message": "Report generated",
  "data": {
    "report_id": 123,
    "pdf_url": "https://your-domain.com/session-report-pdf/123",
    "generated_at": "2025-11-08T10:30:00Z"
  }
}
```

---

## 👥 Student Management

### Get Students
Get list of students.

**Endpoint:** `GET /api/v1/students`

**Query Parameters:**
- `enterprise_id` (required)
- `class_id` (optional)
- `status` (optional: 1=active, 0=inactive)
- `page` (optional, default: 1)
- `per_page` (optional, default: 50)

**Response:**
```json
{
  "success": true,
  "data": {
    "students": [
      {
        "id": 123,
        "name": "John Doe",
        "sex": "Male",
        "current_class": {
          "id": 174,
          "name": "Primary 6"
        },
        "phone_number": "+256783204665",
        "status": 1
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 10,
      "per_page": 50,
      "total": 500
    }
  }
}
```

---

## 💰 Wallet Management

### Get Wallet Balance
Get enterprise wallet balance.

**Endpoint:** `GET /api/v1/wallet/balance/{enterprise_id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "enterprise_id": 7,
    "balance": 299505,
    "currency": "UGX",
    "last_transaction": "2025-11-08T10:30:00Z"
  }
}
```

---

### Get Wallet Transactions
Get wallet transaction history.

**Endpoint:** `GET /api/v1/wallet/transactions/{enterprise_id}`

**Query Parameters:**
- `start_date` (optional)
- `end_date` (optional)
- `page` (optional)

**Response:**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 8428,
        "amount": -50,
        "type": "SMS",
        "details": "Sent 1 messages to +256783204665",
        "balance_after": 299505,
        "created_at": "2025-11-08T10:30:00Z"
      }
    ],
    "summary": {
      "total_debit": -250,
      "total_credit": 0,
      "transaction_count": 5
    }
  }
}
```

---

## 📈 Analytics

### Get Dashboard Stats
Get overview statistics.

**Endpoint:** `GET /api/v1/analytics/dashboard/{enterprise_id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "students": {
      "total": 500,
      "active": 485,
      "boys": 260,
      "girls": 240
    },
    "attendance": {
      "today": 450,
      "rate": 92.8,
      "trend": "up"
    },
    "sms": {
      "sent_today": 120,
      "failed_today": 2,
      "success_rate": 98.3
    },
    "wallet": {
      "balance": 299505,
      "spent_today": 6000
    }
  }
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 201  | Created |
| 400  | Bad Request |
| 401  | Unauthorized |
| 403  | Forbidden |
| 404  | Not Found |
| 422  | Validation Error |
| 429  | Too Many Requests |
| 500  | Internal Server Error |

---

## Rate Limiting

API requests are limited to:
- **60 requests per minute** for authenticated users
- **30 requests per minute** for unauthenticated requests

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
```

---

## Webhooks

### SMS Delivery Webhook
Receive delivery notifications.

**URL:** Configure in settings
**Method:** POST

**Payload:**
```json
{
  "event": "sms.delivered",
  "message_id": 19566,
  "api_message_id": "3122752",
  "status": "Delivered",
  "delivered_at": "2025-11-08T10:30:15Z"
}
```

---

## SDKs & Libraries

### PHP Example
```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://your-domain.com/api/v1/',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ]
]);

$response = $client->post('sms/send', [
    'json' => [
        'receiver_number' => '+256783204665',
        'message_body' => 'Test message',
        'enterprise_id' => 7,
    ]
]);

$data = json_decode($response->getBody(), true);
```

### JavaScript Example
```javascript
fetch('https://your-domain.com/api/v1/sms/send', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    receiver_number: '+256783204665',
    message_body: 'Test message',
    enterprise_id: 7,
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## Support

For API support, contact:
- Email: api-support@your-domain.com
- Documentation: https://your-domain.com/api/docs
- Status Page: https://status.your-domain.com
