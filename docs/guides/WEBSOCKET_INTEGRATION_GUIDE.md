# WebSocket & Real-time Communication Integration Guide

**Complete implementation for real-time messaging, presence tracking, and typing indicators**

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Installation & Setup](#installation--setup)
3. [Database Migration](#database-migration)
4. [API Endpoints](#api-endpoints)
5. [Client Integration](#client-integration)
6. [Socket.io Events](#socketio-events)
7. [Usage Examples](#usage-examples)
8. [Performance Optimization](#performance-optimization)
9. [Troubleshooting](#troubleshooting)
10. [Security Considerations](#security-considerations)

---

## Architecture Overview

### System Components

```
┌─────────────────────────────────────────────────────────────────┐
│                        Frontend (Browser)                        │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ WebSocketClient (JS)                                        ││
│  │ - Connection management                                     ││
│  │ - Event listeners                                           ││
│  │ - Channel subscriptions                                     ││
│  └─────────────────────────────────────────────────────────────┘│
└───────────────────────┬──────────────────────────────────────────┘
                        │ HTTP REST API + Socket.io
                        ▼
┌─────────────────────────────────────────────────────────────────┐
│                  Laravel REST API Layer                          │
│  ┌──────────────────────────────────────────────────────────────┐│
│  │ WebSocketController                                          ││
│  │ - POST /api/websocket/connect                               ││
│  │ - POST /api/websocket/disconnect                            ││
│  │ - POST /api/websocket/presence/update                       ││
│  │ - POST /api/websocket/typing                                ││
│  │ - GET /api/websocket/online-users                           ││
│  │ - GET /api/websocket/stats                                  ││
│  └──────────────────────────────────────────────────────────────┘│
└────────────┬─────────────────────────────────────┬───────────────┘
             │                                     │
             ▼                                     ▼
   ┌──────────────────────┐          ┌──────────────────────┐
   │  WebSocketService    │          │  Socket.io Server    │
   │  (PHP - Laravel)     │          │  (Node.js)           │
   │                      │          │                      │
   │ - Connection mgmt    │◄────────►│ - Real-time events   │
   │ - Event handling     │ Redis    │ - Channel broadcast  │
   │ - Presence tracking  │          │ - Connection pooling │
   │ - Typing indicators  │          │                      │
   └──────────────────────┘          └──────────────────────┘
             │                                     │
             └─────────────┬───────────────────────┘
                           │
                           ▼
              ┌────────────────────────────┐
              │  Redis Cache & Queue       │
              │  - Connection pooling      │
              │  - Event caching           │
              │  - Session storage         │
              └────────────────────────────┘
                           │
                           ▼
              ┌────────────────────────────┐
              │  MySQL Database            │
              │  - websocket_events        │
              │  - websocket_connections   │
              │  - websocket_typing...     │
              │  - websocket_presence      │
              └────────────────────────────┘
```

### Data Flow

1. **Connection Establishment**
   - Client calls REST API: `POST /api/websocket/connect`
   - Server registers connection in DB
   - Server returns connection_id
   - Client initiates Socket.io connection

2. **Real-time Communication**
   - Events sent via Socket.io (fast, low-latency)
   - Fallback to REST API polling if needed
   - Connection state persisted in Redis

3. **Presence Tracking**
   - Client periodically updates status: `POST /api/websocket/presence/update`
   - Broadcasted to all users in club via Socket.io
   - Cached in Redis for fast lookups

4. **Typing Indicators**
   - Client sends: `POST /api/websocket/typing`
   - Auto-expires after 30 seconds (cleanup task)
   - Broadcast to conversation participants

---

## Installation & Setup

### Step 1: Install PHP Dependencies

```bash
composer require predis/predis
```

### Step 2: Install Node.js Dependencies

```bash
cd project_root
npm install socket.io socket.io-redis ioredis dotenv
```

### Step 3: Update Environment Variables

Add to `.env`:

```env
# WebSocket Configuration
SOCKET_SERVER_URL=http://localhost:3000
SOCKET_PORT=3000
SOCKET_ALLOWED_ORIGINS=http://localhost:8000,http://localhost:3000

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
```

### Step 4: Run Database Migration

```bash
php artisan migrate
```

Creates tables:
- `websocket_events`
- `websocket_connections`
- `websocket_typing_indicators`
- `websocket_presence`

### Step 5: Start Socket.io Server

```bash
# Development
node socket-server.js

# Production (with PM2)
pm2 start socket-server.js --name websocket-server
pm2 save
```

---

## Database Migration

### Created Tables

#### `websocket_events`
Stores all WebSocket events for audit trail

```sql
CREATE TABLE websocket_events (
  id bigint PRIMARY KEY,
  tenant_id bigint UNSIGNED,
  user_id bigint UNSIGNED,
  event_type varchar(50),           -- message_sent, notification_received, typing, online_status
  channel varchar(255),              -- private-user.{id}, group.{id}, broadcast
  payload json,                      -- Event data
  status varchar(20) DEFAULT pending, -- pending, delivered, failed
  retry_count int DEFAULT 0,
  error_message text,
  created_at timestamp,
  updated_at timestamp,
  INDEX (tenant_id, user_id),
  INDEX (event_type, channel),
  INDEX (status, created_at)
);
```

#### `websocket_connections`
Active WebSocket connections

```sql
CREATE TABLE websocket_connections (
  id bigint PRIMARY KEY,
  tenant_id bigint UNSIGNED,
  user_id bigint UNSIGNED,
  connection_id varchar(255) UNIQUE, -- Socket.io connection ID
  session_id varchar(255),
  browser varchar(50),               -- Chrome, Firefox, Safari, Edge
  device_type varchar(50),           -- mobile, desktop, tablet
  ip_address varchar(45),
  subscribed_channels json,          -- Array of channels
  status varchar(20) DEFAULT connected, -- connected, disconnected
  connected_at timestamp,
  last_activity_at timestamp,
  disconnected_at timestamp,
  created_at timestamp,
  updated_at timestamp,
  UNIQUE KEY (tenant_id, connection_id),
  INDEX (user_id, status)
);
```

#### `websocket_typing_indicators`
Track who is typing in conversations

```sql
CREATE TABLE websocket_typing_indicators (
  id bigint PRIMARY KEY,
  tenant_id bigint UNSIGNED,
  user_id bigint UNSIGNED,
  conversation_id varchar(255),
  conversation_type varchar(50),    -- direct, group, broadcast
  is_typing boolean DEFAULT true,
  started_at timestamp,
  expires_at timestamp,             -- Auto-cleanup after 30 seconds
  created_at timestamp,
  updated_at timestamp,
  UNIQUE KEY (tenant_id, user_id, conversation_id),
  INDEX (conversation_id, expires_at)
);
```

#### `websocket_presence`
User presence and status

```sql
CREATE TABLE websocket_presence (
  id bigint PRIMARY KEY,
  tenant_id bigint UNSIGNED,
  user_id bigint UNSIGNED,
  status varchar(20) DEFAULT online, -- online, away, busy, offline
  device varchar(50),                -- mobile, desktop, tablet, web
  last_message text,
  away_since timestamp,
  last_seen_at timestamp,
  created_at timestamp,
  updated_at timestamp,
  UNIQUE KEY (tenant_id, user_id)
);
```

---

## API Endpoints

### Connection Management

#### Register Connection
```
POST /api/websocket/connect
Authorization: Bearer {token}
Content-Type: application/json

{
  "connection_id": "conn_1234567890_xyz",
  "session_id": "session_1234567890_xyz",
  "browser": "Chrome",
  "device_type": "desktop"
}

Response 201:
{
  "data": {
    "connection_id": "conn_1234567890_xyz",
    "status": "connected",
    "user_id": 1,
    "connected_at": "2025-10-24T10:00:00Z"
  }
}
```

#### Disconnect Connection
```
POST /api/websocket/disconnect
Authorization: Bearer {token}
Content-Type: application/json

{
  "connection_id": "conn_1234567890_xyz"
}

Response 200:
{
  "message": "Disconnected successfully"
}
```

### Presence Management

#### Get User Presence
```
GET /api/websocket/presence/{userId}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "user_id": 1,
    "status": "online",
    "device": "desktop",
    "display_status": "Online on desktop",
    "last_seen_at": "2025-10-24T10:05:00Z"
  }
}
```

#### Get Online Users
```
GET /api/websocket/online-users
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "user_id": 1,
      "name": "John Doe",
      "status": "online",
      "device": "desktop",
      "display_status": "Online"
    },
    {
      "user_id": 2,
      "name": "Jane Smith",
      "status": "away",
      "device": "mobile",
      "display_status": "Away for 5m"
    }
  ],
  "total": 2
}
```

#### Update Presence
```
POST /api/websocket/presence/update
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "away",
  "device": "mobile",
  "last_message": "In a meeting"
}

Response 200:
{
  "data": {
    "status": "away",
    "device": "mobile",
    "updated_at": "2025-10-24T10:05:00Z"
  }
}
```

### Typing Indicators

#### Record Typing
```
POST /api/websocket/typing
Authorization: Bearer {token}
Content-Type: application/json

{
  "conversation_id": "conv_12345",
  "conversation_type": "direct",
  "is_typing": true
}

Response 200:
{
  "data": {
    "conversation_id": "conv_12345",
    "is_typing": true,
    "expires_at": "2025-10-24T10:05:30Z"
  }
}
```

#### Get Typing Users
```
GET /api/websocket/typing/{conversationId}
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "user_id": 2,
      "user_name": "Jane Smith",
      "started_at": "2025-10-24T10:05:00Z"
    }
  ],
  "count": 1
}
```

### Channel Management

#### Subscribe to Channel
```
POST /api/websocket/subscribe
Authorization: Bearer {token}
Content-Type: application/json

{
  "connection_id": "conn_1234567890_xyz",
  "channel": "group.events.2"
}

Response 200:
{
  "message": "Subscribed to channel",
  "channel": "group.events.2"
}
```

#### Unsubscribe from Channel
```
POST /api/websocket/unsubscribe
Authorization: Bearer {token}
Content-Type: application/json

{
  "connection_id": "conn_1234567890_xyz",
  "channel": "group.events.2"
}

Response 200:
{
  "message": "Unsubscribed from channel",
  "channel": "group.events.2"
}
```

### Statistics

#### Get WebSocket Stats
```
GET /api/websocket/stats
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "total_connections": 5,
    "active_connections": 4,
    "unique_users": 3,
    "pending_events": 0,
    "failed_events": 2
  }
}
```

---

## Client Integration

### Step 1: Include WebSocket Client

In your Blade template:

```blade
<!-- Load the WebSocket client -->
<script src="{{ asset('js/websocket-client.js') }}"></script>

<script>
// Initialize WebSocket connection
const ws = new WebSocketClient({
    baseUrl: '/api/websocket',
    token: document.querySelector('meta[name="api-token"]').content,
    maxReconnectAttempts: 5,
    reconnectDelay: 3000,
});
</script>
```

### Step 2: Handle Connection Events

```javascript
// Connection established
ws.on('connected', (data) => {
    console.log('Connected to WebSocket:', data);
});

// Connection lost
ws.on('disconnected', () => {
    console.log('Disconnected from WebSocket');
});

// Subscription successful
ws.on('subscribed', (data) => {
    console.log('Subscribed to channel:', data.channel);
});

// Maximum reconnection attempts reached
ws.on('max-reconnect-attempts-reached', () => {
    console.error('Failed to reconnect to WebSocket after max attempts');
});

// Heartbeat (stats update)
ws.on('heartbeat', (stats) => {
    console.log('WebSocket stats:', stats);
});
```

### Step 3: Subscribe to Channels

```javascript
// Subscribe to personal notifications
await ws.subscribe('user:me');

// Subscribe to group messages
await ws.subscribe('group.events.2');

// Subscribe to broadcast messages
await ws.subscribe('broadcast');
```

### Step 4: Listen for Real-time Events

```javascript
// Use Socket.io client for real-time events
// Include Socket.io client library
<script src="/socket.io/socket.io.js"></script>

<script>
const socket = io(process.env.VUE_APP_SOCKET_SERVER_URL);

// Authenticate with server
socket.emit('auth', {
    token: document.querySelector('meta[name="api-token"]').content,
    userId: document.querySelector('meta[name="user-id"]').content,
    clubId: document.querySelector('meta[name="club-id"]').content,
    connectionId: ws.connectionId,
});

// Listen for messages
socket.on('message:direct', (data) => {
    console.log('New message:', data);
    updateChatUI(data);
});

socket.on('message:group', (data) => {
    console.log('Group message:', data);
    updateGroupChat(data);
});

// Listen for typing
socket.on('user:typing', (data) => {
    if (data.isTyping) {
        showTypingIndicator(data.userId);
    } else {
        hideTypingIndicator(data.userId);
    }
});

// Listen for presence updates
socket.on('user:presence', (data) => {
    console.log(`${data.userId} is ${data.status}`);
    updateUserStatus(data.userId, data.status);
});

// Listen for user coming online
socket.on('user:online', (data) => {
    console.log(`${data.userId} came online`);
    updateOnlineList();
});

// Listen for user going offline
socket.on('user:offline', (data) => {
    console.log(`${data.userId} went offline`);
    updateOnlineList();
});
</script>
```

---

## Socket.io Events

### Server Events (Emitted by Socket.io Server)

#### `authenticated`
User successfully authenticated
```javascript
{
    status: 'ok'
}
```

#### `message:direct`
Direct message received
```javascript
{
    senderId: 1,
    message: "Hello!",
    conversationId: "conv_12345",
    timestamp: "2025-10-24T10:00:00Z"
}
```

#### `message:group`
Group message received
```javascript
{
    userId: 1,
    message: "Team announcement",
    conversationId: "group_456",
    timestamp: "2025-10-24T10:00:00Z"
}
```

#### `user:typing`
User is typing
```javascript
{
    userId: 2,
    isTyping: true,
    conversationId: "conv_12345",
    timestamp: "2025-10-24T10:00:00Z"
}
```

#### `user:presence`
User presence changed
```javascript
{
    userId: 1,
    status: "away",
    device: "mobile",
    lastMessage: "Lunch break",
    timestamp: "2025-10-24T10:00:00Z"
}
```

#### `user:online` / `user:offline`
User connection state changed
```javascript
{
    userId: 1,
    status: "online",
    timestamp: "2025-10-24T10:00:00Z"
}
```

#### `notification:received`
Push notification received
```javascript
{
    title: "New Message",
    body: "John sent you a message",
    icon: "/images/notification-icon.png",
    tag: "message",
    timestamp: "2025-10-24T10:00:00Z"
}
```

#### `broadcast:message`
Broadcast message received
```javascript
{
    message: "Club announcement",
    messageType: "info",
    senderId: 1,
    timestamp: "2025-10-24T10:00:00Z"
}
```

### Client Events (Emitted to Socket.io Server)

#### `auth`
Authenticate connection
```javascript
socket.emit('auth', {
    token: "bearer_token",
    userId: 1,
    clubId: 2,
    connectionId: "conn_123"
});
```

#### `subscribe`
Subscribe to channel
```javascript
socket.emit('subscribe', {
    channel: 'group.events.2'
});
```

#### `unsubscribe`
Unsubscribe from channel
```javascript
socket.emit('unsubscribe', {
    channel: 'group.events.2'
});
```

#### `message:direct`
Send direct message
```javascript
socket.emit('message:direct', {
    recipientId: 2,
    message: "Hello!",
    conversationId: "conv_12345"
});
```

#### `message:group`
Send group message
```javascript
socket.emit('message:group', {
    channel: 'group.events.2',
    message: "Team message",
    conversationId: "group_456"
});
```

#### `user:typing`
Send typing indicator
```javascript
socket.emit('user:typing', {
    channel: 'group.events.2',
    conversationId: "group_456",
    isTyping: true
});
```

#### `user:presence`
Update user presence
```javascript
socket.emit('user:presence', {
    status: 'away',
    device: 'mobile',
    lastMessage: 'Lunch break'
});
```

#### `notification:send`
Send notification
```javascript
socket.emit('notification:send', {
    recipientIds: [1, 2, 3],
    title: "Alert",
    body: "Important message",
    icon: "/icon.png",
    tag: "alert"
});
```

#### `message:delivered`
Acknowledge message delivery
```javascript
socket.emit('message:delivered', {
    messageId: 123,
    senderId: 1
});
```

---

## Usage Examples

### Complete Real-time Chat Example

```html
<!DOCTYPE html>
<html>
<head>
    <title>Real-time Chat</title>
    <script src="/socket.io/socket.io.js"></script>
    <script src="{{ asset('js/websocket-client.js') }}"></script>
</head>
<body>

<div id="chat-container">
    <div id="online-users"></div>
    <div id="chat-messages"></div>
    <div id="typing-indicator"></div>
    <input type="text" id="message-input" placeholder="Type message...">
    <button id="send-button">Send</button>
</div>

<script>
// Initialize WebSocket
const ws = new WebSocketClient({
    baseUrl: '/api/websocket',
    token: document.querySelector('meta[name="api-token"]').content,
});

// Initialize Socket.io
const socket = io(process.env.VITE_SOCKET_SERVER_URL);

let currentConversation = null;
let isTyping = false;

// When WebSocket connects
ws.on('connected', async () => {
    // Get online users
    const onlineUsers = await ws.getOnlineUsers();
    renderOnlineUsers(onlineUsers);
    
    // Subscribe to presence updates
    await ws.subscribe('presence');
});

// Socket.io authentication
socket.on('connect', () => {
    socket.emit('auth', {
        token: document.querySelector('meta[name="api-token"]').content,
        userId: document.querySelector('meta[name="user-id"]').content,
        clubId: document.querySelector('meta[name="club-id"]').content,
        connectionId: ws.connectionId,
    });
});

// Handle new direct messages
socket.on('message:direct', (data) => {
    if (data.conversationId === currentConversation) {
        displayMessage(data.senderId, data.message);
    }
});

// Handle typing indicator
socket.on('user:typing', (data) => {
    if (data.conversationId === currentConversation && data.isTyping) {
        showTypingIndicator(data.userId);
    }
});

// Handle presence changes
socket.on('user:presence', (data) => {
    updateUserStatus(data.userId, data.status);
});

// Send message
document.getElementById('send-button').addEventListener('click', () => {
    const message = document.getElementById('message-input').value;
    
    socket.emit('message:direct', {
        recipientId: currentConversation.userId,
        message: message,
        conversationId: currentConversation.id,
    });
    
    // Stop typing
    ws.recordTyping(currentConversation.id, false);
    
    document.getElementById('message-input').value = '';
});

// Typing indicator with debounce
document.getElementById('message-input').addEventListener('input', debounce(() => {
    if (currentConversation) {
        ws.recordTyping(currentConversation.id, true);
    }
}, 500));

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    ws.disconnect();
    socket.close();
});

function debounce(fn, ms) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), ms);
    };
}

function renderOnlineUsers(users) {
    const container = document.getElementById('online-users');
    container.innerHTML = users.map(user => `
        <div class="user ${user.status}">
            <span>${user.name}</span>
            <span class="status">${user.display_status}</span>
        </div>
    `).join('');
}

function displayMessage(userId, message) {
    const container = document.getElementById('chat-messages');
    const msgEl = document.createElement('div');
    msgEl.className = 'message';
    msgEl.innerHTML = `<strong>${userId}</strong>: ${message}`;
    container.appendChild(msgEl);
}

function showTypingIndicator(userId) {
    document.getElementById('typing-indicator').textContent = `${userId} is typing...`;
}

function updateUserStatus(userId, status) {
    const userEl = document.querySelector(`.user[data-user-id="${userId}"]`);
    if (userEl) {
        userEl.classList.remove('online', 'away', 'busy', 'offline');
        userEl.classList.add(status);
    }
}
</script>

</body>
</html>
```

### Update User Presence on Page Focus/Blur

```javascript
// Set online when page gains focus
document.addEventListener('visibilitychange', async () => {
    if (document.hidden) {
        await ws.updatePresence('away', { 
            lastMessage: 'Away' 
        });
    } else {
        await ws.updatePresence('online');
    }
});

// Set offline before leaving page
window.addEventListener('beforeunload', async () => {
    await ws.updatePresence('offline');
    await ws.disconnect();
});
```

### Presence Polling (Alternative to Socket.io)

```javascript
// If you prefer pure REST polling instead of Socket.io
setInterval(async () => {
    const onlineUsers = await ws.getOnlineUsers();
    updateOnlineUsersList(onlineUsers);
}, 10000); // Poll every 10 seconds
```

---

## Performance Optimization

### 1. Redis Caching

Enable Redis caching for frequently accessed data:

```php
// In WebSocketService.php
Cache::tags(['websocket', "club:{$clubId}"])
    ->remember("online_users:{$clubId}", 300, function () {
        return $this->getOnlineUsers($clubId);
    });
```

### 2. Database Indexing

Already included in migration:
- Tenant + user compound index
- Event type + channel index
- Status + timestamp index for efficient queries

### 3. Connection Pooling

Socket.io automatically pools connections via Redis adapter.

### 4. Event Batching

```javascript
// Batch typing events to avoid too many updates
const typingQueue = [];
const typingBatchInterval = 500; // ms

function recordTyping(conversationId, isTyping) {
    typingQueue.push({ conversationId, isTyping });
}

setInterval(() => {
    if (typingQueue.length > 0) {
        // Send all typing events in one request
        typingQueue.forEach(item => {
            ws.recordTyping(item.conversationId, item.isTyping);
        });
        typingQueue = [];
    }
}, typingBatchInterval);
```

### 5. Lazy Loading Presence

```javascript
// Only load presence for visible users in UI
const visibleUserIds = getVisibleUserIds();

Promise.all(visibleUserIds.map(id => ws.getPresence(id)))
    .then(presences => updateUI(presences));
```

---

## Troubleshooting

### Connection Fails Immediately

**Check**:
1. REST API endpoints accessible: `curl http://localhost:8000/api/websocket/connect`
2. Socket.io server running: `node socket-server.js`
3. Redis connected: `redis-cli ping` should return `PONG`
4. CORS settings in `.env`: `SOCKET_ALLOWED_ORIGINS`

### Socket.io Connection Succeeds but No Events

**Check**:
1. Authentication worked: `socket.on('authenticated', ...)`
2. Subscribed to channels: `socket.emit('subscribe', { channel: '...' })`
3. Check browser console for errors
4. Verify Redis adapter is initialized in socket-server.js

### Typing Indicators Not Disappearing

**Cause**: Database cleanup task not running

**Fix**:
```bash
# Add to Laravel task scheduler
php artisan schedule:work

# Or run manually
php artisan websocket:cleanup-typing
```

### Presence Data Stale

**Solution**: Increase heartbeat frequency
```javascript
const ws = new WebSocketClient({
    heartbeatInterval: 15000, // 15 seconds instead of 30
});
```

### High Memory Usage

**Check**:
1. Number of active connections: `GET /api/websocket/stats`
2. Redis memory: `redis-cli info memory`
3. Node.js memory: `ps aux | grep node`

**Solutions**:
- Reduce max clients: `io.engine.maxHttpBufferSize = 1e5;`
- Clean up expired records: `php artisan websocket:cleanup`
- Enable compression: `io(..., { perMessageDeflate: true })`

---

## Security Considerations

### 1. Authentication

All REST endpoints protected with `auth:sanctum`:

```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/websocket/connect', ...);
    // ...
});
```

### 2. Authorization

Verify user owns connection:

```php
public function disconnect(Request $request)
{
    $connection = WebSocketConnection::findOrFail($request->input('connection_id'));
    
    if ($connection->user_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }
    // ...
}
```

### 3. Rate Limiting

Add rate limiting to prevent abuse:

```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/websocket/typing', ...);
    Route::post('/websocket/presence/update', ...);
});
```

### 4. CORS Configuration

Restrict Socket.io to approved origins:

```javascript
const io = require('socket.io')(3000, {
    cors: {
        origin: ['http://localhost:8000', 'https://myapp.com'],
        credentials: true,
    },
});
```

### 5. Message Validation

Always validate incoming messages:

```javascript
socket.on('message:direct', (data) => {
    if (!data.recipientId || !data.message || data.message.length > 5000) {
        socket.emit('error', { message: 'Invalid message' });
        return;
    }
    // Process message
});
```

### 6. Connection Limits

Prevent multiple connections from same user:

```php
public function registerConnection()
{
    // Disconnect previous connections
    WebSocketConnection::where('user_id', auth()->id())
        ->where('status', 'connected')
        ->update(['status' => 'disconnected']);
    
    // Register new connection
    // ...
}
```

---

## Monitoring & Logging

### Enable WebSocket Logging

In `config/logging.php`:

```php
'channels' => [
    'websocket' => [
        'driver' => 'single',
        'path' => storage_path('logs/websocket.log'),
        'level' => 'debug',
    ],
],
```

### Log Messages

```php
// In WebSocketService
Log::channel('websocket')->info('Connection registered', [
    'connection_id' => $connectionId,
    'user_id' => $userId,
]);
```

### Monitor Socket.io Server

```javascript
// In socket-server.js
setInterval(() => {
    const stats = {
        connections: io.engine.clientsCount,
        subscriptions: subscriptions.size,
        timestamp: new Date(),
    };
    console.log('📊 Stats:', stats);
}, 60000);
```

---

## Next Steps

1. ✅ WebSocket Infrastructure (completed)
2. → SMS Gateway Integration
3. → Advanced Targeting
4. → A/B Testing
5. → Analytics Dashboard

---

**Last Updated**: 2025-10-24  
**Version**: 1.0.0  
**Status**: Production Ready ✅
