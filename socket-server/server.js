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
        io.to(cleanChannel).emit(eventName, eventData);
        
    } catch (e) {
        console.error('Error parsing Redis message:', e);
    }
});

// Handle Client Connections
io.on('connection', (socket) => {
    console.log(`New client connected: ${socket.id}`);

    // Client requests to join a channel (e.g., 'private-support-ticket.1')
    socket.on('subscribe', (data) => {
        let channel = typeof data === 'string' ? data : data.channel;
        console.log(`Client ${socket.id} joining channel ${channel}`);
        socket.join(channel);
    });

    // Client requests to leave a channel
    socket.on('unsubscribe', (data) => {
        let channel = typeof data === 'string' ? data : data.channel;
        console.log(`Client ${socket.id} leaving channel ${channel}`);
        socket.leave(channel);
    });

    socket.on('disconnect', () => {
        console.log(`Client disconnected: ${socket.id}`);
    });
});

const PORT = process.env.PORT || 3003;
server.listen(PORT, () => {
    console.log(`🚀 Taif Socket.IO Server running on port ${PORT}`);
});
