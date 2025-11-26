# API Documentation

Complete API reference for the MSSQL API Dashboard.

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication

All API requests must include an API key in the request header:

```
X-API-Key: your_api_key_here
```

### Obtaining an API Key

1. Log in to the admin dashboard at `https://your-domain.com/login`
2. Navigate to the Dashboard
3. Click "Generate API Key" or "Regenerate API Key"
4. Copy the displayed key (it will only be shown once)

### Authentication Errors

**Missing API Key (401)**

```json
{
  "error": "Missing API key",
  "message": "X-API-Key header is required"
}
```

**Invalid API Key (401)**

```json
{
  "error": "Invalid API key",
  "message": "The provided API key is invalid or inactive"
}
```

## Endpoints

### 1. List Clients

Retrieve a paginated list of clients with optional filtering and sorting.

**Endpoint:** `GET /api/v1/clients`

**Headers:**
```
X-API-Key: your_api_key_here
Content-Type: application/json
```

**Query Parameters:**

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `clientNum` | integer | No | Filter by exact client number | `1234567890` |
| `payroll` | string | No | Filter by exact payroll value | `MOE` |
| `active` | boolean | No | Filter by active status | `true` or `false` |
| `nameContains` | string | No | Search for substring in name field | `CHANDA` |
| `clientNameContains` | string | No | Search for substring in client name | `LIMITED` |
| `dateAddedFrom` | date | No | Filter by date added (from) | `2023-01-01` |
| `dateAddedTo` | date | No | Filter by date added (to) | `2023-12-31` |
| `lastTxFrom` | date | No | Filter by last transaction date (from) | `2025-01-01` |
| `lastTxTo` | date | No | Filter by last transaction date (to) | `2025-12-31` |
| `sort` | string | No | Sort field (prefix with `-` for DESC) | `clientNum`, `-dateAdded`, `lastTDate` |
| `page` | integer | No | Page number (default: 1) | `1` |
| `pageSize` | integer | No | Results per page (default: 50, max: 500) | `100` |

**Example Request:**

```bash
curl -X GET "https://your-domain.com/api/v1/clients?page=1&pageSize=50&active=true&sort=-dateAdded" \
  -H "X-API-Key: your_api_key_here" \
  -H "Content-Type: application/json"
```

**Success Response (200 OK):**

```json
{
  "data": [
    {
      "clientNum": 1234567890,
      "payroll": "MOE",
      "clientName": "CHANDA LIMITED",
      "name": "CHANDA",
      "title": "MR",
      "initial": "C",
      "salutation": "Mr Chanda",
      "address1": "Plot 10",
      "address2": "Street Name",
      "address3": "Town",
      "telHMasked": "*******1234",
      "telWMasked": "*******4321",
      "emailMasked": "c*****@example.com",
      "contact": "Office",
      "active": true,
      "dateAdded": "2023-04-25T10:30:00Z",
      "lastTDate": "2025-07-02T14:05:00Z",
      "phAdd1": "Physical Address Line 1",
      "phAdd2": "Physical Address Line 2",
      "phAdd3": "Physical Address Line 3",
      "phAdd4": "Physical Address Line 4",
      "clientType": 1
    }
  ],
  "meta": {
    "page": 1,
    "pageSize": 50,
    "total": 3471
  }
}
```

**Error Response (400 Bad Request):**

```json
{
  "error": "Invalid parameters",
  "messages": {
    "pageSize": [
      "The page size must not be greater than 500."
    ]
  }
}
```

---

### 2. Get Single Client

Retrieve a single client record by client number.

**Endpoint:** `GET /api/v1/clients/{clientNum}`

**Headers:**
```
X-API-Key: your_api_key_here
Content-Type: application/json
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `clientNum` | integer | Yes | The client number |

**Example Request:**

```bash
curl -X GET "https://your-domain.com/api/v1/clients/1234567890" \
  -H "X-API-Key: your_api_key_here" \
  -H "Content-Type: application/json"
