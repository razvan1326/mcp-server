# Remote MCP Server pentru Academiadepolitie.com

## 🚀 Despre

Aceasta este implementarea **Remote MCP** pentru Academiadepolitie.com, complet separată de implementarea locală MCP existentă.

### Diferențe față de Local MCP:

| Feature | Local MCP | Remote MCP |
|---------|-----------|------------|
| **Instalare user** | npm install + config | Zero instalare |
| **Autentificare** | JWT manual | OAuth 2.1 automatic |
| **Transport** | STDIO | HTTP/SSE |
| **Updates** | Manual | Automatic |
| **Platform support** | Desktop only | Toate platformele |

## 📁 Structura

```
claude-remote-mcp/
├── src/
│   ├── server.js        # Main server (Express + MCP)
│   ├── sse-handler.js   # Server-Sent Events handler
│   └── tools.js         # Tool definitions
├── auth/
│   └── oauth.js         # OAuth 2.1 implementation
├── deploy/
│   ├── setup.sh         # Setup script pentru AlmaLinux
│   └── nginx.conf       # Nginx configuration
└── package.json         # Dependencies
```

## 🔧 Instalare Server

### Prerequisites
- AlmaLinux 8.10+
- Node.js 18+
- Nginx
- SSL certificate

### Quick Setup

```bash
cd deploy
sudo bash setup.sh
```

### Manual Setup

1. **Install dependencies:**
```bash
npm install
```

2. **Configure environment:**
```bash
cp .env.example .env
# Edit .env cu configurațiile tale
```

3. **Start server:**
```bash
npm start
```

## 🔐 OAuth 2.1 Flow

Remote MCP folosește OAuth 2.1 cu PKCE pentru autentificare:

1. User adaugă `https://mcp.academiadepolitie.com` în Claude
2. Claude inițiază OAuth flow
3. User se autentifică pe academiadepolitie.com
4. Approval și redirect back to Claude
5. Token exchange și connection ready

## 🛠️ Deployment

### Nginx Reverse Proxy

```bash
# Copy nginx config
sudo cp deploy/nginx.conf /etc/nginx/sites-available/mcp.academiadepolitie.com
sudo ln -s /etc/nginx/sites-available/mcp.academiadepolitie.com /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### SSL Certificate (Let's Encrypt)

```bash
sudo certbot --nginx -d mcp.academiadepolitie.com
```

### Systemd Service

Service-ul este creat automat de `setup.sh`. Pentru management manual:

```bash
# Status
sudo systemctl status remote-mcp

# Restart
sudo systemctl restart remote-mcp

# Logs
sudo journalctl -u remote-mcp -f
```

## 📊 Monitoring

### Health Check
```bash
curl https://mcp.academiadepolitie.com/health
```

### Logs
```bash
# Application logs
sudo journalctl -u remote-mcp -f

# Nginx logs
tail -f /var/log/nginx/mcp.academiadepolitie.com.access.log
```

## 🔌 Tool-uri disponibile

1. **get_student_data** - Date complete student
2. **search_articles** - Căutare articole/lecții
3. **get_article_content** - Conținut cu paginare
4. **add_note** - Adăugare notițe
5. **send_challenge** - Provocări între utilizatori
6. **update_reading_progress** - Actualizare progres
7. **save_generated_quiz** - Salvează quiz-uri generate

## 🚨 Important

⚠️ **Aceasta este o implementare SEPARATĂ!**
- Nu afectează Local MCP existent
- Nu modifică `/api/llm/claude-mcp/`
- Backend-ul `/api/internal/` rămâne intact
- Utilizatorii pot folosi ambele versiuni în paralel

## 📈 Migrare utilizatori

Pentru migrarea graduală de la Local la Remote MCP:

1. **Faza 1**: Ambele sisteme active
2. **Faza 2**: Promovare Remote MCP ca principal
3. **Faza 3**: Depreciere Local MCP (opțional)

## 🐛 Troubleshooting

### Server nu pornește
```bash
# Check logs
sudo journalctl -u remote-mcp -n 50

# Check port
sudo netstat -tlnp | grep 3000
```

### OAuth nu funcționează
- Verifică callback URL în .env
- Verifică DNS pentru mcp.academiadepolitie.com
- Check CORS headers în nginx

### SSE connection drops
- Verifică nginx timeouts
- Check firewall pentru persistent connections

## 📝 Licență

MIT - Academiadepolitie.com 2025