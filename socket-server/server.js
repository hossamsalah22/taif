import { Server } from 'socket.io';
import Redis from 'ioredis';
import dotenv from 'dotenv';
import http from 'http';

dotenv.config();

// Create HTTP Server
const server = http.createServer((req, res) => {
    res.writeHead(200);
    res.end('Taif Socket.IO Server is running!');
});

// Setup Socket.IO Server
const io = new Server(server, {
    cors: {
        origin: '*',
        methods: ["GET", "POST"]
    }
});

// Setup Redis Client
const redis = new Redis({
    host: process.env.REDIS_HOST || '127.0.0.1',
    port: process.env.REDIS_PORT || 6379,
    password: process.env.REDIS_PASSWORD || null
});

// Subscribe to all Laravel channels (they usually end with an event name or have a prefix)
redis.psubscribe('*', (err, count) => {
    if (err) {
        console.error('Redis subscription error:', err);
    } else {
        console.log(`Subscribed to ${count} Redis channel pattern(s)`);
    }
});

// Listen for messages from Laravel Redis Publisher
redis.on('pmessage', (pattern, channel, message) => {
    console.log(`[Redis Event] Channel: ${channel} | Data:`, message);
    
    try {
        const payload = JSON.parse(message);
        
        // Laravel's Redis Broadcaster usually formats the channel as [prefix]_[database]_[channel]
        // But for simplicity, we can emit directly to the channel name we received.
        // Or if the client joins 'support-ticket.1', we can match it.
        const eventName = payload.event;
        const eventData = payload.data;
        
        // Broadcast the event to all clients in the corresponding Socket.IO room (channel)
        // Since Laravel prefixes channels in Redis, we extract the actual channel name
        let cleanChannel = channel;
        if(channel.includes('private-')) {
            cleanChannel = channel.substring(channel.indexOf('private-'));
        }

        console.log(`Emitting event '${eventName}' to room '${cleanChannel}'`);
        // Mobile clients receive (event, data); Laravel Echo clients expect (event, channel, data)
        io.to(cleanChannel).emit(eventName, eventData);
        io.to(echoRoom(cleanChannel)).emit(eventName, cleanChannel, eventData);

    } catch (e) {
        console.error('Error parsing Redis message:', e);
    }
});

// Laravel endpoint that authorizes private/presence channels (routes/channels.php)
const AUTH_ENDPOINT = process.env.AUTH_ENDPOINT || 'https://app.taifapp.com/broadcasting/auth';

// Browser origins (e.g. the admin panel) allowed to authenticate with their session cookie
const ALLOWED_ORIGINS = (process.env.ALLOWED_ORIGINS || '')
    .split(',')
    .map((origin) => origin.trim())
    .filter(Boolean);

const isEchoClient = (socket) => socket.handshake.query?.client === 'echo';
const echoRoom = (channel) => `echo:${channel}`;
const roomFor = (socket, channel) => (isEchoClient(socket) ? echoRoom(channel) : channel);

if (!AUTH_ENDPOINT) {
    console.warn('⚠️  AUTH_ENDPOINT is not set — private channel subscriptions will be rejected.');
}

// Ask Laravel whether this socket may join the given private channel.
// Mobile clients send their Sanctum token either in the handshake (io(url, { auth: { token } }))
// or in the subscribe payload (auth.headers.Authorization). Browser clients send their session cookie.
async function canJoinChannel(socket, channel, auth = {}) {
    if (!channel.startsWith('private-') && !channel.startsWith('presence-')) {
        return true;
    }

    if (!AUTH_ENDPOINT) {
        return false;
    }

    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };

    const handshakeToken = socket.handshake.auth?.token;
    const authorization = auth.headers?.Authorization
        || auth.headers?.authorization
        || (handshakeToken ? `Bearer ${handshakeToken}` : null);

    if (authorization) {
        headers['Authorization'] = authorization;
    }

    // Only trust cookies from known origins, otherwise any website could subscribe with an admin's session
    if (socket.handshake.headers.cookie && ALLOWED_ORIGINS.includes(socket.handshake.headers.origin)) {
        headers['Cookie'] = socket.handshake.headers.cookie;
    }

    try {
        const response = await fetch(AUTH_ENDPOINT, {
            method: 'POST',
            headers,
            body: JSON.stringify({ channel_name: channel, socket_id: socket.id }),
        });

        return response.ok;
    } catch (e) {
        console.error(`Auth request failed for ${channel}:`, e.message);

        return false;
    }
}

// Handle Client Connections
io.on('connection', (socket) => {
    console.log(`New client connected: ${socket.id}`);

    // Client requests to join a channel (e.g., 'private-support-ticket.1')
    socket.on('subscribe', async (data, ack) => {
        const channel = typeof data === 'string' ? data : data?.channel;

        if (!channel) {
            return;
        }

        const allowed = await canJoinChannel(socket, channel, data?.auth);
        const respond = typeof ack === 'function' ? ack : () => {};

        if (!allowed) {
            console.log(`Client ${socket.id} denied channel ${channel}`);
            socket.emit('subscription_error', { channel, status: 403 });
            respond({ channel, subscribed: false });

            return;
        }

        console.log(`Client ${socket.id} joining channel ${channel}`);
        socket.join(roomFor(socket, channel));
        socket.emit('subscription_succeeded', { channel });
        respond({ channel, subscribed: true });
    });

    // Client requests to leave a channel
    socket.on('unsubscribe', (data) => {
        const channel = typeof data === 'string' ? data : data?.channel;

        if (!channel) {
            return;
        }

        console.log(`Client ${socket.id} leaving channel ${channel}`);
        socket.leave(roomFor(socket, channel));
    });

    socket.on('disconnect', () => {
        console.log(`Client disconnected: ${socket.id}`);
    });
});

const PORT = process.env.PORT || 3003;
server.listen(PORT, () => {
    console.log(`🚀 Taif Socket.IO Server running on port ${PORT}`);
});