```

**Success Response (200 OK):**

```json
{
  "clientNum": 1234567890,
  "payroll": "MOE",
  "clientName": "CHANDA LIMITED",
  "name": "CHANDA",
  "title": "MR",
  "initial": "C",
  "salutation": "Mr Chanda",
  "address1": "Plot 10",
  "address2": "Street Name",
  "address3": "Town",
  "telHMasked": "*******1234",
  "telWMasked": "*******4321",
  "emailMasked": "c*****@example.com",
  "contact": "Office",
  "active": true,
  "dateAdded": "2023-04-25T10:30:00Z",
  "lastTDate": "2025-07-02T14:05:00Z",
  "phAdd1": "Physical Address Line 1",
  "phAdd2": "Physical Address Line 2",
  "phAdd3": "Physical Address Line 3",
  "phAdd4": "Physical Address Line 4",
  "clientType": 1
}
```

**Error Response (404 Not Found):**

```json
{
  "error": "Not found",
  "message": "Client not found"
}
```

---

## Response Fields

### Client Object

| Field | Type | Description |
|-------|------|-------------|
| `clientNum` | integer | Unique client identifier |
| `payroll` | string | Payroll identifier |
| `clientName` | string | Official client/company name |
| `name` | string | Client name |
| `title` | string | Title (e.g., MR, MRS, MS) |
| `initial` | string | Client initial |
| `salutation` | string | Full salutation |
| `address1` | string | Address line 1 |
| `address2` | string | Address line 2 |
| `address3` | string | Address line 3 |
| `telHMasked` | string | Masked home telephone (last 4 digits visible) |
| `telWMasked` | string | Masked work telephone (last 4 digits visible) |
| `emailMasked` | string | Masked email (first char + domain visible) |
| `contact` | string | Contact information |
| `active` | boolean | Whether the client is active |
| `dateAdded` | datetime | Date client was added (ISO 8601) |
| `lastTDate` | datetime | Last transaction date (ISO 8601) |
| `phAdd1` | string | Physical address line 1 |
| `phAdd2` | string | Physical address line 2 |
| `phAdd3` | string | Physical address line 3 |
| `phAdd4` | string | Physical address line 4 |
| `clientType` | integer | Client type identifier |

### Meta Object (Pagination)

| Field | Type | Description |
|-------|------|-------------|
| `page` | integer | Current page number |
| `pageSize` | integer | Number of results per page |
| `total` | integer | Total number of records |

---

## Error Codes

| Status Code | Error | Description |
|-------------|-------|-------------|
| 400 | Bad Request | Invalid query parameters or request format |
| 401 | Unauthorized | Missing or invalid API key |
| 404 | Not Found | Resource not found |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error occurred |

---

## Rate Limiting

The API implements rate limiting to prevent abuse. Default limits:

- **60 requests per minute** per API key
- **1000 requests per day** per API key (configurable)

When rate limit is exceeded, you'll receive a `429 Too Many Requests` response:

```json
{
  "message": "Too Many Attempts."
}
```

Response headers include rate limit information:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1698765432
```

---

## Best Practices

### 1. Pagination

Always use pagination for large datasets:

```bash
# Good: Request specific page with reasonable page size
curl "https://your-domain.com/api/v1/clients?page=1&pageSize=100"

# Avoid: Requesting all records at once (max pageSize is 500)
```

### 2. Filtering

Use filters to reduce response size and improve performance:

```bash
# Filter by active status and date range
curl "https://your-domain.com/api/v1/clients?active=true&dateAddedFrom=2023-01-01&dateAddedTo=2023-12-31"
```

### 3. Sorting

Sort results to get data in desired order:

```bash
# Sort by date added (newest first)
curl "https://your-domain.com/api/v1/clients?sort=-dateAdded"

# Sort by client number (ascending)
curl "https://your-domain.com/api/v1/clients?sort=clientNum"
```

### 4. Error Handling

Always handle errors gracefully in your application:

```javascript
fetch('https://your-domain.com/api/v1/clients', {
  headers: {
    'X-API-Key': 'your_api_key_here'
  }
})
.then(response => {
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }
  return response.json();
})
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

### 5. Caching

Consider caching responses on the client side to reduce API calls:

```javascript
// Cache responses for 5 minutes
const CACHE_DURATION = 5 * 60 * 1000;
```

---

## Code Examples

### JavaScript (Fetch API)

```javascript
const API_KEY = 'your_api_key_here';
const BASE_URL = 'https://your-domain.com/api/v1';

async function getClients(page = 1, pageSize = 50) {
  const response = await fetch(
    `${BASE_URL}/clients?page=${page}&pageSize=${pageSize}`,
    {
      headers: {
        'X-API-Key': API_KEY,
        'Content-Type': 'application/json'
      }
    }
  );
  
  if (!response.ok) {
    throw new Error(`API error: ${response.status}`);
  }
  
  return await response.json();
}

async function getClient(clientNum) {
  const response = await fetch(
    `${BASE_URL}/clients/${clientNum}`,
    {
      headers: {
        'X-API-Key': API_KEY,
        'Content-Type': 'application/json'
      }
    }
  );
  
  if (!response.ok) {
    if (response.status === 404) {
      return null;
    }
    throw new Error(`API error: ${response.status}`);
  }
  
  return await response.json();
}

// Usage
getClients(1, 100)
  .then(data => console.log('Clients:', data))
  .catch(error => console.error('Error:', error));
```

### Python (Requests)

```python
import requests

API_KEY = 'your_api_key_here'
BASE_URL = 'https://your-domain.com/api/v1'

def get_clients(page=1, page_size=50, **filters):
    """Get list of clients with optional filters"""
    headers = {
        'X-API-Key': API_KEY,
        'Content-Type': 'application/json'
    }
    
    params = {
        'page': page,
        'pageSize': page_size,
        **filters
    }
    
    response = requests.get(
        f'{BASE_URL}/clients',
        headers=headers,
        params=params
    )
    response.raise_for_status()
    return response.json()

def get_client(client_num):
    """Get a single client by client number"""
    headers = {
        'X-API-Key': API_KEY,
        'Content-Type': 'application/json'
    }
    
    response = requests.get(
        f'{BASE_URL}/clients/{client_num}',
        headers=headers
    )
    
    if response.status_code == 404:
        return None
    
    response.raise_for_status()
    return response.json()

# Usage
try:
    clients = get_clients(page=1, page_size=100, active=True)
    print(f"Found {clients['meta']['total']} clients")
    
    client = get_client(1234567890)
    if client:
        print(f"Client: {client['clientName']}")
except requests.exceptions.RequestException as e:
    print(f"API error: {e}")
```

### PHP (cURL)

```php
<?php

class MSSQLApiClient {
    private $apiKey;
    private $baseUrl;
    
    public function __construct($apiKey, $baseUrl) {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    public function getClients($page = 1, $pageSize = 50, $filters = []) {
        $params = array_merge([
            'page' => $page,
            'pageSize' => $pageSize
        ], $filters);
        
        $url = $this->baseUrl . '/clients?' . http_build_query($params);
        return $this->request($url);
    }
    
    public function getClient($clientNum) {
        $url = $this->baseUrl . '/clients/' . $clientNum;
        return $this->request($url);
    }
    
    private function request($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $this->apiKey,
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("API error: HTTP $httpCode");
        }
        
        return json_decode($response, true);
    }
}

// Usage
$client = new MSSQLApiClient(
    'your_api_key_here',
    'https://your-domain.com/api/v1'
);

try {
    $clients = $client->getClients(1, 100, ['active' => true]);
    echo "Found {$clients['meta']['total']} clients\n";
    
    $client = $client->getClient(1234567890);
    echo "Client: {$client['clientName']}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

## Support

For API support or to report issues:
- Contact your system administrator
- Check the admin dashboard for API status
- Review application logs for detailed error messages
