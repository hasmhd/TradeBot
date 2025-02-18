class SocketClient {
    constructor(path, baseUrl, callback) {
      this.baseUrl = baseUrl || 'wss://stream.binance.com/';
      this._path = path;
      this._createSocket();
      this.showMessage = callback;
      this._handlers = new Map();
    }
  
    _createSocket() {
      console.log(`${this.baseUrl}${this._path}`);
      this._ws = new WebSocket(`${this.baseUrl}${this._path}`);
  
      this._ws.onopen = () => {
        this.showMessage('ws connected');
      };
  
      this._ws.on('pong', () => {
        this.showMessage('receieved pong from server');
      });
      this._ws.on('ping', () => {
        this.showMessage('==========receieved ping from server');
        this._ws.pong();
      });
  
      this._ws.onclose = () => {
        this.showMessage('ws closed');
      };
  
      this._ws.onerror = (err) => {
        this.showMessage('ws error', err);
      };
  
      this._ws.onmessage = (msg) => {
        try {
          const message = JSON.parse(msg.data);
          if (this.isMultiStream(message)) {
            this._handlers.get(message.stream).forEach(cb => cb(message));
          } else if (message.e && this._handlers.has(message.e)) {
            this._handlers.get(message.e).forEach(cb => {
              cb(message);
            });
          } else {
            this.showMessage('Unknown method', message);
          }
        } catch (e) {
            this.showMessage('Parse message failed', e);
        }
      };
  
      this.heartBeat();
    }
  
    isMultiStream(message) {
      return message.stream && this._handlers.has(message.stream);
    }
  
    heartBeat() {
      setInterval(() => {
        if (this._ws.readyState === WebSocket.OPEN) {
          this._ws.ping();
          this.showMessage("ping server");
        }
      }, 5000);
    }
  
    setHandler(method, callback) {
      if (!this._handlers.has(method)) {
        this._handlers.set(method, []);
      }
      this._handlers.get(method).push(callback);
    }
  }