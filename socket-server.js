/**
 * Socket.io Server for Real-time WebSocket Communication
 *
 * Setup:
 * 1. npm install socket.io socket.io-redis ioredis dotenv
 * 2. Set SOCKET_PORT=3000 in .env
 * 3. Run: node socket-server.js
 *
 * Features:
 * - Real-time message delivery
 * - Channel-based subscriptions
 * - Presence tracking
 * - Typing indicators
 * - Connection pooling with Redis
 * - Graceful shutdown
 */

const io = require('socket.io')(process.env.SOCKET_PORT || 3000, {
    cors: {
        origin: process.env.SOCKET_ALLOWED_ORIGINS || 'http://localhost',
        credentials: true,
    },
    transports: ['websocket', 'polling'],
});

const { createAdapter } = require('@socket.io/redis-adapter');
const redis = require('redis');
const dotenv = require('dotenv');

dotenv.config();

// Redis clients for adapter
const pubClient = redis.createClient({
    host: process.env.REDIS_HOST || 'localhost',
    port: process.env.REDIS_PORT || 6379,
    password: process.env.REDIS_PASSWORD,
    db: process.env.REDIS_DB || 0,
});

const subClient = pubClient.duplicate();

// Setup Socket.io with Redis adapter for clustering
Promise.all([pubClient.connect(), subClient.connect()]).then(() => {
    io.adapter(createAdapter(pubClient, subClient));
    console.log('✅ Socket.io Redis adapter initialized');
}).catch(err => {
    console.error('❌ Redis connection failed:', err);
    process.exit(1);
});

// Connection tracking
const connections = new Map();
const subscriptions = new Map();

/**
 * Track new connection
 */
