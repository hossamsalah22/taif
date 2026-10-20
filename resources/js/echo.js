import Echo from "laravel-echo";
import io from "socket.io-client";

window.io = io;

// Short-lived admin token rendered by the Filament panel; the socket server forwards it to /broadcasting/auth.
const adminSocketToken = document.querySelector('meta[name="admin-socket-token"]')?.content;

window.Echo = new Echo({
    broadcaster: "socket.io",
    host: import.meta.env.VITE_SOCKET_URL || window.location.hostname + ":3003",
    // Identifies this connection as an Echo client so the socket server emits Echo's (event, channel, data) format.
    query: { client: "echo" },
    // WebSocket only: no cross-origin polling requests are needed.
    transports: ["websocket"],
    auth: {
        headers: adminSocketToken ? { "X-Admin-Socket-Token": adminSocketToken } : {},
    },
});
