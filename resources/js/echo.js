import Echo from "laravel-echo";
import io from "socket.io-client";

window.io = io;

window.Echo = new Echo({
    broadcaster: "socket.io",
    host: import.meta.env.VITE_SOCKET_URL || window.location.hostname + ":3003",
    // Identifies this connection as an Echo client so the socket server emits Echo's (event, channel, data) format.
    query: { client: "echo" },
    // WebSocket only: the session cookie is sent with the handshake and no cross-origin polling is needed.
    transports: ["websocket"],
});