io.on('connection', (socket) => {
    console.log(`📱 New connection: ${socket.id}`);

    // Store connection metadata
    socket.on('auth', async (data) => {
        try {
            const { token, userId, clubId, connectionId } = data;

            // Verify token with Laravel (you'd implement this)
            socket.userId = userId;
            socket.clubId = clubId;
            socket.connectionId = connectionId;
            socket.token = token;

            connections.set(socket.id, {
                userId,
                clubId,
                connectionId,
                connectedAt: new Date(),
            });

            // Join club room
            socket.join(`club:${clubId}`);
            socket.join(`user:${userId}`);

            console.log(`✅ Authenticated: ${socket.id} - User ${userId}`);
            socket.emit('authenticated', { status: 'ok' });

            // Notify others user is online
            io.to(`club:${clubId}`).emit('user:online', {
                userId,
                status: 'online',
                timestamp: new Date(),
            });
        } catch (error) {
            console.error('Authentication error:', error);
            socket.emit('auth:error', { message: error.message });
        }
    });

    /**
     * Handle channel subscription
     */
    socket.on('subscribe', (data) => {
        const { channel } = data;
        socket.join(channel);

        if (!subscriptions.has(channel)) {
            subscriptions.set(channel, new Set());
        }
        subscriptions.get(channel).add(socket.id);

        console.log(`📢 Subscribed: ${socket.id} → ${channel}`);

        io.to(channel).emit('channel:user-joined', {
            channel,
            userId: socket.userId,
            timestamp: new Date(),
        });
    });

    /**
     * Handle channel unsubscription
     */
    socket.on('unsubscribe', (data) => {
        const { channel } = data;
        socket.leave(channel);

        if (subscriptions.has(channel)) {
            subscriptions.get(channel).delete(socket.id);
            if (subscriptions.get(channel).size === 0) {
                subscriptions.delete(channel);
            }
        }

        console.log(`📢 Unsubscribed: ${socket.id} ← ${channel}`);

        io.to(channel).emit('channel:user-left', {
            channel,
            userId: socket.userId,
            timestamp: new Date(),
        });
    });

    /**
     * Handle direct messages
     */
    socket.on('message:direct', (data) => {
        const { recipientId, message, conversationId } = data;

        io.to(`user:${recipientId}`).emit('message:direct', {
            senderId: socket.userId,
            message,
            conversationId,
            timestamp: new Date(),
        });

        console.log(`💬 Direct message: ${socket.userId} → ${recipientId}`);
    });

    /**
     * Handle group messages
     */
    socket.on('message:group', (data) => {
        const { channel, message, conversationId } = data;

        io.to(channel).emit('message:group', {
            userId: socket.userId,
            message,
            conversationId,
            timestamp: new Date(),
        });

        console.log(`💬 Group message in ${channel}`);
    });

    /**
     * Handle typing indicators
     */
    socket.on('user:typing', (data) => {
        const { channel, conversationId, isTyping } = data;

        io.to(channel || `user:${socket.userId}`).emit('user:typing', {
            userId: socket.userId,
            isTyping,
            conversationId,
            timestamp: new Date(),
        });

        console.log(`⌨️  ${socket.userId} typing in ${channel || 'DM'}`);
    });

    /**
     * Handle presence updates
     */
    socket.on('user:presence', (data) => {
        const { status, device, lastMessage } = data;

        io.to(`club:${socket.clubId}`).emit('user:presence', {
            userId: socket.userId,
            status,
            device,
            lastMessage,
            timestamp: new Date(),
        });

        console.log(`👤 ${socket.userId} status: ${status}`);
    });

    /**
     * Handle notifications
     */
    socket.on('notification:send', (data) => {
        const { recipientIds, title, body, icon, tag } = data;

        recipientIds.forEach(recipientId => {
            io.to(`user:${recipientId}`).emit('notification:received', {
                title,
                body,
                icon,
                tag,
                timestamp: new Date(),
            });
        });

        console.log(`🔔 Notification sent to ${recipientIds.length} users`);
    });

    /**
     * Handle broadcast messages
     */
    socket.on('broadcast:send', (data) => {
        const { channel, message, messageType } = data;

        io.to(channel).emit('broadcast:message', {
            message,
            messageType,
            senderId: socket.userId,
            timestamp: new Date(),
        });

        console.log(`📻 Broadcast in ${channel}`);
    });

    /**
     * Acknowledge message delivery
     */
    socket.on('message:delivered', (data) => {
        const { messageId, senderId } = data;

        io.to(`user:${senderId}`).emit('message:delivered', {
            messageId,
            timestamp: new Date(),
        });

        console.log(`✅ Message ${messageId} delivered`);
    });

    /**
     * Error handling
     */
    socket.on('error', (error) => {
        console.error(`❌ Socket error (${socket.id}):`, error);
    });

    /**
     * Disconnect handler
     */
    socket.on('disconnect', (reason) => {
        if (connections.has(socket.id)) {
            const conn = connections.get(socket.id);
            connections.delete(socket.id);

            // Notify others user is offline
            io.to(`club:${socket.clubId}`).emit('user:offline', {
                userId: socket.userId,
                status: 'offline',
                timestamp: new Date(),
            });

            console.log(`📱 Disconnected: ${socket.id} (${reason})`);
        }

        // Clean up subscriptions
        subscriptions.forEach((subscribers, channel) => {
            if (subscribers.has(socket.id)) {
                subscribers.delete(socket.id);
                if (subscribers.size === 0) {
                    subscriptions.delete(channel);
                }
            }
        });
    });
});

// Graceful shutdown
process.on('SIGTERM', shutdown);
process.on('SIGINT', shutdown);

function shutdown() {
    console.log('\n⏹️  Shutting down Socket.io server...');

    io.close();
    pubClient.quit();
    subClient.quit();

    process.exit(0);
}

// Server info
const PORT = process.env.SOCKET_PORT || 3000;
console.log(`
╔══════════════════════════════════════════════════════════════╗
║  🚀 WebSocket Server Running                                 ║
╠══════════════════════════════════════════════════════════════╣
║  Port: ${PORT}
║  Transports: websocket, polling
║  Redis Adapter: Enabled
║  CORS Origin: ${process.env.SOCKET_ALLOWED_ORIGINS || 'http://localhost'}
║  Environment: ${process.env.NODE_ENV || 'development'}
╚══════════════════════════════════════════════════════════════╝
`);

// Periodic stats logging
setInterval(() => {
    const connCount = io.engine.clientsCount;
    console.log(`📊 Active connections: ${connCount}, Subscriptions: ${subscriptions.size}`);
}, 60000);
